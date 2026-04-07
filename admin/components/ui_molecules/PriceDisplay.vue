<script setup lang="ts">
interface Props {
  price: string | number
  salePrice?: string | number | null
  salePercentage?: number | null
  isOnSale?: boolean
}

withDefaults(defineProps<Props>(), {
  isOnSale: false,
})

function formatPrice(value: string | number): string {
  return Number(value).toLocaleString('sr-RS', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' RSD'
}
</script>

<template>
  <div class="flex items-center gap-2">
    <template v-if="isOnSale && salePrice">
      <span class="text-red-600 font-semibold">{{ formatPrice(salePrice) }}</span>
      <span class="text-gray-400 line-through text-sm">{{ formatPrice(price) }}</span>
      <UiAtomsBadge v-if="salePercentage" variant="danger">-{{ salePercentage }}%</UiAtomsBadge>
    </template>
    <template v-else>
      <span class="font-semibold text-gray-900">{{ formatPrice(price) }}</span>
    </template>
  </div>
</template>
