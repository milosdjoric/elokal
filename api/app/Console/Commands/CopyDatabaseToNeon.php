<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Jednokratni ETL: kopira sve poslovne podatke iz lokalne sqlite baze u Neon (pg).
 * Šeme moraju biti identične (proveriti pre pokretanja). Zadržava postojeću Neon
 * šemu i migrations tabelu; briše i puni poslovne tabele iz sqlite source-a.
 *
 * Neon konekcija se čita iz env NEON_DATABASE_URL (prosleđen inline pri pozivu).
 */
class CopyDatabaseToNeon extends Command
{
    protected $signature = 'db:copy-to-neon {--dry-run : Samo prikaži plan, bez pisanja}';
    protected $description = 'Kopira sve podatke iz lokalne sqlite u Neon (pg)';

    /** Runtime/auth tabele koje se NE kopiraju (Neon ih drži prazne/svoje). */
    private array $skip = [
        'migrations', 'cache', 'cache_locks', 'sessions',
        'jobs', 'job_batches', 'failed_jobs',
        'password_reset_tokens', 'personal_access_tokens',
    ];

    public function handle(): int
    {
        $url = env('NEON_DATABASE_URL');
        if (! $url) {
            $this->error('NEON_DATABASE_URL nije postavljen.');
            return self::FAILURE;
        }

        // Programski definiši 'neon' konekciju (bez diranja config/database.php).
        config(['database.connections.neon' => [
            'driver' => 'pgsql',
            'url' => $url,
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'require',
        ]]);

        $source = DB::connection('sqlite');
        $target = DB::connection('neon');

        // Tabele iz sqlite sa podacima.
        $allTables = collect($source->select(
            "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
        ))->pluck('name')->reject(fn ($t) => in_array($t, $this->skip))->values()->all();

        // Neon ne dozvoljava SET session_replication_role (nema superuser), pa FK
        // ostaju aktivni → insertuj u FK-safe redosledu (topološki sort po FK grafu).
        $tables = collect($this->topoSort($target, $allTables))->values();

        $this->info('Tabela za kopiranje: ' . $tables->count());

        if ($this->option('dry-run')) {
            foreach ($tables as $t) {
                $this->line(sprintf('  %-34s %d redova', $t, $source->table($t)->count()));
            }
            return self::SUCCESS;
        }

        // Isključi FK/trigere na targetu tokom prenosa.
        try {
            $target->statement("SET session_replication_role = 'replica'");
        } catch (\Throwable $e) {
            $this->warn('session_replication_role nije dozvoljen: ' . $e->getMessage());
        }

        // Očisti poslovne tabele (zadrži šemu).
        $quoted = $tables->map(fn ($t) => '"' . $t . '"')->implode(', ');
        $target->statement("TRUNCATE {$quoted} RESTART IDENTITY CASCADE");

        $total = 0;
        foreach ($tables as $t) {
            $rows = $source->table($t)->get();
            if ($rows->isEmpty()) {
                continue;
            }

            // Boolean kolone na targetu — sqlite ih čuva kao 0/1, pg traži bool.
            $boolCols = collect($target->select(
                "SELECT column_name FROM information_schema.columns WHERE table_name = ? AND data_type = 'boolean'",
                [$t]
            ))->pluck('column_name')->all();

            $data = $rows->map(function ($r) use ($boolCols) {
                $a = (array) $r;
                foreach ($boolCols as $c) {
                    if (array_key_exists($c, $a) && $a[$c] !== null) {
                        $a[$c] = (bool) $a[$c];
                    }
                }
                return $a;
            })->all();

            foreach (array_chunk($data, 200) as $chunk) {
                $target->table($t)->insert($chunk);
            }
            $total += count($data);
            $this->line(sprintf('  %-34s %d', $t, count($data)));
        }

        // Vrati FK i resetuj sekvence na MAX(id).
        try {
            $target->statement("SET session_replication_role = 'origin'");
        } catch (\Throwable $e) {
            // ignore
        }

        foreach ($tables as $t) {
            $hasId = collect($target->select(
                "SELECT 1 FROM information_schema.columns WHERE table_name = ? AND column_name = 'id'",
                [$t]
            ))->isNotEmpty();
            if (! $hasId) {
                continue;
            }
            $target->statement(
                "SELECT setval(pg_get_serial_sequence('\"{$t}\"', 'id'), COALESCE((SELECT MAX(id) FROM \"{$t}\"), 1), true)"
            );
        }

        $this->info("Gotovo. Ukupno redova kopirano: {$total}");
        return self::SUCCESS;
    }

    /**
     * Topološki sort tabela po FK grafu (roditelj pre deteta). Self-reference
     * (parent_id u istoj tabeli) se ignoriše — oslanja se na rowid redosled izvora.
     *
     * @param  array<string>  $tables
     * @return array<string>
     */
    private function topoSort($target, array $tables): array
    {
        $set = array_flip($tables);

        // FK parovi child→parent iz pg kataloga.
        $fks = $target->select("
            SELECT tc.table_name AS child, ccu.table_name AS parent
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
              ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage ccu
              ON tc.constraint_name = ccu.constraint_name AND tc.table_schema = ccu.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_schema = 'public'
        ");

        $deps = [];      // child => [parent, ...]
        $indeg = array_fill_keys($tables, 0);
        foreach ($tables as $t) {
            $deps[$t] = [];
        }
        $seen = [];
        foreach ($fks as $fk) {
            $c = $fk->child;
            $p = $fk->parent;
            if ($c === $p) {
                continue; // self-ref
            }
            if (! isset($set[$c]) || ! isset($set[$p])) {
                continue; // van skupa (skip tabele)
            }
            $key = "$c|$p";
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $deps[$c][] = $p;
            $indeg[$c]++;
        }

        // Kahn: počni od tabela bez zavisnosti, stabilno (po imenu) radi determinizma.
        $queue = [];
        foreach ($tables as $t) {
            if ($indeg[$t] === 0) {
                $queue[] = $t;
            }
        }
        sort($queue);

        // Obrnuti indeks: parent => [children]
        $children = array_fill_keys($tables, []);
        foreach ($deps as $child => $parents) {
            foreach ($parents as $p) {
                $children[$p][] = $child;
            }
        }

        $sorted = [];
        while ($queue) {
            $t = array_shift($queue);
            $sorted[] = $t;
            $newlyFree = [];
            foreach ($children[$t] as $child) {
                if (--$indeg[$child] === 0) {
                    $newlyFree[] = $child;
                }
            }
            sort($newlyFree);
            foreach ($newlyFree as $f) {
                $queue[] = $f;
            }
        }

        // Ciklus / preostale (ne bi trebalo) — dodaj na kraj.
        foreach ($tables as $t) {
            if (! in_array($t, $sorted, true)) {
                $sorted[] = $t;
            }
        }

        return $sorted;
    }
}
