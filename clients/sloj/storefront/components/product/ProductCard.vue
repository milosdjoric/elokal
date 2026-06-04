<script setup lang="ts">
import type { Product } from '~/types'

const props = withDefaults(defineProps<{ product: Product; showSwatches?: boolean }>(), { showSwatches: true })
const emit = defineEmits<{
  quickView: [product: Product]
  notifyMe: [product: Product]
}>()
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

const designerLine = computed(() => {
  const meta = (props.product as Product & { designer?: string; meta?: { designer?: string } })
  return meta.designer || meta.meta?.designer || null
})

function handleAddToCart() {
  addToCart(props.product)
}
</script>

<template>
  <article
    class="group relative flex flex-col h-full"
    @mouseenter="hovered = true"
    @mouseleave="hovered = false"
  >
    <!-- IMAGE — Vitra-style: bela bg, slika object-contain, generous whitespace -->
    <NuxtLink :to="`/proizvodi/${product.slug}`" class="relative block aspect-square overflow-hidden bg-surface">
      <img
        v-if="primaryImg"
        :src="hovered && hoverImg ? hoverImg : primaryImg"
        :alt="product.name"
        loading="lazy"
        width="600"
        height="600"
        class="absolute inset-0 m-auto max-w-[80%] max-h-[80%] object-contain transition-transform duration-700 ease-out-soft group-hover:scale-[1.04]"
        @error="onImageError($event, product.name)"
      />
      <div v-else class="w-full h-full bg-placeholder" />

      <!-- Sale signal — vrlo suptilan -->
      <p v-if="product.is_on_sale && product.sale_percentage" class="absolute top-3 left-3 text-[12px] text-terra-600 tabular-nums z-10">
        −{{ product.sale_percentage }}%
      </p>
      <p v-else-if="product.stock_quantity === 0" class="absolute top-3 left-3 text-[12px] text-ink-500 z-10">
        Rasprodato
      </p>
      <p v-else-if="product.featured" class="absolute top-3 left-3 text-[12px] text-ink-500 z-10">
        Novo
      </p>

      <!-- Wishlist — fade on hover -->
      <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
        <ProductWishlistButton :product-id="product.id" size="sm" />
      </div>
    </NuxtLink>

    <!-- INFO — Vitra calm hierarchy, centralno poravnano -->
    <div class="flex flex-col flex-1 gap-1 pt-5 text-center">
      <NuxtLink :to="`/proizvodi/${product.slug}`" class="block">
        <h3 class="text-[15px] font-medium text-ink-800 leading-snug line-clamp-2 group-hover:text-terra-600 transition-colors">
          {{ product.name }}
        </h3>
      </NuxtLink>

      <p v-if="designerLine" class="text-[13px] text-ink-500 leading-snug">
        {{ designerLine }}
      </p>

      <div class="mt-2 flex justify-center">
        <UiMoleculesPriceDisplay
          :price="product.price"
          :sale-price="product.sale_price"
          :sale-percentage="product.sale_percentage"
          :is-on-sale="product.is_on_sale"
          :unit-price="product.formatted_unit_price"
          size="sm"
        />
      </div>

      <!-- Hover CTAs — desktop only, fade in -->
      <div class="hidden md:flex mt-3 items-center gap-4 justify-center opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity duration-200">
        <button
          v-if="product.stock_quantity > 0"
          type="button"
          class="text-[13px] text-ink-700 hover:text-terra-600 border-b border-ink-300 hover:border-terra-500 pb-0.5 transition-colors"
          @click="handleAddToCart"
        >
          Dodaj u korpu
        </button>

        <button
          v-else
          type="button"
          class="text-[13px] text-ink-700 hover:text-terra-600 border-b border-ink-300 hover:border-terra-500 pb-0.5 transition-colors"
          @click.prevent="emit('notifyMe', product)"
        >
          Obavesti me
        </button>

        <button
          type="button"
          aria-label="Brzi pregled"
          class="text-[13px] text-ink-500 hover:text-terra-600 transition-colors"
          @click.prevent.stop="emit('quickView', product)"
        >
          Brzi pregled
        </button>
      </div>
    </div>
  </article>
</template>
