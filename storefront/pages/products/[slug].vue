<script setup lang="ts">
import type { Product, ProductVariant, PaginatedResponse } from '~/types'

const route = useRoute()
const { get } = useApi()
const { addToCart } = useCart()

const authStore = useAuthStore()

const product = ref<Product | null>(null)
const related = ref<Product[]>([])
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

function onVariantSelect(variant: ProductVariant | null) {
  selectedVariant.value = variant
}

const notifyEmail = ref('')
const notifyLoading = ref(false)
const notifySuccess = ref('')
const notifyError = ref('')

const tabs = [
  { key: 'description', label: 'Opis' },
  { key: 'reviews', label: 'Recenzije' },
  { key: 'shipping', label: 'Dostava i povrat' },
  { key: 'faq', label: 'FAQ' },
]

const faqItems = [
  { key: 'faq1', title: 'Koliko traje dostava?' },
  { key: 'faq2', title: 'Da li mogu da vratim proizvod?' },
  { key: 'faq3', title: 'Kako mogu da pratim narudžbinu?' },
]

async function fetchProduct() {
  loading.value = true
  try {
    const data = await get<{ data: Product }>(`/v1/products/${route.params.slug}`)
    product.value = data.data

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
    navigateTo('/products')
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
  fetchProduct()
  window.addEventListener('scroll', onScroll)
  if (authStore.user?.email) notifyEmail.value = authStore.user.email
})
onUnmounted(() => window.removeEventListener('scroll', onScroll))

