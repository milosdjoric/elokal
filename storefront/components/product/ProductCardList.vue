<script setup lang="ts">
import type { Product } from '~/types'

const props = defineProps<{ product: Product }>()
const emit = defineEmits<{ quickView: [product: Product] }>()
const { addToCart } = useCart()

const primaryImg = computed(() => {
  const img = props.product.images?.find(i => i.is_primary) || props.product.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
})
</script>

<template>
  <div class="group flex bg-white border border-gray-200 transition-shadow hover:shadow-md overflow-hidden">
    <!-- Image -->
    <NuxtLink :to="`/proizvodi/${product.slug}`" class="flex-shrink-0 w-40 md:w-52 aspect-square overflow-hidden">
      <img
        :src="primaryImg || ''"
        :alt="product.name"
        loading="lazy"
        width="400"
        height="400"
        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
      />
    </NuxtLink>

    <!-- Info -->
    <div class="flex-1 p-4 flex flex-col justify-between min-w-0">
      <div>
        <div class="flex items-start justify-between gap-3">
          <NuxtLink :to="`/proizvodi/${product.slug}`" class="block">
            <h3 class="text-sm md:text-base font-medium text-gray-800 hover:text-primary-600 transition-colors line-clamp-2">
              {{ product.name }}
            </h3>
          </NuxtLink>
          <div class="flex gap-1 flex-shrink-0">
            <ProductWishlistButton :product-id="product.id" size="sm" />
            <ProductCompareButton :product="product" size="sm" />
          </div>
        </div>

        <p v-if="product.short_description" class="text-xs text-gray-500 mt-1 line-clamp-2">
          {{ product.short_description }}
        </p>

        <!-- Badges -->
        <div class="flex gap-1 mt-2">
          <UiAtomsBadge v-if="product.is_on_sale" variant="sale">-{{ product.sale_percentage }}%</UiAtomsBadge>
          <UiAtomsBadge v-if="product.featured" variant="new">Novo</UiAtomsBadge>
        </div>
      </div>

      <div class="flex items-center justify-between gap-4 mt-3">
        <UiMoleculesPriceDisplay
          :price="product.price"
          :sale-price="product.sale_price"
          :sale-percentage="product.sale_percentage"
          :is-on-sale="product.is_on_sale"
          :unit-price="product.formatted_unit_price"
          size="sm"
        />

        <div class="flex items-center gap-2 flex-shrink-0">
          <button
            class="text-xs text-gray-500 hover:text-primary-600 transition-colors"
            @click="emit('quickView', product)"
          >
            Brzi pregled
          </button>
          <button
            v-if="product.stock_quantity > 0"
            class="px-4 py-2 text-xs font-semibold text-primary-600 border border-primary-600 hover:bg-primary-600 hover:text-white transition-colors"
            @click="addToCart(product)"
          >
            Dodaj u korpu
          </button>
          <span v-else class="text-xs text-gray-400">Nema na stanju</span>
        </div>
      </div>
    </div>
  </div>
</template>
