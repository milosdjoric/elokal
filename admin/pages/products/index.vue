<script setup lang="ts">
import type { Product, PaginatedResponse } from '~/types'

const { get, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

const products = ref<Product[]>([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
const sortKey = ref('created_at')
const sortDir = ref<'asc' | 'desc'>('desc')
const page = ref(1)
const totalPages = ref(1)
const total = ref(0)

const deleteModal = ref(false)
const deleteTarget = ref<Product | null>(null)
const deleteLoading = ref(false)

const columns = [
  { key: 'image', label: '', width: '60px' },
  { key: 'name', label: 'Naziv', sortable: true },
  { key: 'sku', label: 'SKU', width: '120px' },
  { key: 'price', label: 'Cena', sortable: true, width: '150px' },
  { key: 'stock_quantity', label: 'Stanje', width: '90px' },
  { key: 'is_active', label: 'Status', width: '100px' },
  { key: 'actions', label: '', width: '100px' },
]

async function fetchProducts() {
  loading.value = true
  try {
    const params: Record<string, string | number> = {
      page: page.value,
      per_page: 15,
      sort: sortKey.value,
      direction: sortDir.value,
    }
    if (search.value) params.search = search.value
    if (statusFilter.value) params.status = statusFilter.value

    const data = await get<PaginatedResponse<Product>>('/admin/products', params)
    products.value = data.data
    totalPages.value = data.meta.last_page
    total.value = data.meta.total
    page.value = data.meta.current_page
  }
  catch (e) {
    toastError(getErrorMessage(e))
  }
  finally {
    loading.value = false
  }
}

function handleSort(key: string) {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  }
  else {
    sortKey.value = key
    sortDir.value = 'asc'
  }
  page.value = 1
  fetchProducts()
}

function handleSearch(q: string) {
  search.value = q
  page.value = 1
  fetchProducts()
}

function handlePageChange(p: number) {
  page.value = p
  fetchProducts()
}

function handleStatusFilter(status: string) {
  statusFilter.value = status
  page.value = 1
  fetchProducts()
}

function confirmDelete(product: Product) {
  deleteTarget.value = product
  deleteModal.value = true
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await del(`/admin/products/${deleteTarget.value.id}`)
    success('Proizvod obrisan.')
    deleteModal.value = false
    fetchProducts()
  }
  catch (e) {
    toastError(getErrorMessage(e))
  }
  finally {
    deleteLoading.value = false
  }
}

function primaryImage(product: Product): string | null {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  return img ? `http://localhost:8000/storage/${img.image_path}` : null
}

onMounted(fetchProducts)

const route = useRoute()
watch(() => route.query.search, (val) => {
  if (val) {
    search.value = String(val)
    fetchProducts()
  }
})
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Proizvodi' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Proizvodi</h1>
      <NuxtLink to="/products/create">
        <UiAtomsButton>+ Novi proizvod</UiAtomsButton>
      </NuxtLink>
    </div>

    <!-- Filters -->
    <div class="flex items-center gap-4 mb-4">
      <div class="flex-1 max-w-sm">
        <UiMoleculesSearchBar
          v-model="search"
          placeholder="Pretraži proizvode..."
          @search="handleSearch"
        />
      </div>

      <select
        :value="statusFilter"
        class="px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
        @change="handleStatusFilter(($event.target as HTMLSelectElement).value)"
      >
        <option value="">Svi statusi</option>
        <option value="active">Aktivni</option>
        <option value="inactive">Neaktivni</option>
      </select>
    </div>

    <!-- Table -->
    <UiMoleculesDataTable
      :columns="columns"
      :rows="(products as unknown as Record<string, unknown>[])"
      :loading="loading"
      :sort-key="sortKey"
      :sort-dir="sortDir"
      :page="page"
      :total-pages="totalPages"
      :total="total"
      empty-message="Nema proizvoda."
      @sort="handleSort"
      @page-change="handlePageChange"
    >
      <template #cell-image="{ row }">
        <img
          v-if="primaryImage(row as unknown as Product)"
          :src="primaryImage(row as unknown as Product)!"
          class="w-10 h-10 object-cover border border-gray-200"
          alt=""
        />
        <div v-else class="w-10 h-10 bg-gray-100 flex items-center justify-center">
          <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M2.25 18h19.5M2.25 6h19.5v12H2.25V6z" />
          </svg>
        </div>
      </template>

      <template #cell-name="{ row }">
        <NuxtLink
          :to="`/products/${(row as unknown as Product).id}/edit`"
          class="font-medium text-gray-900 hover:text-primary-600"
        >
          {{ (row as unknown as Product).name }}
        </NuxtLink>
        <p v-if="(row as unknown as Product).featured" class="mt-0.5">
          <UiAtomsBadge variant="warning">Istaknuto</UiAtomsBadge>
        </p>
      </template>

      <template #cell-price="{ row }">
        <UiMoleculesPriceDisplay
          :price="(row as unknown as Product).price"
          :sale-price="(row as unknown as Product).sale_price"
          :sale-percentage="(row as unknown as Product).sale_percentage"
          :is-on-sale="(row as unknown as Product).is_on_sale"
        />
      </template>

      <template #cell-stock_quantity="{ row }">
        <span :class="(row as unknown as Product).stock_quantity === 0 ? 'text-red-600 font-medium' : 'text-gray-700'">
          {{ (row as unknown as Product).stock_quantity }}
        </span>
      </template>

      <template #cell-is_active="{ row }">
        <UiAtomsBadge :variant="(row as unknown as Product).is_active ? 'success' : 'neutral'">
          {{ (row as unknown as Product).is_active ? 'Aktivan' : 'Neaktivan' }}
        </UiAtomsBadge>
      </template>

      <template #cell-actions="{ row }">
        <div class="flex items-center gap-2">
          <NuxtLink :to="`/products/${(row as unknown as Product).id}/edit`">
            <UiAtomsButton variant="ghost" size="sm">Izmeni</UiAtomsButton>
          </NuxtLink>
          <UiAtomsButton variant="ghost" size="sm" @click="confirmDelete(row as unknown as Product)">
            <span class="text-red-500">Obriši</span>
          </UiAtomsButton>
        </div>
      </template>
    </UiMoleculesDataTable>

    <!-- Delete confirm -->
    <UiMoleculesConfirmDialog
      v-model="deleteModal"
      title="Brisanje proizvoda"
      :message="`Da li ste sigurni da želite da obrišete '${deleteTarget?.name}'?`"
      confirm-text="Obriši"
      :loading="deleteLoading"
      @confirm="handleDelete"
    />
  </div>
</template>
