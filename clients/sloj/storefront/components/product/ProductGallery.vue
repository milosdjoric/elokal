<script setup lang="ts">
import type { ProductImage } from '~/types'

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
    <!-- Main image — Vitra-style: bela bg, slika object-contain -->
    <div class="relative aspect-square overflow-hidden bg-surface cursor-zoom-in mb-4 flex items-center justify-center" @click="openLightbox(activeIndex)">
      <img v-if="activeImage" :src="activeImage" :alt="sortedImages[activeIndex]?.alt_text || ''" class="max-w-[88%] max-h-[88%] object-contain" @error="onImageError($event, sortedImages[activeIndex]?.alt_text || 'sloj')" />
    </div>

    <!-- Thumbnails -->
    <div v-if="sortedImages.length > 1" class="flex gap-2 overflow-x-auto">
      <button
        v-for="(img, index) in sortedImages"
        :key="img.id"
        class="w-16 h-16 flex-shrink-0 bg-surface flex items-center justify-center transition-all"
        :class="index === activeIndex ? 'ring-1 ring-ink-800' : 'ring-1 ring-ink-100 hover:ring-ink-400'"
        @click="activeIndex = index"
      >
        <img :src="resolveImageUrl(img.image_path)" :alt="img.alt_text || ''" class="max-w-[80%] max-h-[80%] object-contain" @error="onImageError($event, img.alt_text || 'sloj', 160)" />
      </button>
    </div>

    <!-- Lightbox -->
    <Teleport to="body">
      <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" leave-active-class="transition duration-150" leave-to-class="opacity-0">
        <div v-if="lightboxOpen" class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center" @click.self="closeLightbox">
          <button class="absolute top-4 right-4 text-white/70 hover:text-white" aria-label="Zatvori" @click="closeLightbox">
            <Icon name="lucide:x" class="w-8 h-8" />
          </button>
          <button class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white" aria-label="Prethodna slika" @click="prevImage">
            <Icon name="lucide:chevron-left" class="w-10 h-10" />
          </button>
          <img v-if="activeImage" :src="activeImage" class="max-w-[90vw] max-h-[90vh] object-contain" @error="onImageError($event, sortedImages[activeIndex]?.alt_text || 'sloj', 1200)" />
          <button class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white" aria-label="Sledeća slika" @click="nextImage">
            <Icon name="lucide:chevron-right" class="w-10 h-10" />
          </button>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
