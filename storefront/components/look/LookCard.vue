<script setup lang="ts">
interface LookProduct {
  id: number
  name: string
  slug: string
  price: string
  effective_price: string
  images?: { image_path: string; is_primary: boolean }[]
}

interface Hotspot {
  product_id: number
  x_position: number
  y_position: number
  label: string
  product?: LookProduct
}

interface Look {
  id: number
  title: string
  image_path: string
  hotspots: Hotspot[]
}

const props = defineProps<{ look: Look }>()

const { addToCart } = useCart()

const activeHotspot = ref<number | null>(null)

function primaryImage(product: LookProduct): string | null {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  if (!img) return null
  return resolveImageUrl(img.image_path)
}

function formatPrice(price: string): string {
  return parseFloat(price).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) + ' RSD'
}

function addAllToCart() {
  for (const hs of props.look.hotspots) {
    if (hs.product) {
      addToCart(hs.product as never, 1)
    }
  }
}
</script>

<template>
  <div class="relative overflow-hidden rounded-lg border border-gray-200 group">
    <div class="relative aspect-[4/3]">
      <img :src="resolveImageUrl(look.image_path)" :alt="look.title" class="w-full h-full object-cover" />

      <!-- Hotspot markeri -->
      <button
        v-for="(hs, i) in look.hotspots"
        :key="i"
        class="absolute w-7 h-7 -translate-x-1/2 -translate-y-1/2 bg-white border-2 border-primary-600 rounded-full flex items-center justify-center text-xs font-bold text-primary-600 shadow-lg hover:scale-110 transition-transform z-10"
        :style="{ left: `${hs.x_position}%`, top: `${hs.y_position}%` }"
        @mouseenter="activeHotspot = i"
        @mouseleave="activeHotspot = null"
        @click="activeHotspot = activeHotspot === i ? null : i"
      >
        +
      </button>

      <!-- Hotspot popup -->
      <Transition
        v-for="(hs, i) in look.hotspots"
        :key="`popup-${i}`"
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="activeHotspot === i && hs.product"
          class="absolute z-20 bg-white rounded-lg shadow-xl border border-gray-200 p-3 w-48"
          :style="{
            left: `${Math.min(hs.x_position, 70)}%`,
            top: `${hs.y_position + 4}%`,
          }"
        >
          <NuxtLink :to="`/products/${hs.product.slug}`" class="block">
            <img v-if="primaryImage(hs.product)" :src="primaryImage(hs.product)!" class="w-full h-24 object-cover rounded mb-2" />
            <p class="text-sm font-medium text-gray-800 truncate">{{ hs.product.name }}</p>
            <p class="text-sm text-primary-600 font-semibold">{{ formatPrice(hs.product.effective_price || hs.product.price) }}</p>
          </NuxtLink>
        </div>
      </Transition>
    </div>

    <div class="p-4 flex items-center justify-between">
      <div>
        <h3 class="font-semibold text-gray-800">{{ look.title }}</h3>
        <p class="text-xs text-gray-500">{{ look.hotspots.length }} proizvoda</p>
      </div>
      <button
        class="px-3 py-1.5 text-xs font-medium rounded bg-primary-600 text-white hover:bg-primary-700 transition-colors"
        @click="addAllToCart"
      >
        Dodaj sve u korpu
      </button>
    </div>
  </div>
</template>
