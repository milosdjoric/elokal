<script setup lang="ts">
import type { Product, ProductVariant, PaginatedResponse } from '~/types'

const route = useRoute()
const { get } = useApi()
const { addToCart } = useCart()
const { add: trackView, getExcluding: getRecentlyViewed } = useRecentlyViewed()

const authStore = useAuthStore()
const { setProductSchema, setBreadcrumbSchema } = useSchemaOrg()

const product = ref<Product | null>(null)
const recentlyViewed = ref<Product[]>([])
const related = ref<Product[]>([])
const prevProduct = ref<{ slug: string; name: string } | null>(null)
const nextProduct = ref<{ slug: string; name: string } | null>(null)
const loading = ref(true)
const qty = ref(1)
const activeTab = ref('description')

// Varijante
const selectedVariant = ref<ProductVariant | null>(null)
const hasVariants = computed(() => (product.value?.variants?.length ?? 0) > 0)

const displayPrice = computed(() => {
  if (selectedVariant.value) return selectedVariant.value.effective_price
  return product.value?.effective_price ?? '0'
})

const displayStock = computed(() => {
  if (selectedVariant.value) return selectedVariant.value.stock_quantity
  return product.value?.stock_quantity ?? 0
})

const canAddToCart = computed(() => {
  if (hasVariants.value && !selectedVariant.value) return false
  return displayStock.value > 0
})

const savingsAmount = computed(() => {
  const p = product.value
  if (!p || !p.is_on_sale || !p.sale_price) return 0
  return Math.max(0, parseFloat(p.price) - parseFloat(p.sale_price))
})

function onVariantSelect(variant: ProductVariant | null) {
  selectedVariant.value = variant
}

// Slike galerije — prioritet varijantine slike, fallback na sve product slike
const galleryImages = computed(() => {
  if (selectedVariant.value?.images?.length) return selectedVariant.value.images
  return product.value?.images || []
})

// Deep link — prosleđuj query params u VariantSelector i ažuriraj URL
const router = useRouter()
const variantInitialSelection = computed(() => {
  const query = route.query
  const result: Record<string, string> = {}
  for (const [key, val] of Object.entries(query)) {
    if (typeof val === 'string') result[key] = val
  }
  return result
})

function onVariantSelectionChange(params: Record<string, string>) {
  router.replace({ query: { ...params } })
}

// Prikaz varijanti — swatch/table/both
const { getValue: getFeatureValue } = useFeature()
const variantDisplayMode = ref('swatch')

async function loadVariantDisplayMode() {
  variantDisplayMode.value = await getFeatureValue('storefront_variant_display_mode', 'swatch')
}

// Quick view
const quickViewProduct = ref<Product | null>(null)
const quickViewOpen = ref(false)
function openQuickView(p: Product) { quickViewProduct.value = p; quickViewOpen.value = true }

const notifyEmail = ref('')
const notifyLoading = ref(false)
const notifySuccess = ref('')
const notifyError = ref('')

// Social proof — "X ljudi gleda"
const viewerCount = ref(0)
async function trackProductView() {
  if (!product.value) return
  try {
    const { apiBase } = useApi()
    const data = await $fetch<{ viewers: number }>(`${apiBase}/v1/products/${product.value.id}/view`, {
      method: 'POST',
    })
    viewerCount.value = data.viewers
  }
  catch { /* ignorisano */ }
}

const hasSizeGuide = computed(() => !!product.value?.size_guide?.headers?.length)
const customTabs = computed(() => product.value?.custom_tabs?.filter(t => t.title && t.content) || [])

const tabs = computed(() => {
  const list = [
    { key: 'description', label: 'Opis' },
    { key: 'reviews', label: 'Recenzije' },
  ]
  if (hasSizeGuide.value) {
    list.push({ key: 'size_guide', label: 'Vodič za veličine' })
  }
  for (let i = 0; i < customTabs.value.length; i++) {
    list.push({ key: `custom_${i}`, label: customTabs.value[i].title })
  }
  list.push(
    { key: 'shipping', label: 'Dostava i povrat' },
    { key: 'faq', label: 'FAQ' },
  )
  return list
})

const faqItems = [
  { key: 'faq1', title: 'Koliko traje dostava?' },
  { key: 'faq2', title: 'Da li mogu da vratim proizvod?' },
  { key: 'faq3', title: 'Kako mogu da pratim narudžbinu?' },
]

