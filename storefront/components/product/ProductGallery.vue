<script setup lang="ts">
import type { ProductImage } from '~/types'
// Eksplicitan import (golo u template-u) — layer-safe za nasleđivanje u klijentima.
import { resolveImageUrl } from '../../utils/image'

const props = defineProps<{ images: ProductImage[] }>()

const activeIndex = ref(0)
const lightboxOpen = ref(false)

const sortedImages = computed(() =>
  [...props.images].sort((a, b) => a.sort_order - b.sort_order)
)

const activeImage = computed(() =>
  sortedImages.value[activeIndex.value] ? resolveImageUrl(sortedImages.value[activeIndex.value].image_path) : null
)

function openLightbox(index: number) {
  activeIndex.value = index
  lightboxOpen.value = true
}

function closeLightbox() { lightboxOpen.value = false }
function nextImage() { activeIndex.value = (activeIndex.value + 1) % sortedImages.value.length }
function prevImage() { activeIndex.value = (activeIndex.value - 1 + sortedImages.value.length) % sortedImages.value.length }

// Reset na prvu sliku kad se promene slike (npr. izbor varijante)
watch(() => props.images, () => { activeIndex.value = 0 })
</script>

<template>
  <div>
    <!-- Main image -->
    <div class="relative aspect-square overflow-hidden border border-gray-200 cursor-zoom-in mb-3" @click="openLightbox(activeIndex)">
      <img v-if="activeImage" :src="activeImage" :alt="sortedImages[activeIndex]?.alt_text || ''" class="w-full h-full object-cover" />
    </div>

    <!-- Thumbnails -->
    <div v-if="sortedImages.length > 1" class="flex gap-2 overflow-x-auto">
      <button
        v-for="(img, index) in sortedImages"
        :key="img.id"
        class="w-16 h-16 flex-shrink-0 border-2 overflow-hidden transition-colors"
        :class="index === activeIndex ? 'border-primary-600' : 'border-gray-200 hover:border-gray-400'"
        @click="activeIndex = index"
      >
        <img :src="resolveImageUrl(img.image_path)" :alt="img.alt_text || ''" class="w-full h-full object-cover" />
      </button>
    </div>

    <!-- Lightbox -->
    <Teleport to="body">
      <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" leave-active-class="transition duration-150" leave-to-class="opacity-0">
        <div v-if="lightboxOpen" class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center" @click.self="closeLightbox">
          <button class="absolute top-4 right-4 text-white/70 hover:text-white" @click="closeLightbox">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
          <button class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white" @click="prevImage">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
          </button>
          <img v-if="activeImage" :src="activeImage" class="max-w-[90vw] max-h-[90vh] object-contain" />
          <button class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white" @click="nextImage">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
          </button>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
