<script setup lang="ts">
import type { CartItem } from '~/types'

const props = defineProps<{ item: CartItem }>()
const { updateQuantity, removeFromCart } = useCart()

const img = computed(() => {
  const i = props.item.product.images?.find(i => i.is_primary) || props.item.product.images?.[0]
  return i ? resolveImageUrl(i.image_path) : null
})

const lineTotal = computed(() => {
  const price = parseFloat(props.item.product.effective_price)
  return (price * props.item.quantity).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) + ' RSD'
})
</script>

<template>
  <div class="flex gap-3">
    <img v-if="img" :src="img" :alt="item.product.name" class="w-20 h-20 object-cover flex-shrink-0 border border-gray-200" />
    <div class="flex-1 min-w-0">
      <NuxtLink :to="`/proizvodi/${item.product.slug}`" class="text-sm font-medium text-gray-800 hover:text-primary-600 line-clamp-2">
        {{ item.product.name }}
      </NuxtLink>
      <p class="text-xs text-gray-500 mt-0.5">{{ Number(item.product.effective_price).toLocaleString('sr-RS') }} RSD</p>
      <div class="flex items-center justify-between mt-2">
        <UiMoleculesQuantitySelector v-model="item.quantity" @update:model-value="updateQuantity(item.product.id, $event)" />
        <div class="text-right">
          <p class="text-sm font-semibold">{{ lineTotal }}</p>
          <button class="text-xs text-red-500 hover:underline mt-0.5" @click="removeFromCart(item.product.id)">Ukloni</button>
        </div>
      </div>
    </div>
  </div>
</template>