async function fetchProduct() {
  loading.value = true
  try {
    const data = await get<{ data: Product; prev_next?: { prev: { slug: string; name: string } | null; next: { slug: string; name: string } | null } }>(`/v1/products/${route.params.slug}`)
    product.value = data.data
    prevProduct.value = data.prev_next?.prev ?? null
    nextProduct.value = data.prev_next?.next ?? null

    // Track recently viewed
    trackView(data.data)
    recentlyViewed.value = getRecentlyViewed(data.data.id)

    // Schema.org — Product
    const p = data.data
    const primaryImg = p.images?.find(i => i.is_primary) || p.images?.[0]
    const imgUrl = primaryImg ? `${useApi().apiBase.replace('/api', '')}/storage/${primaryImg.image_path}` : undefined
    setProductSchema({
      name: p.name,
      description: p.short_description || p.description || undefined,
      image: imgUrl ? [imgUrl] : undefined,
      sku: p.sku || undefined,
      url: `${window.location.origin}/proizvodi/${p.slug}`,
      price: p.price,
      salePrice: p.is_on_sale && p.sale_price ? p.sale_price : undefined,
      availability: p.stock_quantity > 0 ? 'InStock' : 'OutOfStock',
    })

    // Schema.org — BreadcrumbList
    const breadcrumbs = [
      { name: 'Početna', url: window.location.origin },
      { name: p.name, url: `${window.location.origin}/proizvodi/${p.slug}` },
    ]
    setBreadcrumbSchema(breadcrumbs)

    // Ručne relacije imaju prioritet, fallback na istu kategoriju
    if (data.data.related_products?.length) {
      related.value = data.data.related_products
    } else if (data.data.categories?.length) {
      const relData = await get<PaginatedResponse<Product>>('/v1/products', {
        category: data.data.categories[0],
        per_page: 8,
      })
      related.value = relData.data.filter(p => p.id !== data.data.id)
    }
  }
  catch {
    navigateTo('/proizvodi')
  }
  finally { loading.value = false }
}

async function handleNotifyMe() {
  if (!product.value) return
  notifyLoading.value = true
  notifyError.value = ''
  try {
    const { apiBase } = useApi()
    const email = notifyEmail.value || authStore.user?.email
    const data = await $fetch<{ message: string }>(`${apiBase}/v1/products/${product.value.id}/notify-me`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email }),
    })
    notifySuccess.value = data.message
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    notifyError.value = err.data?.message || 'Greška.'
  }
  finally { notifyLoading.value = false }
}

function handleAddToCart() {
  if (product.value) {
    addToCart(product.value, qty.value)
    qty.value = 1
  }
}

// Sticky add to cart bar
const showStickyBar = ref(false)
function onScroll() { showStickyBar.value = window.scrollY > 500 }
onMounted(() => {
  fetchProduct().then(() => trackProductView())
  loadVariantDisplayMode()
  window.addEventListener('scroll', onScroll)
  if (authStore.user?.email) notifyEmail.value = authStore.user.email
})
onUnmounted(() => window.removeEventListener('scroll', onScroll))

useHead({
  title: computed(() => product.value?.meta_title || (product.value ? `${product.value.name} — sloj kolektiv` : 'sloj kolektiv')),
  link: [{ rel: 'canonical', href: computed(() => `http://localhost:3000/proizvodi/${route.params.slug}`) }],
})

useSeoMeta({
  description: computed(() => product.value?.meta_description || product.value?.short_description || ''),
  ogTitle: computed(() => product.value?.name || ''),
  ogDescription: computed(() => product.value?.short_description || ''),
  ogImage: computed(() => {
    const img = product.value?.images?.find(i => i.is_primary) || product.value?.images?.[0]
    return img ? resolveImageUrl(img.image_path) : ''
  }),
  ogType: 'website',
})
</script>

