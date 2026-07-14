<script setup lang="ts">
const compareStore = useCompareStore()

const { isEnabled } = useFeature()
const featureActive = computed(() => isEnabled('feature_compare', true))

const primaryImage = (product: { images?: Array<{ image_path: string; is_primary: boolean }> }) => {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
}
</script>

<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="translate-y-full opacity-0"
    leave-active-class="transition duration-150 ease-in"
    leave-to-class="translate-y-full opacity-0"
  >
    <div v-if="featureActive && compareStore.count > 0" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 py-3">
      <div class="max-w-7xl mx-auto px-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 overflow-x-auto">
          <div
            v-for="item in compareStore.items"
            :key="item.id"
            class="flex items-center gap-2 bg-gray-50 border border-gray-200 px-2 py-1 rounded flex-shrink-0"
          >
            <img
              v-if="primaryImage(item)"
              :src="primaryImage(item)!"
              :alt="item.name"
              class="w-8 h-8 object-cover rounded"
            />
            <span class="text-xs text-gray-700 max-w-24 truncate">{{ item.name }}</span>
            <button
              class="text-gray-400 hover:text-red-500 text-xs ml-1"
              @click="compareStore.remove(item.id)"
            >
              ✕
            </button>
          </div>

          <!-- Empty slots -->
          <div
            v-for="i in (4 - compareStore.count)"
            :key="`empty-${i}`"
            class="w-10 h-10 border-2 border-dashed border-gray-200 rounded flex items-center justify-center flex-shrink-0"
          >
            <span class="text-gray-300 text-xs">+</span>
          </div>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
          <span class="text-xs text-gray-500">{{ compareStore.count }}/4</span>
          <NuxtLink to="/uporedi">
            <UiAtomsButton size="sm" :disabled="compareStore.count < 2">Uporedi</UiAtomsButton>
          </NuxtLink>
          <button class="text-xs text-gray-400 hover:text-red-500" @click="compareStore.clear()">Obriši</button>
        </div>
      </div>
    </div>
  </Transition>
</template>
