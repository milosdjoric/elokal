<script setup lang="ts">
import type { Product } from '~/types'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Lista želja — eLokal' })

const authStore = useAuthStore()
const { apiBase } = useApi()
const wishlistStore = useWishlistStore()

const products = ref<Product[]>([])
const loading = ref(true)

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
    <LayoutBreadcrumbs :items="[{ label: 'Moj nalog', to: '/account' }, { label: 'Lista želja' }]" />

    <div class="flex flex-col lg:flex-row gap-8">
      <LayoutAccountSidebar />

      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Lista želja</h1>

        <div v-if="loading" class="space-y-3">
          <UiAtomsSkeleton v-for="i in 3" :key="i" height="80px" />
        </div>

        <div v-else-if="products.length === 0" class="text-center py-16 text-gray-500">
          <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
          </svg>
          <p>Vaša lista želja je prazna.</p>
          <NuxtLink to="/products" class="inline-block mt-4">
            <UiAtomsButton variant="outline">Pogledaj proizvode</UiAtomsButton>
          </NuxtLink>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div v-for="product in products" :key="product.id" class="relative">
            <ProductCard :product="product" />
            <button
              class="absolute top-2 right-2 z-20 bg-white rounded-full p-1.5 shadow text-red-500 hover:bg-red-50"
              title="Ukloni"
              @click="removeItem(product.id)"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
