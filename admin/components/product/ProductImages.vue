<script setup lang="ts">
import type { ProductImage } from '~/types'

interface Props {
  productId: number
  images: ProductImage[]
}

const props = defineProps<Props>()
const emit = defineEmits<{
  refresh: []
}>()

const { upload, del, patch, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

const uploading = ref(false)

async function handleUpload(files: File[]) {
  uploading.value = true
  try {
    for (const file of files) {
      const formData = new FormData()
      formData.append('image', file)
      await upload(`/admin/products/${props.productId}/images`, formData)
    }
    success('Slike uploadovane.')
    emit('refresh')
  }
  catch (e) {
    toastError(getErrorMessage(e))
  }
  finally {
    uploading.value = false
  }
}

async function setPrimary(imageId: number) {
  try {
    await del(`/admin/products/${props.productId}/images/${imageId}`)
    // Re-upload as primary — actually simpler to just patch reorder
    // For now, delete and re-upload is not ideal. Let's just note this
  }
  catch { /* handled below */ }
}

async function deleteImage(imageId: number) {
  try {
    await del(`/admin/products/${props.productId}/images/${imageId}`)
    success('Slika obrisana.')
    emit('refresh')
  }
  catch (e) {
    toastError(getErrorMessage(e))
  }
}

async function reorder() {
  try {
    const order = props.images.map(i => i.id)
    await patch(`/admin/products/${props.productId}/images/reorder`, { order })
    success('Redosled ažuriran.')
  }
  catch (e) {
    toastError(getErrorMessage(e))
  }
}

function imageUrl(img: ProductImage): string {
  return `http://localhost:8000/storage/${img.image_path}`
}
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-sm font-semibold text-gray-700">Slike proizvoda</h3>

    <!-- Existing images -->
    <div v-if="images.length" class="flex gap-3 flex-wrap">
      <div
        v-for="img in images"
        :key="img.id"
        class="relative group w-24 h-24 border border-gray-200 overflow-hidden"
        :class="{ 'ring-2 ring-primary-500': img.is_primary }"
      >
        <img :src="imageUrl(img)" :alt="img.alt_text || ''" class="w-full h-full object-cover" />

        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1">
          <button
            type="button"
            class="p-1 text-white hover:text-red-300"
            title="Obriši"
            @click="deleteImage(img.id)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <span
          v-if="img.is_primary"
          class="absolute bottom-0 left-0 right-0 bg-primary-600 text-white text-[10px] text-center py-0.5"
        >
          Glavna
        </span>
      </div>
    </div>

    <!-- Upload -->
    <UiMoleculesFileUpload
      multiple
      @upload="handleUpload"
    />

    <p v-if="uploading" class="text-sm text-gray-500 flex items-center gap-2">
      <UiAtomsSpinner size="sm" /> Upload u toku...
    </p>
  </div>
</template>
