<script setup lang="ts">
import type { Product } from '~/types'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Lista želja — sloj kolektiv' })

const { isFeatureEnabled } = useFeature()
if (!isFeatureEnabled(FEATURES.wishlist)) {
  throw createError({ statusCode: 404, statusMessage: 'Stranica nije pronađena' })
}

const authStore = useAuthStore()
const { apiBase } = useApi()
const wishlistStore = useWishlistStore()

const products = ref<Product[]>([])
const loading = ref(true)
const notifyMeProduct = ref<Product | null>(null)
const notifyMeOpen = ref(false)

function openNotifyMe(product: Product) {
  notifyMeProduct.value = product
  notifyMeOpen.value = true
}

async function fetchWishlist() {
  loading.value = true
  try {
    const data = await $fetch<{ data: Product[] }>(`${apiBase}/v1/wishlist`, {
      headers: { Authorization: `Bearer ${authStore.token}`, Accept: 'application/json' },
    })
    products.value = data.data
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

async function removeItem(productId: number) {
  wishlistStore.toggle(productId)
  products.value = products.value.filter(p => p.id !== productId)
}

onMounted(fetchWishlist)
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: 'Moj nalog', to: '/nalog' }, { label: 'Lista želja' }]" />

    <div class="flex flex-col lg:flex-row gap-8">
      <LayoutAccountSidebar />

      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Lista želja</h1>

        <div v-if="loading" class="space-y-3">
          <UiAtomsSkeleton v-for="i in 3" :key="i" height="80px" />
        </div>

        <UiMoleculesEmptyState
          v-else-if="products.length === 0"
          variant="wishlist"
          size="lg"
          title="Lista želja je prazna"
          description="Sačuvajte proizvode klikom na srce na kartici proizvoda — biće ovde da im se vratite."
        >
          <NuxtLink to="/proizvodi">
            <UiAtomsButton variant="outline">Pogledaj proizvode</UiAtomsButton>
          </NuxtLink>
        </UiMoleculesEmptyState>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div v-for="product in products" :key="product.id" class="relative">
            <ProductCard :product="product" @notify-me="openNotifyMe" />
            <button
              class="absolute top-2 right-2 z-20 bg-white rounded-full p-1.5 shadow text-ink-600 hover:bg-ink-50"
              title="Ukloni"
              aria-label="Ukloni iz liste želja"
              @click="removeItem(product.id)"
            >
              <Icon name="lucide:x" class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <ProductNotifyMeModal v-model="notifyMeOpen" :product="notifyMeProduct" />
  </div>
</template>
