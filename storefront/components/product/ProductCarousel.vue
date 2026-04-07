<script setup lang="ts">
import type { Product } from '~/types'

defineProps<{ title: string; products: Product[] }>()
const scrollRef = ref<HTMLElement>()

function scrollLeft() { scrollRef.value?.scrollBy({ left: -300, behavior: 'smooth' }) }
function scrollRight() { scrollRef.value?.scrollBy({ left: 300, behavior: 'smooth' }) }
</script>

<template>
  <section v-if="products.length" class="relative">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-bold text-gray-800">{{ title }}</h2>
      <div class="flex gap-2">
        <button class="w-8 h-8 border border-gray-300 flex items-center justify-center hover:bg-gray-50" @click="scrollLeft">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        </button>
        <button class="w-8 h-8 border border-gray-300 flex items-center justify-center hover:bg-gray-50" @click="scrollRight">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </button>
      </div>
    </div>
    <div ref="scrollRef" class="flex gap-4 overflow-x-auto scrollbar-hide pb-2 -mx-1 px-1 snap-x">
      <div v-for="product in products" :key="product.id" class="w-56 flex-shrink-0 snap-start">
        <ProductCard :product="product" />
      </div>
    </div>
  </section>
</template>
