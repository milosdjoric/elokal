<script setup lang="ts">
import type { Product } from '~/types'

defineProps<{
  products: Product[]
  loading?: boolean
  columns?: 3 | 4
  layout?: 'grid' | 'list' | 'compact'
}>()

const emit = defineEmits<{ quickView: [product: Product] }>()

const gridClasses = computed(() => {
  return {
    grid: 'grid grid-cols-2 md:grid-cols-3 gap-4',
    list: 'space-y-3',
    compact: 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3',
  }
})
</script>

<template>
  <div>
    <!-- Loading skeletons -->
    <div v-if="loading" :class="gridClasses[layout || 'grid']">
      <div v-for="i in 8" :key="i" class="border border-gray-200">
        <UiAtomsSkeleton :height="layout === 'list' ? '120px' : '200px'" />
        <div v-if="layout !== 'list'" class="p-3 space-y-2">
          <UiAtomsSkeleton height="1rem" width="80%" />
          <UiAtomsSkeleton height="1.25rem" width="50%" />
        </div>
      </div>
    </div>

    <!-- Products - List layout -->
    <div v-else-if="products.length && layout === 'list'" class="space-y-3">
      <ProductCardList
        v-for="product in products"
        :key="product.id"
        :product="product"
        @quick-view="emit('quickView', $event)"
      />
    </div>

    <!-- Products - Grid/Compact -->
    <div v-else-if="products.length" :class="gridClasses[layout || 'grid']">
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
        @quick-view="emit('quickView', $event)"
      />
    </div>

    <!-- Empty -->
    <div v-else class="text-center py-16">
      <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
      </svg>
      <p class="text-gray-500">Nema proizvoda za prikaz.</p>
    </div>
  </div>
</template>
