<script setup lang="ts">
interface Props {
  price: string | number
  salePrice?: string | number | null
  salePercentage?: number | null
  isOnSale?: boolean
  unitPrice?: string | null
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), { isOnSale: false, size: 'md' })

const sizeClasses: Record<string, { main: string; currency: string; old: string }> = {
  sm: { main: 'text-[14px]', currency: 'text-[11px] ml-1', old: 'text-[12px]' },
  md: { main: 'text-[18px]', currency: 'text-[12px] ml-1', old: 'text-[13px]' },
  lg: { main: 'text-[24px]', currency: 'text-[14px] ml-1.5', old: 'text-[15px]' },
}

const mainParts = computed(() => formatPriceParts(props.isOnSale && props.salePrice ? props.salePrice : props.price))
const oldParts = computed(() => (props.isOnSale && props.salePrice ? formatPriceParts(props.price) : null))
</script>

<template>
  <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
    <span
      class="font-normal tabular-nums leading-none"
      :class="[sizeClasses[size].main, isOnSale ? 'text-terra-600' : 'text-ink-800']"
    >{{ mainParts.amount }}<span class="font-light text-ink-500" :class="sizeClasses[size].currency">{{ mainParts.currency }}</span></span>

    <span
      v-if="oldParts"
      class="text-ink-400 line-through tabular-nums font-light"
      :class="sizeClasses[size].old"
    >{{ oldParts.amount }}<span class="ml-0.5">{{ oldParts.currency }}</span></span>

    <span v-if="unitPrice" class="text-[11px] text-ink-400 block w-full tracking-[0.04em]">{{ unitPrice }}</span>
  </div>
</template>
