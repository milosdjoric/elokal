<?php

namespace App\Jobs;

use App\Models\LoyaltyTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireLoyaltyPoints implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        if (! feature('feature_loyalty')) {
            return;
        }

        // Pronađi earn transakcije koje su istekle i imaju pozitivne poene
        $expired = LoyaltyTransaction::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->where('points', '>', 0)
            ->where('type', '!=', 'expiry')
            ->get();

        $totalExpired = 0;

        foreach ($expired as $transaction) {
            $account = $transaction->account;
            if (! $account) continue;

            $pointsToExpire = $transaction->points;

            // Ne oduzimaj više poena nego što korisnik ima
            $pointsToExpire = min($pointsToExpire, $account->points_balance);
            if ($pointsToExpire <= 0) {
                // Markiraj kao obrađeno (setuj expires_at u prošlost)
                $transaction->update(['expires_at' => null]);
                continue;
            }

            $account->decrement('points_balance', $pointsToExpire);

            $account->transactions()->create([
                'points' => -$pointsToExpire,
                'type' => 'expiry',
                'description' => "Isteklo {$pointsToExpire} poena",
            ]);

            // Markiraj original transakciju kao obrađenu
            $transaction->update(['expires_at' => null]);

            $totalExpired += $pointsToExpire;
        }

        if ($totalExpired > 0) {
            Log::info("ExpireLoyaltyPoints: isteklo {$totalExpired} poena ukupno.");
        }
    }
}
