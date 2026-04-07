<script setup lang="ts">
import type { Product } from '~/types'

const props = defineProps<{ product: Product }>()
const emit = defineEmits<{ quickView: [product: Product] }>()
const { addToCart } = useCart()

const hovered = ref(false)

const primaryImg = computed(() => {
  const img = props.product.images?.find(i => i.is_primary) || props.product.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
})

const hoverImg = computed(() => {
  const imgs = props.product.images || []
  const secondary = imgs.find(i => !i.is_primary) || imgs[1]
  return secondary ? resolveImageUrl(secondary.image_path) : null
})

function handleAddToCart() {
  addToCart(props.product)
}
</script>

<template>
  <div
    class="group relative bg-white border border-gray-200 transition-shadow hover:shadow-md"
    @mouseenter="hovered = true"
    @mouseleave="hovered = false"
  >
    <!-- Image -->
    <NuxtLink :to="`/products/${product.slug}`" class="block relative aspect-square overflow-hidden">
      <img
        :src="hovered && hoverImg ? hoverImg : primaryImg || ''"
        :alt="product.name"
        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
      />

      <!-- Wishlist heart -->
      <div class="absolute top-2 right-2 z-10">
        <ProductWishlistButton :product-id="product.id" size="sm" />
      </div>

      <!-- Badges -->
      <div class="absolute top-2 left-2 flex flex-col gap-1">
        <UiAtomsBadge v-if="product.is_on_sale" variant="sale">-{{ product.sale_percentage }}%</UiAtomsBadge>
        <UiAtomsBadge v-if="product.featured" variant="new">Novo</UiAtomsBadge>
        <UiAtomsBadge v-if="product.stock_quantity === 0" variant="out">Nema</UiAtomsBadge>
      </div>

      <!-- Quick view overlay -->
      <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-end justify-center pb-4 opacity-0 group-hover:opacity-100">
        <button
          class="bg-white text-gray-800 text-xs font-semibold px-4 py-2 shadow hover:bg-gray-50 transition-colors"
          @click.prevent="emit('quickView', product)"
        >
          Brzi pregled
        </button>
      </div>
    </NuxtLink>

    <!-- Info -->
    <div class="p-3">
      <NuxtLink :to="`/products/${product.slug}`" class="block">
        <h3 class="text-sm font-medium text-gray-800 line-clamp-2 hover:text-primary-600 transition-colors">
          {{ product.name }}
        </h3>
      </NuxtLink>

      <div class="mt-2">
        <UiMoleculesPriceDisplay
          :price="product.price"
          :sale-price="product.sale_price"
          :sale-percentage="product.sale_percentage"
          :is-on-sale="product.is_on_sale"
          :unit-price="product.formatted_unit_price"
          size="sm"
        />
      </div>

      <button
        v-if="product.stock_quantity > 0"
        class="mt-3 w-full py-2 text-xs font-semibold text-primary-600 border border-primary-600 hover:bg-primary-600 hover:text-white transition-colors"
        @click="handleAddToCart"
      >
        Dodaj u korpu
      </button>
      <p v-else class="mt-3 text-xs text-gray-400 text-center py-2">Nema na stanju</p>
    </div>
  </div>
</template>
