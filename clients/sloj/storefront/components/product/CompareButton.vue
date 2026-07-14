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
    :aria-label="isActive ? 'Ukloni iz poređenja' : 'Dodaj u poređenje'"
    class="group transition-colors"
    :class="size === 'sm' ? 'p-1' : 'p-2'"
    @click.prevent.stop="toggle"
  >
    <Icon
      name="lucide:scale"
      :class="[
        size === 'sm' ? 'w-5 h-5' : 'w-6 h-6',
        isActive ? 'text-primary-700' : 'text-gray-400 group-hover:text-primary-500',
      ]"
    />
  </button>
</template>