useHead({
  title: computed(() => product.value?.meta_title || (product.value ? `${product.value.name} — eLokal` : 'eLokal')),
  link: [{ rel: 'canonical', href: computed(() => `http://localhost:3000/products/${route.params.slug}`) }],
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
  <div>
    <div v-if="loading" class="max-w-7xl mx-auto px-4 py-20 flex justify-center">
      <UiAtomsSpinner size="lg" />
    </div>

    <template v-else-if="product">
      <div class="max-w-7xl mx-auto px-4 py-6">
        <LayoutBreadcrumbs :items="[
          { label: 'Proizvodi', to: '/products' },
          { label: product.name },
        ]" />

        <!-- Main section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
          <!-- Gallery -->
          <ProductGallery :images="product.images || []" />

          <!-- Info -->
          <div>
            <div class="flex items-start justify-between gap-4 mb-3">
              <h1 class="text-2xl md:text-3xl font-bold text-gray-800">{{ product.name }}</h1>
              <ProductWishlistButton :product-id="product.id" />
            </div>

            <div class="mb-4">
              <UiMoleculesPriceDisplay
                :price="product.price"
                :sale-price="product.sale_price"
                :sale-percentage="product.sale_percentage"
                :is-on-sale="product.is_on_sale"
                :unit-price="product.formatted_unit_price"
                size="lg"
              />
            </div>

            <ProductSaleCountdown v-if="product.is_on_sale && product.sale_price_to" :ends-at="product.sale_price_to" />

            <p v-if="product.short_description" class="text-gray-600 mb-6">{{ product.short_description }}</p>

            <!-- Varijante -->
            <div v-if="hasVariants" class="mb-6">
              <ProductVariantSelector :variants="product.variants!" @select="onVariantSelect" />
            </div>

            <!-- Stock status (bez varijanti — varijante prikazuju svoj stock) -->
            <div v-if="!hasVariants" class="mb-4">
              <span v-if="displayStock > 0" class="text-sm text-green-600 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                Na stanju ({{ displayStock }} kom)
              </span>
              <span v-else class="text-sm text-red-500 font-medium">Nema na stanju</span>
            </div>

            <!-- Upozorenje: izaberite varijantu -->
            <p v-if="hasVariants && !selectedVariant" class="text-sm text-amber-600 mb-4">
              Izaberite opcije iznad da biste dodali u korpu.
            </p>

            <!-- Add to cart -->
            <div v-if="canAddToCart" class="flex items-center gap-4 mb-6">
              <UiMoleculesQuantitySelector v-model="qty" />
              <UiAtomsButton size="lg" @click="handleAddToCart">Dodaj u korpu</UiAtomsButton>
            </div>

            <!-- Notify me -->
            <div v-else class="mb-6">
              <div v-if="notifySuccess" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm">
                {{ notifySuccess }}
              </div>
              <form v-else class="flex gap-2" @submit.prevent="handleNotifyMe">
                <input
                  v-model="notifyEmail"
                  type="email"
                  required
                  :placeholder="authStore.user?.email || 'Vaš email'"
                  class="flex-1 px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
                <UiAtomsButton type="submit" :loading="notifyLoading">Obavesti me</UiAtomsButton>
              </form>
              <p v-if="notifyError" class="mt-1 text-sm text-red-600">{{ notifyError }}</p>
            </div>

            <!-- SKU -->
            <p v-if="product.sku" class="text-xs text-gray-400 mb-4">SKU: {{ product.sku }}</p>

            <!-- Callback -->
            <div class="mb-6">
              <ProductCallbackModal :product-id="product.id" :product-name="product.name" />
            </div>

            <!-- Trust info -->
            <div class="border border-gray-200 divide-y divide-gray-100">
              <div class="flex items-center gap-3 px-4 py-3 text-sm text-gray-600">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                Besplatna dostava za narudžbine iznad 5.000 RSD
              </div>
              <div class="flex items-center gap-3 px-4 py-3 text-sm text-gray-600">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" /></svg>
                Povrat u roku od 14 dana
              </div>
              <div class="flex items-center gap-3 px-4 py-3 text-sm text-gray-600">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Isporuka za 2-4 radna dana
              </div>
            </div>

            <!-- Social share -->
            <div class="mt-6">
              <UiMoleculesSocialShare :url="`http://localhost:3000/products/${product.slug}`" :title="product.name" />
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="mb-12">
          <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-6 -mb-px">
              <button
                v-for="tab in tabs"
                :key="tab.key"
                class="py-3 text-sm font-medium border-b-2 transition-colors"
                :class="activeTab === tab.key ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                @click="activeTab = tab.key"
              >
                {{ tab.label }}
              </button>
            </nav>
          </div>

          <div v-show="activeTab === 'description'" class="prose prose-sm max-w-none text-gray-600">
            <div v-if="product.description" v-html="product.description.replace(/\n/g, '<br>')" />
            <p v-else class="text-gray-400">Nema opisa.</p>
          </div>

          <div v-show="activeTab === 'reviews'">
            <ProductReviews :product-id="product.id" />
          </div>

          <div v-show="activeTab === 'shipping'" class="text-sm text-gray-600 space-y-4">
            <div>
              <h4 class="font-semibold text-gray-800 mb-1">Dostava</h4>
              <p>Besplatna dostava za narudžbine iznad 5.000 RSD. Standardna dostava: 2-4 radna dana.</p>
            </div>
            <div>
              <h4 class="font-semibold text-gray-800 mb-1">Povrat</h4>
              <p>Imate pravo na povrat proizvoda u roku od 14 dana od prijema. Proizvod mora biti u originalnom pakovanju.</p>
            </div>
          </div>

          <div v-show="activeTab === 'faq'">
            <UiMoleculesAccordion :items="faqItems">
              <template #faq1>Standardna dostava traje 2-4 radna dana. Express dostava je dostupna za narudžbine iz Beograda.</template>
              <template #faq2>Da, imate pravo na povrat u roku od 14 dana. Kontaktirajte nas za detalje.</template>
              <template #faq3>Nakon slanja narudžbine, dobićete email sa brojem za praćenje.</template>
            </UiMoleculesAccordion>
          </div>
        </div>

        <!-- Up-sell -->
        <section v-if="product.up_sell_products?.length" class="mb-12">
          <ProductCarousel title="Možda vas zanima i..." :products="product.up_sell_products" />
        </section>

        <!-- Related products -->
        <section v-if="related.length" class="mb-12">
          <ProductCarousel title="Slični proizvodi" :products="related" />
        </section>
      </div>

      <!-- Sticky add to cart bar -->
      <Transition enter-active-class="transition duration-200" enter-from-class="translate-y-full" leave-active-class="transition duration-150" leave-to-class="translate-y-full">
        <div v-if="showStickyBar && product.stock_quantity > 0" class="fixed bottom-0 lg:bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 py-3">
          <div class="max-w-7xl mx-auto px-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
              <img
                v-if="product.images?.[0]"
                :src="resolveImageUrl(product.images[0].image_path)"
                class="w-10 h-10 object-cover flex-shrink-0"
                alt=""
              />
              <p class="text-sm font-medium text-gray-800 truncate">{{ product.name }}</p>
            </div>
            <div class="flex items-center gap-4 flex-shrink-0">
              <span class="font-bold text-primary-600">{{ Number(product.effective_price).toLocaleString('sr-RS') }} RSD</span>
              <UiAtomsButton @click="handleAddToCart">Dodaj u korpu</UiAtomsButton>
            </div>
          </div>
        </div>
      </Transition>
    </template>
  </div>
</template>
