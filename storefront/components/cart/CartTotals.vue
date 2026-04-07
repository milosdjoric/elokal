<script setup lang="ts">
import type { Product } from '~/types'

const { total, count } = useCart()
const { get } = useApi()

const featuredProducts = ref<Product[]>([])

async function fetchCrossSell() {
  try {
    const data = await get<{ data: Product[] }>('/v1/products', { featured: 1, per_page: 4 })
    featuredProducts.value = data.data
  }
  catch { /* silent */ }
}

function formatPrice(price: string): string {
  return `${parseFloat(price).toLocaleString('sr-RS', { minimumFractionDigits: 2 })} RSD`
}

function primaryImage(product: Product): string | null {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  if (!img) return null
  const path = img.image_path
  if (path.startsWith('http')) return path
  const { apiBase } = useApi()
  return `${apiBase.replace('/api', '')}/storage/${path}`
}

onMounted(fetchCrossSell)
</script>

<template>
  <div class="space-y-6">
    <!-- Totals -->
    <div class="border border-gray-200 p-6 space-y-3">
      <h3 class="text-lg font-bold mb-4">Pregled narudžbine</h3>

      <div class="flex justify-between text-sm">
        <span class="text-gray-600">Stavke ({{ count }})</span>
        <span class="font-medium">{{ total }}</span>
      </div>
      <div class="flex justify-between text-sm">
        <span class="text-gray-600">Dostava</span>
        <span class="text-green-600 font-medium">Besplatno</span>
      </div>
      <div class="flex justify-between text-sm">
        <span class="text-gray-600">Porez</span>
        <span class="text-gray-400">Uračunat u cenu</span>
      </div>
      <hr class="my-2" />
      <div class="flex justify-between text-lg font-bold">
        <span>Ukupno</span>
        <span>{{ total }}</span>
      </div>

      <NuxtLink to="/checkout">
        <UiAtomsButton class="w-full mt-4" size="lg">Nastavi ka plaćanju</UiAtomsButton>
      </NuxtLink>

      <NuxtLink to="/products" class="block text-center text-sm text-primary-600 hover:text-primary-800 mt-2">
        ← Nastavi kupovinu
      </NuxtLink>
    </div>

    <!-- Cross-sell -->
    <div v-if="featuredProducts.length > 0" class="border border-gray-200 p-6">
      <h3 class="text-sm font-semibold text-gray-800 mb-4">Možda vas zanima</h3>
      <div class="space-y-3">
        <NuxtLink
          v-for="product in featuredProducts"
          :key="product.id"
          :to="`/products/${product.slug}`"
          class="flex items-center gap-3 hover:bg-gray-50 -mx-2 px-2 py-1 rounded transition-colors"
        >
          <img
            v-if="primaryImage(product)"
            :src="primaryImage(product)!"
            :alt="product.name"
            class="w-12 h-12 object-cover rounded"
          />
          <div v-else class="w-12 h-12 bg-gray-100 rounded flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate">{{ product.name }}</p>
            <p class="text-sm text-primary-600">{{ formatPrice(product.effective_price) }}</p>
          </div>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>
