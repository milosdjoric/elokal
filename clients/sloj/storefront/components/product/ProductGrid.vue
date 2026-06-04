<script setup lang="ts">
import type { Product } from '~/types'

defineProps<{
  products: Product[]
  loading?: boolean
  columns?: 3 | 4
  layout?: 'grid' | 'list' | 'compact'
}>()

const emit = defineEmits<{
  quickView: [product: Product]
  notifyMe: [product: Product]
}>()

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
        @notify-me="emit('notifyMe', $event)"
      />
    </div>

    <!-- Products - Grid/Compact -->
    <div v-else-if="products.length" :class="gridClasses[layout || 'grid']">
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
        @quick-view="emit('quickView', $event)"
        @notify-me="emit('notifyMe', $event)"
      />
    </div>

    <!-- Empty -->
    <UiMoleculesEmptyState
      v-else
      variant="search"
      title="Nema proizvoda za prikaz"
      description="Pokušajte da promenite filtere ili izaberete drugu kategoriju."
    />
  </div>
</template>
