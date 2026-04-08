<script setup lang="ts">
const modelValue = defineModel<boolean>({ default: false })
const emit = defineEmits<{ select: [imagePath: string] }>()

const { get, getErrorMessage } = useApi()
const { error: toastError } = useToast()

interface MediaImage {
  id: number
  image_path: string
  alt_text: string | null
}

const images = ref<MediaImage[]>([])
const loading = ref(false)
const search = ref('')

async function fetchImages() {
  if (images.value.length > 0) return // Keširaj
  loading.value = true
  try {
    const data = await get<{ data: MediaImage[] }>('/admin/media')
    images.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

const filteredImages = computed(() => {
  if (!search.value) return images.value
  const q = search.value.toLowerCase()
  return images.value.filter(img =>
    img.image_path.toLowerCase().includes(q) ||
    img.alt_text?.toLowerCase().includes(q),
  )
})

function select(img: MediaImage) {
  emit('select', img.image_path)
  modelValue.value = false
}

watch(modelValue, (open) => {
  if (open) fetchImages()
})
</script>

<template>
  <UiMoleculesModal v-model="modelValue" title="Izaberi sliku" size="lg">
    <!-- Search -->
    <div class="mb-4">
      <input
        v-model="search"
        type="text"
        placeholder="Pretraži slike..."
        class="w-full px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-4 gap-3">
      <UiAtomsSkeleton v-for="i in 8" :key="i" height="100px" />
    </div>

    <!-- Empty -->
    <div v-else-if="filteredImages.length === 0" class="text-center py-12 text-gray-400 text-sm">
      Nema slika{{ search ? ' za pretragu' : '' }}.
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-4 gap-3 max-h-[400px] overflow-y-auto">
      <button
        v-for="img in filteredImages"
        :key="img.id"
        type="button"
        class="aspect-square border-2 border-transparent hover:border-primary-500 rounded overflow-hidden transition-colors focus:outline-none focus:border-primary-500"
        @click="select(img)"
      >
        <img
          :src="resolveImageUrl(img.image_path)"
          :alt="img.alt_text || ''"
          class="w-full h-full object-cover"
        />
      </button>
    </div>
  </UiMoleculesModal>
</template>
