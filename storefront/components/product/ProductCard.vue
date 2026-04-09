<script setup lang="ts">
import type { Product } from '~/types'

const props = withDefaults(defineProps<{ product: Product; showSwatches?: boolean }>(), { showSwatches: true })
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

// Izvuci unikatne swatch vrednosti iz varijanti (samo atributi sa is_visible_on_card)
const variantSwatches = computed(() => {
  if (!props.product.variants?.length) return []
  const seen = new Set<number>()
  const swatches: Array<{ value_id: number; value: string; type: string; color_hex: string | null }> = []

  for (const variant of props.product.variants) {
    if (!variant.attributes) continue
    for (const attr of variant.attributes) {
      if (seen.has(attr.value_id)) continue
      seen.add(attr.value_id)
      swatches.push({
        value_id: attr.value_id,
        value: attr.value,
        type: attr.attribute_type,
        color_hex: attr.color_hex,
      })
    }
  }
  return swatches
})
</script>

<template>
  <div
    class="group relative bg-white border border-gray-200 transition-shadow hover:shadow-md flex flex-col h-full"
    @mouseenter="hovered = true"
    @mouseleave="hovered = false"
  >
    <!-- Image -->
    <NuxtLink :to="`/proizvodi/${product.slug}`" class="block relative aspect-square overflow-hidden">
      <img
        :src="hovered && hoverImg ? hoverImg : primaryImg || ''"
        :alt="product.name"
        loading="lazy"
        width="400"
        height="400"
        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
      />

      <!-- Actions: wishlist + compare -->
      <div class="absolute top-2 right-2 z-10 flex flex-col gap-1">
        <ProductWishlistButton :product-id="product.id" size="sm" />
        <ProductCompareButton :product="product" size="sm" />
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
    <div class="p-3 flex flex-col flex-1 gap-2">
      <NuxtLink :to="`/proizvodi/${product.slug}`" class="block">
        <h3 class="text-sm font-medium text-gray-800 line-clamp-2 min-h-[2.5rem] hover:text-primary-600 transition-colors">
          {{ product.name }}
        </h3>
      </NuxtLink>

      <div class="">
        <UiMoleculesPriceDisplay
          :price="product.price"
          :sale-price="product.sale_price"
          :sale-percentage="product.sale_percentage"
          :is-on-sale="product.is_on_sale"
          :unit-price="product.formatted_unit_price"
          size="sm"
        />
      </div>

      <ProductSaleCountdown v-if="product.is_on_sale && product.sale_price_to" :ends-at="product.sale_price_to" size="sm" class="mt-1" />

      <!-- Variant swatches (max 5) -->
      <div v-if="showSwatches && variantSwatches.length > 0" class="mt-2 flex flex-wrap items-center gap-1">
        <template v-for="swatch in variantSwatches.slice(0, 5)" :key="swatch.value_id">
          <span
            v-if="swatch.type === 'color' && swatch.color_hex"
            class="w-4 h-4 rounded-full border border-gray-300"
            :style="{ backgroundColor: swatch.color_hex }"
            :title="swatch.value"
          />
          <span
            v-else
            class="text-[10px] text-gray-500 border border-gray-200 px-1.5 py-0.5"
          >
            {{ swatch.value }}
          </span>
        </template>
        <span v-if="variantSwatches.length > 5" class="text-[10px] text-gray-400">+{{ variantSwatches.length - 5 }}</span>
      </div>

      <button
        v-if="product.stock_quantity > 0"
        class="mt-auto pt-3 w-full py-2 text-xs font-semibold text-primary-600 border border-primary-600 hover:bg-primary-600 hover:text-white transition-colors"
        @click="handleAddToCart"
      >
        Dodaj u korpu
      </button>
      <p v-else class="mt-auto pt-3 text-xs text-gray-400 text-center py-2">Nema na stanju</p>
    </div>
  </div>
</template>
