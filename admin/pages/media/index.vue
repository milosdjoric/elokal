<script setup lang="ts">
import type { ProductImage, PaginatedResponse } from '~/types'

interface MediaImage extends ProductImage {
  product?: { id: number; name: string; slug: string }
}

const { get, patch, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

const images = ref<MediaImage[]>([])
const loading = ref(true)
const search = ref('')
const page = ref(1)
const totalPages = ref(1)
const total = ref(0)

// Edit alt modal
const editModal = ref(false)
const editTarget = ref<MediaImage | null>(null)
const editAlt = ref('')
const editSaving = ref(false)

// Delete
const deleteModal = ref(false)
const deleteTarget = ref<MediaImage | null>(null)
const deleteLoading = ref(false)

async function fetchImages() {
  loading.value = true
  try {
    const params: Record<string, string | number> = {
      page: page.value,
      per_page: 24,
    }
    if (search.value) params.search = search.value

    const data = await get<PaginatedResponse<MediaImage>>('/admin/media', params)
    images.value = data.data
    totalPages.value = data.meta.last_page
    total.value = data.meta.total
  }
  catch (e) {
    toastError(getErrorMessage(e))
  }
  finally {
    loading.value = false
  }
}

function handleSearch(q: string) {
  search.value = q
  page.value = 1
  fetchImages()
}

function openEdit(img: MediaImage) {
  editTarget.value = img
  editAlt.value = img.alt_text || ''
  editModal.value = true
}

async function saveAlt() {
  if (!editTarget.value) return
  editSaving.value = true
  try {
    await patch(`/admin/media/${editTarget.value.id}`, { alt_text: editAlt.value })
    editTarget.value.alt_text = editAlt.value
    success('Alt tekst sačuvan.')
    editModal.value = false
  }
  catch (e) {
    toastError(getErrorMessage(e))
  }
  finally {
    editSaving.value = false
  }
}

function confirmDelete(img: MediaImage) {
  deleteTarget.value = img
  deleteModal.value = true
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await del(`/admin/products/${deleteTarget.value.product_id}/images/${deleteTarget.value.id}`)
    success('Slika obrisana.')
    deleteModal.value = false
    fetchImages()
  }
  catch (e) {
    toastError(getErrorMessage(e))
  }
  finally {
    deleteLoading.value = false
  }
}

function imageUrl(img: MediaImage): string {
  return resolveImageUrl(img.image_path)
}

onMounted(fetchImages)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Media Library' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Media Library</h1>
      <span class="text-sm text-gray-500">{{ total }} slika</span>
    </div>

    <!-- Search -->
    <div class="max-w-sm mb-6">
      <UiMoleculesSearchBar
        v-model="search"
        placeholder="Pretraži po alt tekstu ili proizvodu..."
        @search="handleSearch"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
      <UiAtomsSkeleton v-for="i in 12" :key="i" height="8rem" />
    </div>

    <!-- Empty -->
    <div v-else-if="images.length === 0" class="text-center py-12 text-gray-500">
      Nema slika.
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
      <div
        v-for="img in images"
        :key="img.id"
        class="group relative border border-gray-200 overflow-hidden bg-gray-50"
      >
        <img
          :src="imageUrl(img)"
          :alt="img.alt_text || ''"
          class="w-full aspect-square object-cover"
        />

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
          <UiAtomsButton variant="secondary" size="sm" @click="openEdit(img)">
            Alt tekst
          </UiAtomsButton>
          <UiAtomsButton variant="danger" size="sm" @click="confirmDelete(img)">
            Obriši
          </UiAtomsButton>
        </div>

        <!-- Product name -->
        <div class="absolute bottom-0 left-0 right-0 bg-black/40 text-white text-[10px] px-2 py-1 truncate">
          {{ img.product?.name || `Proizvod #${img.product_id}` }}
        </div>

        <!-- Primary badge -->
        <span
          v-if="img.is_primary"
          class="absolute top-1 left-1 bg-primary-600 text-white text-[9px] px-1.5 py-0.5 font-medium"
        >
          Glavna
        </span>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="mt-6 flex justify-center gap-2">
      <UiAtomsButton
        variant="secondary"
        size="sm"
        :disabled="page === 1"
        @click="page--; fetchImages()"
      >
        Prethodna
      </UiAtomsButton>
      <span class="px-3 py-1.5 text-sm text-gray-600">{{ page }} / {{ totalPages }}</span>
      <UiAtomsButton
        variant="secondary"
        size="sm"
        :disabled="page === totalPages"
        @click="page++; fetchImages()"
      >
        Sledeća
      </UiAtomsButton>
    </div>

    <!-- Edit alt modal -->
    <UiMoleculesModal v-model="editModal" title="Izmeni alt tekst" size="sm">
      <UiAtomsInput v-model="editAlt" label="Alt tekst" placeholder="Opis slike..." />

      <template #footer>
        <UiAtomsButton variant="secondary" @click="editModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="editSaving" @click="saveAlt">Sačuvaj</UiAtomsButton>
      </template>
    </UiMoleculesModal>

    <!-- Delete confirm -->
    <UiMoleculesConfirmDialog
      v-model="deleteModal"
      title="Brisanje slike"
      message="Da li ste sigurni da želite da obrišete ovu sliku?"
      confirm-text="Obriši"
      :loading="deleteLoading"
      @confirm="handleDelete"
    />
  </div>
</template>
