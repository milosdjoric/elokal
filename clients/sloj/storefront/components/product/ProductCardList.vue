<script setup lang="ts">
import type { Product } from '~/types'

const props = defineProps<{ product: Product }>()
const emit = defineEmits<{
  quickView: [product: Product]
  notifyMe: [product: Product]
}>()
const { addToCart } = useCart()

const primaryImg = computed(() => {
  const img = props.product.images?.find(i => i.is_primary) || props.product.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
})

const lowStock = computed(() => props.product.stock_quantity > 0 && props.product.stock_quantity <= 5)
</script>

<template>
  <article class="group flex bg-white border border-gray-200 transition-all duration-180 ease-out hover:border-primary-400 hover:shadow-md overflow-hidden">
    <!-- Image -->
    <NuxtLink :to="`/proizvodi/${product.slug}`" class="relative flex-shrink-0 w-40 md:w-52 aspect-square overflow-hidden bg-placeholder">
      <img
        :src="primaryImg || ''"
        :alt="product.name"
        loading="lazy"
        width="400"
        height="400"
        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
      />

      <!-- Badges (gornji-levi) -->
      <div class="absolute top-2 left-2 flex flex-col gap-1">
        <UiAtomsBadge v-if="product.is_on_sale && product.sale_percentage" variant="sale">−{{ product.sale_percentage }}%</UiAtomsBadge>
        <UiAtomsBadge v-if="product.featured" variant="new">Novo</UiAtomsBadge>
        <UiAtomsBadge v-if="product.stock_quantity === 0" variant="out">Rasprodato</UiAtomsBadge>
      </div>
    </NuxtLink>

    <!-- Info -->
    <div class="flex-1 p-4 flex flex-col justify-between min-w-0">
      <div>
        <div class="flex items-start justify-between gap-3">
          <NuxtLink :to="`/proizvodi/${product.slug}`" class="block min-w-0">
            <h3 class="text-sm md:text-base font-medium text-gray-900 hover:text-primary-700 transition-colors line-clamp-2">
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

        <p v-if="lowStock" class="text-[11px] font-medium text-warning tabular-nums mt-2">
          ⏱ Još {{ product.stock_quantity }} {{ product.stock_quantity === 1 ? 'komad' : 'komada' }}
        </p>
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
            type="button"
            class="hidden md:inline-flex text-xs text-gray-500 hover:text-primary-700 transition-colors px-2 py-1"
            @click="emit('quickView', product)"
          >
            Brzi pregled
          </button>
          <button
            v-if="product.stock_quantity > 0"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold bg-gray-900 text-white hover:bg-primary-700 transition-colors"
            @click="addToCart(product)"
          >
            <Icon name="lucide:shopping-bag" class="w-3.5 h-3.5" />
            <span>Dodaj</span>
          </button>
          <button
            v-else
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold border border-gray-300 text-gray-700 hover:border-primary-500 hover:text-primary-700 transition-colors"
            @click="emit('notifyMe', product)"
          >
            Obavesti me
          </button>
        </div>
      </div>
    </div>
  </article>
</template>
