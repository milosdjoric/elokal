<?php

namespace App\Enums;

/**
 * Jedini izvor istine za ključeve feature flagova na backend strani.
 *
 * PRAVILO "UI + API": svaki flag mora da gejtuje i UI (sakrivanje u
 * storefront/admin) i odgovarajuću API rutu ili obradu (middleware
 * `feature:<ime_bez_prefiksa>` ili `feature()` provera u kodu).
 * Flag koji gejtuje samo UI tretira se kao neimplementiran.
 *
 * Default vrednosti žive u config/features.php (ključevi tamo moraju biti
 * identični case-ovima ovde — čuva ih FeatureFlagRegistryTest).
 * Frontend ogledala registra: storefront/utils/features.ts i admin/utils/features.ts.
 */
enum FeatureFlag: string
{
    case Wishlist = 'feature_wishlist';
    case Newsletter = 'feature_newsletter';
    case Compare = 'feature_compare';
    case SocialProof = 'feature_social_proof';
    case StoreCredits = 'feature_store_credits';
    case MultiCurrency = 'feature_multi_currency';
    case GiftCards = 'feature_gift_cards';
    case Loyalty = 'feature_loyalty';
    case Webhooks = 'feature_webhooks';
    case AbandonedCart = 'feature_abandoned_cart';
    case ShopTheLook = 'feature_shop_the_look';
    case StoreLocator = 'feature_store_locator';
    case Downloads = 'feature_downloads';
    case MultiLanguage = 'feature_multi_language';
}