<template>
  <div class="bg-paper">
    <div v-if="loading" class="max-w-[1400px] mx-auto px-6 lg:px-10 py-32 flex justify-center">
      <UiAtomsSpinner size="lg" />
    </div>

    <template v-else-if="product">
      <div class="max-w-[1400px] mx-auto px-6 lg:px-10 pt-8 pb-20">
        <div class="flex items-center justify-between mb-8">
          <LayoutBreadcrumbs :items="[
            { label: 'Proizvodi', to: '/proizvodi' },
            { label: product.name },
          ]" />
          <div v-if="prevProduct || nextProduct" class="hidden md:flex items-center gap-3 text-[14px]">
            <NuxtLink v-if="prevProduct" :to="`/proizvodi/${prevProduct.slug}`" class="text-ink-500 hover:text-terra-600 transition-colors" :title="prevProduct.name">
              ← Prethodni
            </NuxtLink>
            <span v-if="prevProduct && nextProduct" class="text-ink-200">·</span>
            <NuxtLink v-if="nextProduct" :to="`/proizvodi/${nextProduct.slug}`" class="text-ink-500 hover:text-terra-600 transition-colors" :title="nextProduct.name">
              Sledeći →
            </NuxtLink>
          </div>
        </div>

        <!-- Main section — 60/40 split -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 mb-20">
          <!-- Gallery — 7 col -->
          <div class="lg:col-span-7">
            <ProductGallery :images="galleryImages" />
          </div>

          <!-- Info — 5 col, sticky on desktop -->
          <div class="lg:col-span-5 lg:sticky lg:top-28 lg:self-start">
            <!-- Meta line: SKU/category -->
            <div class="flex items-center justify-between mb-4">
              <p v-if="product.sku" class="font-mono text-[10px] tracking-[0.16em] uppercase text-ink-400 tabular-nums">
                {{ product.sku }}
              </p>
              <ProductWishlistButton :product-id="product.id" />
            </div>

            <!-- Display naziv -->
            <h1 class="text-[32px] md:text-[44px] font-light text-ink-800 leading-[1.05] tracking-[-0.02em] mb-3">
              {{ product.name }}
            </h1>

            <!-- Designer / year line -->
            <p v-if="product.short_description" class="text-[14px] text-ink-500 leading-[1.6] mb-8 max-w-md">
              {{ product.short_description }}
            </p>

            <!-- PRICE BLOCK — bez border-a, dovoljno prostora -->
            <div class="mb-8">
              <UiMoleculesPriceDisplay
                :price="product.price"
                :sale-price="product.sale_price"
                :sale-percentage="product.sale_percentage"
                :is-on-sale="product.is_on_sale"
                :unit-price="product.formatted_unit_price"
                size="lg"
              />

              <p v-if="product.is_on_sale && savingsAmount > 0" class="mt-2 text-[12px] text-terra-600 tabular-nums tracking-[0.04em]">
                Štedite {{ formatPrice(savingsAmount) }}
              </p>

              <p v-if="product.is_on_sale && product.lowest_price_30_days" class="mt-2 text-[11px] text-ink-400 leading-[1.6]">
                Najniža cena u poslednjih 30 dana:
                <span class="tabular-nums text-ink-500">{{ formatPrice(product.lowest_price_30_days) }}</span>
              </p>
            </div>

            <!-- Sale countdown — diskretni inline -->
            <p
              v-if="product.is_on_sale && product.sale_price_to"
              class="flex items-center gap-2 text-[12px] text-terra-600 mb-6 tracking-[0.04em]"
            >
              <Icon name="lucide:clock" class="w-3.5 h-3.5 flex-shrink-0" />
              <ProductSaleCountdown :ends-at="product.sale_price_to" inline />
            </p>

            <!-- Varijante -->
            <div v-if="hasVariants" class="mb-8 pb-8 border-b border-ink-100">
              <ProductVariantSelector
                v-if="variantDisplayMode === 'swatch' || variantDisplayMode === 'both'"
                :variants="product.variants!"
                :initial-selection="variantInitialSelection"
                @select="onVariantSelect"
                @selection-change="onVariantSelectionChange"
              />
              <ProductVariantTable
                v-if="variantDisplayMode === 'table' || variantDisplayMode === 'both'"
                :variants="product.variants!"
                :product="product"
                :class="{ 'mt-4': variantDisplayMode === 'both' }"
                @select="onVariantSelect"
              />
            </div>

            <!-- Stock status -->
            <div v-if="!hasVariants" class="mb-6">
              <span v-if="displayStock > 0" class="inline-flex items-center gap-2 text-[13px] text-success">
                <span class="w-1.5 h-1.5 bg-success" aria-hidden="true" />
                Na stanju
              </span>
              <span v-else class="inline-flex items-center gap-2 text-[13px] text-ink-500">
                <span class="w-1.5 h-1.5 bg-ink-300" aria-hidden="true" />
                Nema na stanju
              </span>
            </div>

            <p v-if="hasVariants && !selectedVariant" class="text-[13px] text-terra-600 mb-4">
              Izaberite opcije iznad.
            </p>

            <!-- Add to cart — bordered, no solid -->
            <div v-if="canAddToCart" class="space-y-3 mb-8">
              <UiMoleculesQuantitySelector v-model="qty" />
              <button
                type="button"
                class="block w-full py-4 text-[14px] border border-ink-800 text-ink-800 hover:bg-ink-800 hover:text-paper transition-colors"
                @click="handleAddToCart"
              >
                Dodaj u korpu →
              </button>
            </div>

            <!-- Notify me -->
            <div v-else class="mb-8">
              <div v-if="notifySuccess" class="text-[14px] text-success">
                {{ notifySuccess }}
              </div>
              <form v-else class="space-y-3" @submit.prevent="handleNotifyMe">
                <input
                  v-model="notifyEmail"
                  type="email"
                  required
                  :placeholder="authStore.user?.email || 'Vaš email'"
                  class="w-full px-0 py-3 text-[14px] bg-transparent border-0 border-b border-ink-200 focus:outline-none focus:border-ink-800 placeholder:text-ink-300"
                />
                <button
                  type="submit"
                  :disabled="notifyLoading"
                  class="block w-full py-4 text-[14px] border border-ink-800 text-ink-800 hover:bg-ink-800 hover:text-paper transition-colors disabled:opacity-50"
                >
                  {{ notifyLoading ? '…' : 'Obavesti me' }}
                </button>
              </form>
              <p v-if="notifyError" class="mt-2 text-[13px] text-terra-600">{{ notifyError }}</p>
            </div>

            <!-- Callback -->
            <div class="mb-8">
              <ProductCallbackModal :product-id="product.id" :product-name="product.name" />
            </div>

            <!-- Trust info — clean lines -->
            <div class="space-y-3 pt-6 border-t border-ink-100">
              <div class="flex items-start gap-3 text-[13px] text-ink-500">
                <Icon name="lucide:truck" class="w-4 h-4 text-ink-400 flex-shrink-0 mt-0.5" />
                <span>Besplatna dostava preko <span class="tabular-nums text-ink-700">5.000 RSD</span></span>
              </div>
              <div class="flex items-start gap-3 text-[13px] text-ink-500">
                <Icon name="lucide:rotate-ccw" class="w-4 h-4 text-ink-400 flex-shrink-0 mt-0.5" />
                <span>Povrat u roku od 14 dana</span>
              </div>
              <div class="flex items-start gap-3 text-[13px] text-ink-500">
                <Icon name="lucide:clock" class="w-4 h-4 text-ink-400 flex-shrink-0 mt-0.5" />
                <span>Isporuka 14–21 dan (po porudžbini)</span>
              </div>
            </div>

            <!-- Social proof — discrete -->
            <p v-if="viewerCount > 1" class="mt-6 text-[12px] text-ink-400 tracking-[0.04em] flex items-center gap-2">
              <Icon name="lucide:eye" class="w-3.5 h-3.5" />
              {{ viewerCount }} {{ viewerCount < 5 ? 'osobe gledaju' : 'osoba gleda' }} ovaj proizvod
            </p>

            <!-- Social share -->
            <div class="mt-6">
              <UiMoleculesSocialShare :url="`http://localhost:3000/proizvodi/${product.slug}`" :title="product.name" />
            </div>
          </div>
        </div>

        <!-- Akordeon sekcije — Vitra style -->
        <div class="mb-20 max-w-3xl">
          <details v-if="product.description" class="group border-t border-ink-200 py-6" open>
            <summary class="flex items-center justify-between cursor-pointer list-none">
              <span class="text-[18px] md:text-[22px] font-light text-ink-800 tracking-[-0.01em]">O dizajnu</span>
              <Icon name="lucide:plus" class="w-4 h-4 text-ink-400 transition-transform group-open:rotate-45" />
            </summary>
            <div class="prose prose-sm max-w-none text-ink-600 leading-[1.7] mt-6">
              <div v-html="product.description.replace(/\n/g, '<br>')" />
            </div>
          </details>

          <details class="group border-t border-ink-200 py-6">
            <summary class="flex items-center justify-between cursor-pointer list-none">
              <span class="text-[18px] md:text-[22px] font-light text-ink-800 tracking-[-0.01em]">Materijali i održavanje</span>
              <Icon name="lucide:plus" class="w-4 h-4 text-ink-400 transition-transform group-open:rotate-45" />
            </summary>
            <div class="text-[14px] text-ink-600 leading-[1.7] mt-6 space-y-3 max-w-2xl">
              <p>Breza šperploča, prirodno premazana voskom. Slojevi se vide na ivicama — to je deo dizajna, ne defekt.</p>
              <p>Brisanje vlažnom krpom. Izbegavajte direktnu sunčevu svetlost duže vreme da boja drveta ostane ujednačena.</p>
            </div>
          </details>

          <details v-for="(ct, ci) in customTabs" :key="`custom_${ci}`" class="group border-t border-ink-200 py-6">
            <summary class="flex items-center justify-between cursor-pointer list-none">
              <span class="text-[18px] md:text-[22px] font-light text-ink-800 tracking-[-0.01em]">{{ ct.title }}</span>
              <Icon name="lucide:plus" class="w-4 h-4 text-ink-400 transition-transform group-open:rotate-45" />
            </summary>
            <div class="prose prose-sm max-w-none text-ink-600 leading-[1.7] mt-6">
              <div v-html="ct.content.replace(/\n/g, '<br>')" />
            </div>
          </details>

          <details v-if="hasSizeGuide" class="group border-t border-ink-200 py-6">
            <summary class="flex items-center justify-between cursor-pointer list-none">
              <span class="text-[18px] md:text-[22px] font-light text-ink-800 tracking-[-0.01em]">Dimenzije</span>
              <Icon name="lucide:plus" class="w-4 h-4 text-ink-400 transition-transform group-open:rotate-45" />
            </summary>
            <div class="overflow-x-auto mt-6">
              <table class="w-full text-[13px]">
                <thead>
                  <tr class="border-b border-ink-100">
                    <th
                      v-for="(header, i) in product!.size_guide!.headers"
                      :key="i"
                      class="px-3 py-3 text-left text-[11px] uppercase tracking-[0.16em] text-ink-500"
                    >
                      {{ header }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, ri) in product!.size_guide!.rows" :key="ri" class="border-b border-ink-100">
                    <td
                      v-for="(cell, ci2) in row"
                      :key="ci2"
                      class="px-3 py-3 text-ink-700 tabular-nums"
                      :class="{ 'text-ink-800': ci2 === 0 }"
                    >
                      {{ cell }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </details>

          <details class="group border-t border-ink-200 py-6">
            <summary class="flex items-center justify-between cursor-pointer list-none">
              <span class="text-[18px] md:text-[22px] font-light text-ink-800 tracking-[-0.01em]">Dostava i povrat</span>
              <Icon name="lucide:plus" class="w-4 h-4 text-ink-400 transition-transform group-open:rotate-45" />
            </summary>
            <div class="text-[14px] text-ink-600 leading-[1.7] mt-6 space-y-3 max-w-2xl">
              <p>Komadi se izrađuju po porudžbini — isporuka 14–21 radni dan. Besplatna dostava preko 5.000 RSD.</p>
              <p>Povrat u roku od 14 dana po Zakonu o zaštiti potrošača. Komad mora biti u originalnom pakovanju, neoštećen.</p>
            </div>
          </details>

          <details class="group border-t border-b border-ink-200 py-6">
            <summary class="flex items-center justify-between cursor-pointer list-none">
              <span class="text-[18px] md:text-[22px] font-light text-ink-800 tracking-[-0.01em]">Recenzije</span>
              <Icon name="lucide:plus" class="w-4 h-4 text-ink-400 transition-transform group-open:rotate-45" />
            </summary>
            <div class="mt-6">
              <ProductReviews :product-id="product.id" />
            </div>
          </details>
        </div>

        <!-- Up-sell -->
        <section v-if="product.up_sell_products?.length" class="mb-20">
          <ProductCarousel title="Možda Vam se dopada" :products="product.up_sell_products" @quick-view="openQuickView" />
        </section>

        <!-- Related products -->
        <section v-if="related.length" class="mb-20">
          <ProductCarousel title="Iz iste familije" :products="related" @quick-view="openQuickView" />
        </section>

        <!-- Recently viewed -->
        <section v-if="recentlyViewed.length > 0" class="mb-20">
          <ProductCarousel title="Nedavno gledali ste" :products="recentlyViewed" @quick-view="openQuickView" />
        </section>
      </div>

      <!-- Sticky add to cart bar — minimalistic -->
      <Transition enter-active-class="transition duration-200" enter-from-class="translate-y-full" leave-active-class="transition duration-150" leave-to-class="translate-y-full">
        <div v-if="showStickyBar && product.stock_quantity > 0" class="fixed bottom-0 left-0 right-0 bg-paper/95 backdrop-blur border-t border-ink-100 z-40 py-4 lg:hidden">
          <div class="max-w-[1400px] mx-auto px-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
              <p class="text-[14px] text-ink-800 truncate">{{ product.name }}</p>
            </div>
            <div class="flex items-center gap-4 flex-shrink-0">
              <span class="text-[14px] text-ink-800 tabular-nums">{{ formatPrice(product.effective_price) }}</span>
              <button class="px-5 py-3 text-[14px] bg-ink-800 text-paper hover:bg-terra-600 transition-colors" @click="handleAddToCart">
                Dodaj
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </template>

    <ProductQuickView v-model="quickViewOpen" :product="quickViewProduct" />
  </div>
</template>
