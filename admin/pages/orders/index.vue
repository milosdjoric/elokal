<script setup lang="ts">
import type { Order, PaginatedResponse, OrderStatus } from '~/types'

const { get, getErrorMessage } = useApi()
const { error: toastError } = useToast()

const orders = ref<Order[]>([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
const page = ref(1)
const totalPages = ref(1)
const total = ref(0)

const statusLabels: Record<OrderStatus, string> = {
  pending: 'Na čekanju',
  confirmed: 'Potvrđena',
  processing: 'U obradi',
  shipped: 'Poslata',
  delivered: 'Isporučena',
  completed: 'Završena',
  cancelled: 'Otkazana',
  refunded: 'Refundirana',
}

const statusVariants: Record<OrderStatus, string> = {
  pending: 'warning',
  confirmed: 'info',
  processing: 'info',
  shipped: 'info',
  delivered: 'success',
  completed: 'success',
  cancelled: 'danger',
  refunded: 'neutral',
}

const columns = [
  { key: 'order_number', label: 'Broj', width: '160px' },
  { key: 'customer', label: 'Kupac' },
  { key: 'status', label: 'Status', width: '130px' },
  { key: 'total', label: 'Ukupno', width: '130px' },
  { key: 'created_at', label: 'Datum', width: '140px' },
  { key: 'actions', label: '', width: '80px' },
]

async function fetchOrders() {
  loading.value = true
  try {
    const params: Record<string, string | number> = {
      page: page.value,
      per_page: 15,
    }
    if (search.value) params.search = search.value
    if (statusFilter.value) params.status = statusFilter.value

    const data = await get<PaginatedResponse<Order>>('/admin/orders', params)
    orders.value = data.data
    totalPages.value = data.meta.last_page
    total.value = data.meta.total
    page.value = data.meta.current_page
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function handleSearch(q: string) {
  search.value = q
  page.value = 1
  fetchOrders()
}

function handleStatusFilter(status: string) {
  statusFilter.value = status
  page.value = 1
  fetchOrders()
}

function handlePageChange(p: number) {
  page.value = p
  fetchOrders()
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('sr-Latn-RS', {
    day: '2-digit', month: '2-digit', year: 'numeric',
  })
}

function formatPrice(price: string): string {
  return `${parseFloat(price).toLocaleString('sr-RS', { minimumFractionDigits: 2 })} RSD`
}

onMounted(fetchOrders)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Narudžbine' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Narudžbine</h1>
      <span class="text-sm text-gray-400">{{ total }} ukupno</span>
    </div>

    <!-- Filters -->
    <div class="flex items-center gap-4 mb-4">
      <div class="flex-1 max-w-sm">
        <UiMoleculesSearchBar
          v-model="search"
          placeholder="Pretraži po broju, emailu, imenu..."
          @search="handleSearch"
        />
      </div>

      <select
        :value="statusFilter"
        class="px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
        @change="handleStatusFilter(($event.target as HTMLSelectElement).value)"
      >
        <option value="">Svi statusi</option>
        <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
      </select>
    </div>

    <!-- Table -->
    <UiMoleculesDataTable
      :columns="columns"
      :rows="(orders as unknown as Record<string, unknown>[])"
      :loading="loading"
      :page="page"
      :total-pages="totalPages"
      :total="total"
      empty-message="Nema narudžbina."
      @page-change="handlePageChange"
    >
      <template #cell-order_number="{ row }">
        <NuxtLink
          :to="`/orders/${(row as unknown as Order).id}`"
          class="font-medium text-primary-600 hover:text-primary-800"
        >
          {{ (row as unknown as Order).order_number }}
        </NuxtLink>
      </template>

      <template #cell-customer="{ row }">
        <p class="text-sm font-medium text-gray-800">
          {{ (row as unknown as Order).shipping.first_name }} {{ (row as unknown as Order).shipping.last_name }}
        </p>
        <p class="text-xs text-gray-400">{{ (row as unknown as Order).email }}</p>
      </template>

      <template #cell-status="{ row }">
        <UiAtomsBadge :variant="statusVariants[(row as unknown as Order).status] as 'success' | 'warning' | 'danger' | 'info' | 'neutral'">
          {{ statusLabels[(row as unknown as Order).status] }}
        </UiAtomsBadge>
      </template>

      <template #cell-total="{ row }">
        <span class="font-medium">{{ formatPrice((row as unknown as Order).total) }}</span>
      </template>

      <template #cell-created_at="{ row }">
        <span class="text-sm text-gray-500">{{ formatDate((row as unknown as Order).created_at) }}</span>
      </template>

      <template #cell-actions="{ row }">
        <NuxtLink :to="`/orders/${(row as unknown as Order).id}`">
          <UiAtomsButton variant="ghost" size="sm">Detalji</UiAtomsButton>
        </NuxtLink>
      </template>
    </UiMoleculesDataTable>
  </div>
</template>
