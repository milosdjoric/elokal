<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    /**
     * Vraća prevode za dati model i locale.
     * GET /admin/translations?type=Product&id=1&locale=en
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:Product,Category,Post,Page',
            'id' => 'required|integer',
            'locale' => 'required|string|max:10',
        ]);

        $modelClass = "App\\Models\\{$request->type}";
        $model = $modelClass::findOrFail($request->id);

        return response()->json([
            'data' => [
                'translations' => $model->getTranslations($request->locale),
                'completeness' => $model->translationCompleteness($request->locale),
                'fields' => $model->translatableFields,
            ],
        ]);
    }

    /**
     * Sačuvaj prevode za dati model i locale.
     * PUT /admin/translations
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:Product,Category,Post,Page',
            'id' => 'required|integer',
            'locale' => 'required|string|max:10',
            'translations' => 'required|array',
        ]);

        $modelClass = "App\\Models\\{$request->type}";
        $model = $modelClass::findOrFail($request->id);

        $model->setTranslations($request->locale, $request->translations);

        return response()->json([
            'message' => 'Prevodi sačuvani.',
            'completeness' => $model->translationCompleteness($request->locale),
        ]);
    }

    /**
     * Bulk export prevoda za tip modela.
     * GET /admin/translations/export?type=Product&locale=en
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:Product,Category,Post,Page',
            'locale' => 'required|string|max:10',
        ]);

        $modelClass = "App\\Models\\{$request->type}";
        $models = $modelClass::with(['translations' => fn ($q) => $q->where('locale', $request->locale)])->get();

        $date = now()->format('Y-m-d');

        return response()->streamDownload(function () use ($models, $request) {
            $h = fopen('php://output', 'w');
            $fields = (new ($request->type === 'Product' ? \App\Models\Product::class : "App\\Models\\{$request->type}"))->translatableFields;
            fputcsv($h, array_merge(['id', 'original_name'], $fields));

            foreach ($models as $model) {
                $row = [$model->id, $model->name ?? $model->title ?? ''];
                foreach ($fields as $field) {
                    $t = $model->translations->where('field', $field)->first();
                    $row[] = $t?->value ?? '';
                }
                fputcsv($h, $row);
            }
            fclose($h);
        }, "translations-{$request->type}-{$request->locale}-{$date}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * Bulk import prevoda iz CSV-a.
     * POST /admin/translations/import
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:Product,Category,Post,Page',
            'locale' => 'required|string|max:10',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $modelClass = "App\\Models\\{$request->type}";
        $fields = (new $modelClass)->translatableFields;

        $file = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($file); // Skip header
        $count = 0;

        while ($row = fgetcsv($file)) {
            $id = $row[0] ?? null;
            if (! $id) continue;

            $model = $modelClass::find($id);
            if (! $model) continue;

            $translations = [];
            foreach ($fields as $i => $field) {
                $value = $row[$i + 2] ?? ''; // +2 jer kolone 0=id, 1=original_name
                if ($value !== '') {
                    $translations[$field] = $value;
                }
            }

            if (! empty($translations)) {
                $model->setTranslations($request->locale, $translations);
                $count++;
            }
        }
        fclose($file);

        return response()->json(['message' => "Uvezeno prevoda za {$count} stavki."]);
    }

    /**
     * Dostupni jezici i statistika kompletnosti.
     * GET /admin/translations/languages
     */
    public function languages(): JsonResponse
    {
        $available = \App\Models\Setting::getValue('languages_available', 'sr');
        $locales = array_map('trim', explode(',', $available));

        $stats = [];
        foreach ($locales as $locale) {
            if ($locale === 'sr') continue;
            $total = Translation::where('locale', $locale)->count();
            $filled = Translation::where('locale', $locale)->whereNotNull('value')->where('value', '!=', '')->count();
            $stats[$locale] = [
                'total_fields' => $total,
                'translated' => $filled,
                'percentage' => $total > 0 ? round(($filled / $total) * 100) : 0,
            ];
        }

        return response()->json([
            'locales' => $locales,
            'default' => \App\Models\Setting::getValue('languages_default', 'sr'),
            'stats' => $stats,
        ]);
    }
}
