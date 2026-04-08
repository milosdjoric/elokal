<script setup lang="ts">
const modelValue = defineModel<boolean>({ default: false })
const emit = defineEmits<{ select: [imagePath: string] }>()

const { get, post, getErrorMessage } = useApi()
const { error: toastError } = useToast()

interface MediaImage {
  id: number
  image_path: string
  alt_text: string | null
  folder_id: number | null
}

interface MediaFolder {
  id: number
  name: string
  parent_id: number | null
  images_count: number
  children?: MediaFolder[]
}

const images = ref<MediaImage[]>([])
const folders = ref<MediaFolder[]>([])
const loading = ref(false)
const search = ref('')
const activeFolderId = ref<number | null>(null)
const newFolderName = ref('')
const showNewFolder = ref(false)

async function fetchImages() {
  loading.value = true
  try {
    const params: Record<string, string | number | null> = {}
    if (activeFolderId.value !== null) params.folder_id = activeFolderId.value
    const data = await get<{ data: MediaImage[] }>('/admin/media', params as Record<string, string | number>)
    images.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function fetchFolders() {
  try {
    const data = await get<{ data: MediaFolder[] }>('/admin/media-folders')
    folders.value = data.data
  }
  catch { /* silent */ }
}

async function createFolder() {
  if (!newFolderName.value.trim()) return
  try {
    await post('/admin/media-folders', { name: newFolderName.value, parent_id: activeFolderId.value })
    newFolderName.value = ''
    showNewFolder.value = false
    fetchFolders()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

function selectFolder(id: number | null) {
  activeFolderId.value = id
  images.value = []
  fetchImages()
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
  if (open) {
    fetchFolders()
    fetchImages()
  }
})
</script>

<template>
  <UiMoleculesModal v-model="modelValue" title="Izaberi sliku" size="lg">
    <!-- Search + folders -->
    <div class="flex gap-4 mb-4">
      <input
        v-model="search"
        type="text"
        placeholder="Pretraži slike..."
        class="flex-1 px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
      />
      <button
        type="button"
        class="text-xs text-primary-600 hover:text-primary-700 whitespace-nowrap"
        @click="showNewFolder = !showNewFolder"
      >
        + Folder
      </button>
    </div>

    <!-- New folder input -->
    <div v-if="showNewFolder" class="flex gap-2 mb-4">
      <input
        v-model="newFolderName"
        type="text"
        placeholder="Naziv foldera"
        class="flex-1 px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-1 focus:ring-primary-500"
        @keyup.enter="createFolder"
      />
      <UiAtomsButton size="sm" @click="createFolder">Kreiraj</UiAtomsButton>
    </div>

    <!-- Folder tabs -->
    <div v-if="folders.length > 0" class="flex flex-wrap gap-1 mb-4">
      <button
        type="button"
        class="px-3 py-1 text-xs rounded-full border transition-colors"
        :class="activeFolderId === null ? 'bg-primary-50 border-primary-400 text-primary-600' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
        @click="selectFolder(null)"
      >
        Sve
      </button>
      <button
        v-for="folder in folders"
        :key="folder.id"
        type="button"
        class="px-3 py-1 text-xs rounded-full border transition-colors"
        :class="activeFolderId === folder.id ? 'bg-primary-50 border-primary-400 text-primary-600' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
        @click="selectFolder(folder.id)"
      >
        {{ folder.name }} ({{ folder.images_count }})
      </button>
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
