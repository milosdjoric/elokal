// Jedini izvor istine za feature flag ključeve na admin strani.
// Ogledalo backend registra: api/app/Enums/FeatureFlag.php (imena)
// i api/config/features.php (defaulti) — menjati uvek zajedno.

export const FEATURES = {
  wishlist: 'feature_wishlist',
  newsletter: 'feature_newsletter',
  compare: 'feature_compare',
  socialProof: 'feature_social_proof',
  storeCredits: 'feature_store_credits',
  multiCurrency: 'feature_multi_currency',
  giftCards: 'feature_gift_cards',
  loyalty: 'feature_loyalty',
  webhooks: 'feature_webhooks',
  abandonedCart: 'feature_abandoned_cart',
  shopTheLook: 'feature_shop_the_look',
  storeLocator: 'feature_store_locator',
  downloads: 'feature_downloads',
  multiLanguage: 'feature_multi_language',
} as const

export type FeatureKey = (typeof FEATURES)[keyof typeof FEATURES]

// Fallback dok se settings ne učitaju ili ako ključ ne postoji u bazi
export const FEATURE_DEFAULTS: Record<FeatureKey, boolean> = {
  [FEATURES.wishlist]: true,
  [FEATURES.newsletter]: true,
  [FEATURES.compare]: true,
  [FEATURES.socialProof]: false,
  [FEATURES.storeCredits]: true,
  [FEATURES.multiCurrency]: false,
  [FEATURES.giftCards]: true,
  [FEATURES.loyalty]: true,
  [FEATURES.webhooks]: false,
  [FEATURES.abandonedCart]: true,
  [FEATURES.shopTheLook]: false,
  [FEATURES.storeLocator]: false,
  [FEATURES.downloads]: false,
  [FEATURES.multiLanguage]: false,
}

export const FEATURE_LABELS: Record<FeatureKey, string> = {
  [FEATURES.wishlist]: 'Lista želja (Wishlist)',
  [FEATURES.newsletter]: 'Newsletter',
  [FEATURES.compare]: 'Uporedi proizvode',
  [FEATURES.socialProof]: 'Social Proof popup-ovi',
  [FEATURES.storeCredits]: 'Store Credits',
  [FEATURES.multiCurrency]: 'Više valuta',
  [FEATURES.giftCards]: 'Poklon kartice',
  [FEATURES.loyalty]: 'Loyalty program (poeni)',
  [FEATURES.webhooks]: 'Webhooks',
  [FEATURES.abandonedCart]: 'Napuštene korpe',
  [FEATURES.shopTheLook]: 'Shop the Look',
  [FEATURES.storeLocator]: 'Lokator prodavnica',
  [FEATURES.downloads]: 'Digitalna preuzimanja',
  [FEATURES.multiLanguage]: 'Više jezika',
}
