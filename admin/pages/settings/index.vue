<script setup lang="ts">
const { get, put, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

const loading = ref(true)

function toBool(val: string): boolean { return val === 'true' }
function fromBool(val: boolean): string { return val ? 'true' : 'false' }
const saving = ref(false)
const activeTab = ref('general')

const tabs = [
  { key: 'general', label: 'Opšte' },
  { key: 'storefront', label: 'Storefront Layout' },
  { key: 'topbar', label: 'Top Bar' },
  { key: 'trust', label: 'Trust & Conversion' },
  { key: 'cart', label: 'Korpa & Checkout' },
  { key: 'badges', label: 'Bedževi' },
  { key: 'seo', label: 'SEO' },
  { key: 'gdpr', label: 'GDPR' },
  { key: 'features', label: 'Feature Flags' },
]

// Settings per group
const general = reactive({
  site_name: '',
  logo_url: '',
  favicon_url: '',
  address: '',
  phone: '',
  email: '',
  facebook_url: '',
  instagram_url: '',
  tiktok_url: '',
})

const storefront = reactive({
  header_variant: 'A',
  plp_variant: 'A',
  pdp_variant: 'A',
  cart_variant: 'A',
  products_per_page: '12',
  variant_display_mode: 'swatch',
})

const topbar = reactive({
  enabled: 'true',
  text: 'Besplatna dostava za narudžbine iznad 5.000 din',
  bg_color: '#03045e',
  text_color: '#ffffff',
})

const trust = reactive({
  show_stock_status: 'true',
  show_urgency_bar: 'false',
  urgency_bar_text: 'Požurite! Ostalo još malo na stanju.',
  show_countdown: 'false',
  shipping_text: 'Besplatna dostava za narudžbine iznad 5.000 RSD',
  return_text: 'Povrat u roku od 14 dana',
  dispatch_text: 'Isporuka za 2-4 radna dana',
  show_trust_badges: 'true',
})

const cart = reactive({
  add_to_cart_feedback: 'drawer',
  free_shipping_threshold: '5000',
  guest_checkout: 'true',
  feature_abandoned_cart: 'true',
})

const badges = reactive({
  new_threshold_days: '14',
  new_badge_color: '#22c55e',
  sale_badge_color: '#ef4444',
})

const seo = reactive({
  meta_title_pattern: '{product_name} | {site_name}',
  meta_description_pattern: 'Kupite {product_name} po povoljnoj ceni.',
  ga_id: '',
  fb_pixel_id: '',
})

const gdpr = reactive({
  cookie_consent_text: 'Koristimo kolačiće za poboljšanje iskustva.',
  privacy_policy_url: '',
  terms_url: '',
})

// Flagovi koriste kanonske ključeve (`feature_*`, jednina) koje čitaju
// backend feature() helper, storefront i sidebar — bez grupnog prefiksa.
const features = reactive({
  feature_wishlist: 'true',
  feature_newsletter: 'true',
  feature_compare: 'true',
  feature_social_proof: 'false',
  feature_store_credits: 'true',
  feature_multi_currency: 'false',
  feature_gift_cards: 'true',
  feature_loyalty: 'true',
  feature_webhooks: 'false',
  feature_abandoned_cart: 'true',
  feature_shop_the_look: 'false',
})

const featureLabels: Record<string, string> = {
  feature_wishlist: 'Lista želja (Wishlist)',
  feature_newsletter: 'Newsletter',
  feature_compare: 'Uporedi proizvode',
  feature_social_proof: 'Social Proof popup-ovi',
  feature_store_credits: 'Store Credits',
  feature_multi_currency: 'Više valuta',
  feature_gift_cards: 'Poklon kartice',
  feature_loyalty: 'Loyalty program (poeni)',
  feature_webhooks: 'Webhooks',
  feature_abandoned_cart: 'Napuštene korpe',
  feature_shop_the_look: 'Shop the Look',
}

const groups: Record<string, Record<string, unknown>> = {
  general,
  storefront,
  topbar,
  trust,
  cart,
  badges,
  seo,
  gdpr,
  features,
}

async function fetchSettings() {
  loading.value = true
  try {
    const res = await get<{ data: Record<string, Record<string, string>> }>('/admin/settings')
    const data = res.data

    for (const [group, fields] of Object.entries(data)) {
      if (groups[group]) {
        for (const [key, value] of Object.entries(fields)) {
          const shortKey = group === 'features' ? key : key.replace(`${group}_`, '')
          if (shortKey in groups[group]) {
            (groups[group] as Record<string, string>)[shortKey] = value
          }
        }
      }
    }
  }
  catch { /* first load, no settings yet */ }
  finally {
    loading.value = false
  }
}

async function saveSettings() {
  saving.value = true
  try {
    const settings: { group: string; key: string; value: string }[] = []

    for (const [group, fields] of Object.entries(groups)) {
      for (const [key, value] of Object.entries(fields as Record<string, string>)) {
        settings.push({ group, key: group === 'features' ? key : `${group}_${key}`, value: String(value ?? '') })
      }
    }

    await put('/admin/settings', { settings })
    success('Podešavanja sačuvana.')
  }
  catch (e) {
    toastError(getErrorMessage(e))
  }
  finally {
    saving.value = false
  }
}

onMounted(fetchSettings)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Podešavanja' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Podešavanja</h1>
      <UiAtomsButton :loading="saving" @click="saveSettings">Sačuvaj sve</UiAtomsButton>
    </div>

    <div v-if="loading" class="py-20 flex justify-center">
      <UiAtomsSpinner size="lg" />
    </div>

    <div v-else class="bg-white border border-gray-200 p-6">
      <UiMoleculesTabs v-model="activeTab" :tabs="tabs">
        <template #default="{ active }">

          <!-- General -->
          <div v-show="active === 'general'" class="space-y-4">
            <UiAtomsInput v-model="general.site_name" label="Naziv sajta" />
            <UiAtomsInput v-model="general.logo_url" label="Logo URL" />
            <UiAtomsInput v-model="general.favicon_url" label="Favicon URL" />
            <UiAtomsInput v-model="general.address" label="Adresa" />
            <div class="grid grid-cols-2 gap-4">
              <UiAtomsInput v-model="general.phone" label="Telefon" />
              <UiAtomsInput v-model="general.email" label="Email" type="email" />
            </div>
            <h3 class="text-sm font-semibold text-gray-700 pt-2">Društvene mreže</h3>
            <UiAtomsInput v-model="general.facebook_url" label="Facebook URL" />
            <UiAtomsInput v-model="general.instagram_url" label="Instagram URL" />
            <UiAtomsInput v-model="general.tiktok_url" label="TikTok URL" />
          </div>

          <!-- Storefront Layout -->
          <div v-show="active === 'storefront'" class="space-y-4">
            <div v-for="field in ['header_variant', 'plp_variant', 'pdp_variant', 'cart_variant']" :key="field" class="flex items-center gap-4">
              <label class="text-sm font-medium text-gray-700 w-40">{{ field.replace(/_/g, ' ').replace('variant', 'varijanta') }}</label>
              <div class="flex gap-3">
                <UiAtomsRadio
                  v-for="v in ['A', 'B']"
                  :key="v"
                  v-model="(storefront as Record<string, string>)[field]"
                  :value="v"
                  :name="field"
                  :label="`Varijanta ${v}`"
                />
              </div>
            </div>
            <UiAtomsInput v-model="storefront.products_per_page" label="Proizvoda po stranici" type="number" />

            <div class="flex items-center gap-4">
              <label class="text-sm font-medium text-gray-700 w-40">Prikaz varijanti</label>
              <div class="flex gap-3">
                <UiAtomsRadio
                  v-for="v in [{ val: 'swatch', label: 'Swatch/Dropdown' }, { val: 'table', label: 'Tabela (B2B)' }, { val: 'both', label: 'Oba' }]"
                  :key="v.val"
                  v-model="storefront.variant_display_mode"
                  :value="v.val"
                  name="variant_display_mode"
                  :label="v.label"
                />
              </div>
            </div>
          </div>

          <!-- Top Bar -->
          <div v-show="active === 'topbar'" class="space-y-4">
            <UiAtomsSwitch :model-value="toBool(topbar.enabled)" label="Top bar uključen" @update:model-value="topbar.enabled = fromBool($event)" />
            <UiAtomsInput v-model="topbar.text" label="Tekst" />
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Boja pozadine</label>
                <input v-model="topbar.bg_color" type="color" class="w-full h-10 border border-gray-300 cursor-pointer" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Boja teksta</label>
                <input v-model="topbar.text_color" type="color" class="w-full h-10 border border-gray-300 cursor-pointer" />
              </div>
            </div>
          </div>

          <!-- Trust & Conversion -->
          <div v-show="active === 'trust'" class="space-y-4">
            <UiAtomsSwitch :model-value="toBool(trust.show_stock_status)" label="Prikaži status zaliha" @update:model-value="trust.show_stock_status = fromBool($event)" />
            <UiAtomsSwitch :model-value="toBool(trust.show_urgency_bar)" label="Urgency bar" @update:model-value="trust.show_urgency_bar = fromBool($event)" />
            <UiAtomsInput v-model="trust.urgency_bar_text" label="Urgency tekst" />
            <UiAtomsSwitch :model-value="toBool(trust.show_countdown)" label="Countdown timer" @update:model-value="trust.show_countdown = fromBool($event)" />
            <UiAtomsInput v-model="trust.shipping_text" label="Tekst o dostavi" />
            <UiAtomsInput v-model="trust.return_text" label="Tekst o povratu" />
            <UiAtomsInput v-model="trust.dispatch_text" label="Tekst o isporuci" />
            <UiAtomsSwitch :model-value="toBool(trust.show_trust_badges)" label="Trust badges" @update:model-value="trust.show_trust_badges = fromBool($event)" />
          </div>

          <!-- Cart & Checkout -->
          <div v-show="active === 'cart'" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Add to cart feedback</label>
              <div class="flex gap-4">
                <UiAtomsRadio v-model="cart.add_to_cart_feedback" value="drawer" name="cart_feedback" label="Drawer" />
                <UiAtomsRadio v-model="cart.add_to_cart_feedback" value="modal" name="cart_feedback" label="Modal" />
                <UiAtomsRadio v-model="cart.add_to_cart_feedback" value="toast" name="cart_feedback" label="Toast" />
              </div>
            </div>
            <UiAtomsInput v-model="cart.free_shipping_threshold" label="Prag za besplatnu dostavu (RSD)" type="number" />
            <UiAtomsSwitch :model-value="toBool(cart.guest_checkout)" label="Guest checkout" @update:model-value="cart.guest_checkout = fromBool($event)" />
            <UiAtomsSwitch :model-value="toBool(cart.feature_abandoned_cart)" label="Napuštene korpe (abandoned cart)" @update:model-value="cart.feature_abandoned_cart = fromBool($event)" />
          </div>

          <!-- Badges -->
          <div v-show="active === 'badges'" class="space-y-4">
            <UiAtomsInput v-model="badges.new_threshold_days" label="NEW badge — dana od kreiranja" type="number" />
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NEW badge boja</label>
                <input v-model="badges.new_badge_color" type="color" class="w-full h-10 border border-gray-300 cursor-pointer" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SALE badge boja</label>
                <input v-model="badges.sale_badge_color" type="color" class="w-full h-10 border border-gray-300 cursor-pointer" />
              </div>
            </div>
          </div>

          <!-- SEO -->
          <div v-show="active === 'seo'" class="space-y-4">
            <UiAtomsInput v-model="seo.meta_title_pattern" label="Meta title pattern" />
            <p class="text-xs text-gray-400 -mt-2">Dostupni tokeni: {product_name}, {category_name}, {site_name}</p>
            <UiAtomsInput v-model="seo.meta_description_pattern" label="Meta description pattern" />
            <UiAtomsInput v-model="seo.ga_id" label="Google Analytics ID" placeholder="G-XXXXXXXXXX" />
            <UiAtomsInput v-model="seo.fb_pixel_id" label="Facebook Pixel ID" />
          </div>

          <!-- GDPR -->
          <div v-show="active === 'gdpr'" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cookie consent tekst</label>
              <textarea
                v-model="gdpr.cookie_consent_text"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            <UiAtomsInput v-model="gdpr.privacy_policy_url" label="Politika privatnosti URL" />
            <UiAtomsInput v-model="gdpr.terms_url" label="Uslovi korišćenja URL" />
          </div>

          <!-- Feature Flags -->
          <div v-show="active === 'features'" class="space-y-4">
            <p class="text-sm text-gray-500 mb-2">Uključite ili isključite funkcionalnosti webshopa.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div
                v-for="(label, key) in featureLabels"
                :key="key"
                class="flex items-center justify-between border border-gray-200 rounded-lg px-4 py-3"
              >
                <span class="text-sm font-medium text-gray-700">{{ label }}</span>
                <UiAtomsSwitch
                  :model-value="toBool((features as Record<string, string>)[key])"
                  @update:model-value="(features as Record<string, string>)[key] = fromBool($event)"
                />
              </div>
            </div>
          </div>

        </template>
      </UiMoleculesTabs>
    </div>
  </div>
</template>
