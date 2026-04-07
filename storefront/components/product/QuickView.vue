<script setup lang="ts">
import type { Product } from '~/types'

const props = defineProps<{ modelValue: boolean; product: Product | null }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()
const { addToCart } = useCart()
const qty = ref(1)

function primaryImg(): string | null {
  const img = props.product?.images?.find(i => i.is_primary) || props.product?.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
}

function handleAdd() {
  if (props.product) {
    addToCart(props.product, qty.value)
    emit('update:modelValue', false)
    qty.value = 1
  }
}

watch(() => props.modelValue, (v) => { if (v) qty.value = 1 })
</script>

<template>
  <UiMoleculesModal :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
    <div v-if="product" class="flex flex-col md:flex-row gap-6">
      <div class="md:w-1/2">
        <img v-if="primaryImg()" :src="primaryImg()!" :alt="product.name" class="w-full aspect-square object-cover" />
      </div>
      <div class="md:w-1/2">
        <h2 class="text-lg font-bold text-gray-800 mb-2">{{ product.name }}</h2>
        <UiMoleculesPriceDisplay
          :price="product.price"
          :sale-price="product.sale_price"
          :sale-percentage="product.sale_percentage"
          :is-on-sale="product.is_on_sale"
          size="md"
          class="mb-4"
        />
        <p v-if="product.short_description" class="text-sm text-gray-600 mb-4">{{ product.short_description }}</p>

        <div v-if="product.stock_quantity > 0" class="flex items-center gap-3 mb-4">
          <UiMoleculesQuantitySelector v-model="qty" />
          <UiAtomsButton @click="handleAdd">Dodaj u korpu</UiAtomsButton>
        </div>
        <p v-else class="text-red-500 text-sm mb-4">Nema na stanju</p>

        <NuxtLink :to="`/products/${product.slug}`" class="text-sm text-primary-600 hover:underline">
          Pogledaj detalje →
        </NuxtLink>
      </div>
    </div>
  </UiMoleculesModal>
</template>
