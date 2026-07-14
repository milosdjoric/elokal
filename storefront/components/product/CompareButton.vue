<script setup lang="ts">
import type { Product } from '~/types'

const props = defineProps<{
  product: Product
  size?: 'sm' | 'md'
}>()

const store = useCompareStore()
const isActive = computed(() => store.isInCompare(props.product.id))

const { isEnabled } = useFeature()
const featureActive = computed(() => isEnabled('feature_compare', true))

function toggle() {
  store.toggle(props.product)
}
</script>

<template>
  <button
    v-if="featureActive"
    type="button"
    :title="isActive ? 'Ukloni iz poređenja' : 'Dodaj u poređenje'"
    class="group transition-colors"
    :class="size === 'sm' ? 'p-1' : 'p-2'"
    @click.prevent.stop="toggle"
  >
    <svg
      :class="[
        size === 'sm' ? 'w-5 h-5' : 'w-6 h-6',
        isActive ? 'text-primary-600' : 'text-gray-400 group-hover:text-primary-400',
      ]"
      fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
    >
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
    </svg>
  </button>
</template>
