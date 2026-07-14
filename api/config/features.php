<?php

/*
 * Default vrednosti feature flagova (fallback kad ključ ne postoji u settings tabeli).
 *
 * Ključevi MORAJU biti identični case-ovima u App\Enums\FeatureFlag — to je jedini
 * izvor istine za imena (čuva ih FeatureFlagRegistryTest). Frontend ogledala
 * defaulta: storefront/utils/features.ts i admin/utils/features.ts.
 *
 * PRAVILO "UI + API": svaki flag mora da gejtuje i UI i odgovarajuću API rutu/obradu
 * (middleware `feature:<ime>` ili `feature()` u kodu) — flag koji samo sakriva UI
 * tretira se kao neimplementiran.
 */
return [
    'feature_wishlist' => true,
    'feature_newsletter' => true,
    'feature_compare' => true,
    'feature_social_proof' => false,
    'feature_store_credits' => true,
    'feature_multi_currency' => false,
    'feature_gift_cards' => true,
    'feature_loyalty' => true,
    'feature_webhooks' => false,
    'feature_abandoned_cart' => true,
    'feature_shop_the_look' => false,
    'feature_store_locator' => false,
    'feature_downloads' => false,
    'feature_multi_language' => false,
];
