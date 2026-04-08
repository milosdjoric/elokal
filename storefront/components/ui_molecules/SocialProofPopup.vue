<script setup lang="ts">
import type { Product, PaginatedResponse } from '~/types'

const { get } = useApi()

const show = ref(false)
const currentProduct = ref<{ name: string; image: string | null; city: string } | null>(null)

const cities = ['Beograda', 'Novog Sada', 'Niša', 'Kragujevca', 'Subotice', 'Čačka', 'Leskovca', 'Valjeva', 'Kruševca', 'Zrenjanina']
const names = ['Marko', 'Ana', 'Nikola', 'Jelena', 'Stefan', 'Milica', 'Petar', 'Maja', 'Luka', 'Ivana']

let products: Product[] = []
let interval: ReturnType<typeof setInterval>

async function init() {
  try {
    const data = await get<PaginatedResponse<Product>>('/v1/products', { per_page: 20 })
    products = data.data
    if (products.length > 0) {
      // Prva pojava posle 15 sekundi
      setTimeout(showNext, 15000)
      // Ponavljaj na 30-60 sekundi
      interval = setInterval(showNext, 30000 + Math.random() * 30000)
    }
  }
  catch { /* silent */ }
}

function showNext() {
  if (products.length === 0) return
  const product = products[Math.floor(Math.random() * products.length)]
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  currentProduct.value = {
    name: product.name,
    image: img ? resolveImageUrl(img.image_path) : null,
    city: cities[Math.floor(Math.random() * cities.length)],
  }
  show.value = true
  setTimeout(() => { show.value = false }, 5000)
}

onMounted(init)
onUnmounted(() => clearInterval(interval))
</script>

<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="translate-y-full opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-full opacity-0"
  >
    <div v-if="show && currentProduct" class="fixed bottom-4 left-4 z-50 max-w-xs bg-white border border-gray-200 shadow-lg rounded-lg p-3 flex items-center gap-3">
      <img v-if="currentProduct.image" :src="currentProduct.image" class="w-12 h-12 object-cover rounded flex-shrink-0" alt="" />
      <div class="min-w-0">
        <p class="text-xs text-gray-500">
          <strong class="text-gray-700">{{ names[Math.floor(Math.random() * names.length)] }}</strong> iz {{ currentProduct.city }}
        </p>
        <p class="text-sm font-medium text-gray-800 truncate">kupio/la {{ currentProduct.name }}</p>
        <p class="text-xs text-gray-400">pre {{ Math.floor(Math.random() * 30) + 1 }} min</p>
      </div>
      <button class="absolute top-1 right-1 text-gray-300 hover:text-gray-500" @click="show = false">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
      </button>
    </div>
  </Transition>
</template>
