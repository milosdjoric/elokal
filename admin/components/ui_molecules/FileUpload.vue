<script setup lang="ts">
interface Props {
  accept?: string
  multiple?: boolean
  maxSize?: number
}

const props = withDefaults(defineProps<Props>(), {
  accept: 'image/jpeg,image/png,image/webp',
  multiple: false,
  maxSize: 5120,
})

const emit = defineEmits<{
  upload: [files: File[]]
}>()

const isDragging = ref(false)
const previews = ref<{ file: File; url: string }[]>([])
const fileInput = ref<HTMLInputElement>()

function handleFiles(files: FileList | null) {
  if (!files) return

  const validFiles: File[] = []
  for (const file of Array.from(files)) {
    if (file.size <= props.maxSize * 1024) {
      validFiles.push(file)
    }
  }

  previews.value = validFiles.map(file => ({
    file,
    url: URL.createObjectURL(file),
  }))

  if (validFiles.length) {
    emit('upload', validFiles)
  }
}

function onDrop(e: DragEvent) {
  isDragging.value = false
  handleFiles(e.dataTransfer?.files ?? null)
}

function onFileSelect(e: Event) {
  handleFiles((e.target as HTMLInputElement).files)
}

function removePreview(index: number) {
  URL.revokeObjectURL(previews.value[index].url)
  previews.value.splice(index, 1)
}

function browse() {
  fileInput.value?.click()
}

onUnmounted(() => {
  previews.value.forEach(p => URL.revokeObjectURL(p.url))
})
</script>

<template>
  <div>
    <div
      class="border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer"
      :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'"
      @dragover.prevent="isDragging = true"
      @dragleave="isDragging = false"
      @drop.prevent="onDrop"
      @click="browse"
    >
      <svg class="mx-auto w-10 h-10 text-gray-400 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
      </svg>
      <p class="text-sm text-gray-600">
        Prevucite slike ovde ili
        <span class="text-blue-600 font-medium">izaberite fajlove</span>
      </p>
      <p class="text-xs text-gray-400 mt-1">
        JPEG, PNG, WebP do {{ maxSize / 1024 }}MB
      </p>
    </div>

    <input
      ref="fileInput"
      type="file"
      :accept="accept"
      :multiple="multiple"
      class="hidden"
      @change="onFileSelect"
    />

    <!-- Previews -->
    <div v-if="previews.length" class="mt-4 flex gap-3 flex-wrap">
      <div
        v-for="(preview, index) in previews"
        :key="index"
        class="relative w-20 h-20 rounded-lg overflow-hidden border border-gray-200"
      >
        <img :src="preview.url" :alt="preview.file.name" class="w-full h-full object-cover" />
        <button
          type="button"
          class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600"
          @click.stop="removePreview(index)"
        >
          ×
        </button>
      </div>
    </div>
  </div>
</template>
