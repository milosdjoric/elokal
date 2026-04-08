<script setup lang="ts">
import type { ProductImage, PaginatedResponse } from '~/types'

interface MediaImage extends ProductImage {
  product?: { id: number; name: string; slug: string }
}

const { get, patch, del, post, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

const images = ref<MediaImage[]>([])
const loading = ref(true)
const search = ref('')
const page = ref(1)
const totalPages = ref(1)
const total = ref(0)

// Filteri
const filterType = ref('')
const filterDateFrom = ref('')
const filterDateTo = ref('')

// Bulk selekcija
const selectedIds = ref<Set<number>>(new Set())
const bulkDeleting = ref(false)

// Edit alt modal
const editModal = ref(false)
const editTarget = ref<MediaImage | null>(null)
const editAlt = ref('')
const editSaving = ref(false)

// Delete
const deleteModal = ref(false)
const deleteTarget = ref<MediaImage | null>(null)
const deleteLoading = ref(false)

// Usage modal
const usageModal = ref(false)
const usageTarget = ref<MediaImage | null>(null)
const usageData = ref<{ product: { id: number; name: string; slug: string } | null; variants: { id: number; sku: string; label: string }[] }>({ product: null, variants: [] })
const usageLoading = ref(false)

async function fetchImages() {
  loading.value = true
  try {
    const params: Record<string, string | number> = {
      page: page.value,
      per_page: 24,
    }
    if (search.value) params.search = search.value
    if (filterType.value) params.type = filterType.value
    if (filterDateFrom.value) params.date_from = filterDateFrom.value
    if (filterDateTo.value) params.date_to = filterDateTo.value

    const data = await get<PaginatedResponse<MediaImage>>('/admin/media', params)
    images.value = data.data
    totalPages.value = data.meta.last_page
    total.value = data.meta.total
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function handleSearch(q: string) {
  search.value = q
  page.value = 1
  fetchImages()
}

function applyFilters() {
  page.value = 1
  fetchImages()
}

function clearFilters() {
  filterType.value = ''
  filterDateFrom.value = ''
  filterDateTo.value = ''
  page.value = 1
  fetchImages()
}

// Selekcija
function toggleSelect(id: number) {
  if (selectedIds.value.has(id)) selectedIds.value.delete(id)
  else selectedIds.value.add(id)
}

function toggleSelectAll() {
  if (selectedIds.value.size === images.value.length) {
    selectedIds.value.clear()
  } else {
    images.value.forEach(img => selectedIds.value.add(img.id))
  }
}

const isAllSelected = computed(() => images.value.length > 0 && selectedIds.value.size === images.value.length)

// Bulk delete
const bulkDeleteModal = ref(false)

async function handleBulkDelete() {
  bulkDeleting.value = true
  try {
    await post('/admin/media/bulk-delete', { ids: Array.from(selectedIds.value) })
    success(`${selectedIds.value.size} slika obrisano.`)
    selectedIds.value.clear()
    bulkDeleteModal.value = false
    fetchImages()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { bulkDeleting.value = false }
}

// Alt edit
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
  catch (e) { toastError(getErrorMessage(e)) }
  finally { editSaving.value = false }
}

// Single delete
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
  catch (e) { toastError(getErrorMessage(e)) }
  finally { deleteLoading.value = false }
}

// Usage
async function openUsage(img: MediaImage) {
  usageTarget.value = img
  usageLoading.value = true
  usageModal.value = true
  try {
    const data = await get<typeof usageData.value>(`/admin/media/${img.id}/usage`)
    usageData.value = data
  }
  catch { usageData.value = { product: null, variants: [] } }
  finally { usageLoading.value = false }
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

    <!-- Search + Filteri -->
    <div class="flex flex-wrap items-end gap-3 mb-6">
      <div class="w-64">
        <UiMoleculesSearchBar
          v-model="search"
          placeholder="Pretraži..."
          @search="handleSearch"
        />
      </div>
      <select
        v-model="filterType"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
        @change="applyFilters"
      >
        <option value="">Svi tipovi</option>
        <option value="jpg">JPG</option>
        <option value="png">PNG</option>
        <option value="webp">WebP</option>
      </select>
      <input
        v-model="filterDateFrom"
        type="date"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
        @change="applyFilters"
      />
      <input
        v-model="filterDateTo"
        type="date"
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
        @change="applyFilters"
      />
      <UiAtomsButton v-if="filterType || filterDateFrom || filterDateTo" variant="ghost" size="sm" @click="clearFilters">
        Resetuj filtere
      </UiAtomsButton>
    </div>

    <!-- Bulk toolbar -->
    <div v-if="images.length > 0" class="flex items-center gap-3 mb-4">
      <label class="flex items-center gap-2 text-sm cursor-pointer">
        <input
          type="checkbox"
          :checked="isAllSelected"
          class="w-4 h-4 text-primary-600 border-gray-300 rounded"
          @change="toggleSelectAll"
        />
        Označi sve
      </label>
      <span v-if="selectedIds.size > 0" class="text-sm text-gray-500">{{ selectedIds.size }} odabrano</span>
      <UiAtomsButton
        v-if="selectedIds.size > 0"
        variant="danger"
        size="sm"
        @click="bulkDeleteModal = true"
      >
        Obriši odabrane
      </UiAtomsButton>
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
        class="group relative border overflow-hidden bg-gray-50 cursor-pointer transition-colors"
        :class="selectedIds.has(img.id) ? 'border-primary-500 ring-2 ring-primary-300' : 'border-gray-200'"
      >
        <!-- Checkbox -->
        <div class="absolute top-1 right-1 z-10">
          <input
            type="checkbox"
            :checked="selectedIds.has(img.id)"
            class="w-4 h-4 text-primary-600 border-gray-300 rounded"
            @click.stop="toggleSelect(img.id)"
          />
        </div>

        <img
          :src="imageUrl(img)"
          :alt="img.alt_text || ''"
          class="w-full aspect-square object-cover"
          @click="openUsage(img)"
        />

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
          <UiAtomsButton variant="secondary" size="sm" @click.stop="openEdit(img)">Alt tekst</UiAtomsButton>
          <UiAtomsButton variant="secondary" size="sm" @click.stop="openUsage(img)">Detalji</UiAtomsButton>
          <UiAtomsButton variant="danger" size="sm" @click.stop="confirmDelete(img)">Obriši</UiAtomsButton>
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
      <UiAtomsButton variant="secondary" size="sm" :disabled="page === 1" @click="page--; fetchImages()">Prethodna</UiAtomsButton>
      <span class="px-3 py-1.5 text-sm text-gray-600">{{ page }} / {{ totalPages }}</span>
      <UiAtomsButton variant="secondary" size="sm" :disabled="page === totalPages" @click="page++; fetchImages()">Sledeća</UiAtomsButton>
    </div>

    <!-- Edit alt modal -->
    <UiMoleculesModal v-model="editModal" title="Izmeni alt tekst" size="sm">
      <UiAtomsInput v-model="editAlt" label="Alt tekst" placeholder="Opis slike..." />
      <template #footer>
        <UiAtomsButton variant="secondary" @click="editModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="editSaving" @click="saveAlt">Sačuvaj</UiAtomsButton>
      </template>
    </UiMoleculesModal>

    <!-- Usage modal -->
    <UiMoleculesModal v-model="usageModal" title="Detalji slike" size="md">
      <div v-if="usageLoading" class="py-8 flex justify-center">
        <UiAtomsSpinner />
      </div>
      <div v-else class="space-y-4">
        <div v-if="usageTarget" class="flex gap-4">
          <img :src="imageUrl(usageTarget)" class="w-24 h-24 object-cover rounded border" />
          <div class="text-sm space-y-1">
            <p><span class="text-gray-500">Alt:</span> {{ usageTarget.alt_text || '—' }}</p>
            <p><span class="text-gray-500">Putanja:</span> {{ usageTarget.image_path }}</p>
            <p><span class="text-gray-500">Glavna:</span> {{ usageTarget.is_primary ? 'Da' : 'Ne' }}</p>
          </div>
        </div>

        <div>
          <h4 class="text-sm font-semibold text-gray-700 mb-2">Koristi se na:</h4>
          <div v-if="usageData.product" class="ml-2 mb-2">
            <NuxtLink :to="`/products/${usageData.product.id}/edit`" class="text-sm text-primary-600 hover:underline">
              {{ usageData.product.name }}
            </NuxtLink>
          </div>
          <div v-if="usageData.variants.length > 0" class="ml-2">
            <p class="text-xs text-gray-500 mb-1">Varijante:</p>
            <ul class="space-y-1">
              <li v-for="v in usageData.variants" :key="v.id" class="text-sm text-gray-600">
                {{ v.label || v.sku || `Varijanta #${v.id}` }}
              </li>
            </ul>
          </div>
          <p v-if="!usageData.product && usageData.variants.length === 0" class="text-sm text-gray-400">
            Nije povezana sa proizvodom.
          </p>
        </div>
      </div>
      <template #footer>
        <UiAtomsButton variant="secondary" @click="usageModal = false">Zatvori</UiAtomsButton>
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

    <!-- Bulk delete confirm -->
    <UiMoleculesConfirmDialog
      v-model="bulkDeleteModal"
      title="Brisanje odabranih slika"
      :message="`Da li ste sigurni da želite da obrišete ${selectedIds.size} slika?`"
      confirm-text="Obriši sve"
      :loading="bulkDeleting"
      @confirm="handleBulkDelete"
    />
  </div>
</template>
