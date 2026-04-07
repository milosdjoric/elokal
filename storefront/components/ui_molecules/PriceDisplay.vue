<script setup lang="ts">
interface Props {
  price: string | number
  salePrice?: string | number | null
  salePercentage?: number | null
  isOnSale?: boolean
  unitPrice?: string | null
  size?: 'sm' | 'md' | 'lg'
}

withDefaults(defineProps<Props>(), { isOnSale: false, size: 'md' })

function format(value: string | number): string {
  return Number(value).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) + ' RSD'
}

const sizeClasses: Record<string, { main: string; old: string }> = {
  sm: { main: 'text-sm', old: 'text-xs' },
  md: { main: 'text-lg', old: 'text-sm' },
  lg: { main: 'text-2xl', old: 'text-base' },
}
</script>

<template>
  <div class="flex flex-wrap items-center gap-2">
    <template v-if="isOnSale && salePrice">
      <span class="font-bold text-red-600" :class="sizeClasses[size].main">{{ format(salePrice) }}</span>
      <span class="text-gray-400 line-through" :class="sizeClasses[size].old">{{ format(price) }}</span>
      <UiAtomsBadge v-if="salePercentage" variant="sale" size="sm">-{{ salePercentage }}%</UiAtomsBadge>
    </template>
    <template v-else>
      <span class="font-bold text-gray-900" :class="sizeClasses[size].main">{{ format(price) }}</span>
    </template>
    <span v-if="unitPrice" class="text-xs text-gray-400 block w-full">{{ unitPrice }}</span>
  </div>
</template>
