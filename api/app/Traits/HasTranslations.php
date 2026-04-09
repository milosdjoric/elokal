<?php

namespace App\Traits;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Vraća prevedenu vrednost polja ili fallback na originalno.
     */
    public function translated(string $field, string $locale): string
    {
        if ($locale === 'sr') {
            return $this->{$field} ?? '';
        }

        $translation = $this->translations
            ->where('locale', $locale)
            ->where('field', $field)
            ->first();

        return $translation?->value ?? $this->{$field} ?? '';
    }

    /**
     * Sačuvaj prevode za dati locale.
     */
    public function setTranslations(string $locale, array $fields): void
    {
        foreach ($fields as $field => $value) {
            Translation::updateOrCreate(
                [
                    'translatable_type' => static::class,
                    'translatable_id' => $this->id,
                    'locale' => $locale,
                    'field' => $field,
                ],
                ['value' => $value],
            );
        }
    }

    /**
     * Vraća sve prevode za dati locale kao key-value array.
     */
    public function getTranslations(string $locale): array
    {
        return $this->translations()
            ->where('locale', $locale)
            ->pluck('value', 'field')
            ->toArray();
    }

    /**
     * Procenat kompletnosti prevoda za dati locale.
     */
    public function translationCompleteness(string $locale): int
    {
        if (! property_exists($this, 'translatableFields')) {
            return 0;
        }

        $total = count($this->translatableFields);
        if ($total === 0) return 100;

        $translated = $this->translations()
            ->where('locale', $locale)
            ->whereIn('field', $this->translatableFields)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->count();

        return (int) round(($translated / $total) * 100);
    }
}
