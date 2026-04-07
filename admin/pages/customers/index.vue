<script setup lang="ts">
import type { Customer, PaginatedResponse } from '~/types'

const { get, getErrorMessage } = useApi()
const { error: toastError } = useToast()

const customers = ref<Customer[]>([])
const loading = ref(true)
const search = ref('')
const page = ref(1)
const totalPages = ref(1)
const total = ref(0)

const columns = [
  { key: 'name', label: 'Ime' },
  { key: 'email', label: 'Email' },
  { key: 'orders_count', label: 'Narudžbine', width: '120px' },
  { key: 'total_spent', label: 'Ukupno potrošeno', width: '160px' },
  { key: 'created_at', label: 'Registrovan', width: '140px' },
  { key: 'actions', label: '', width: '80px' },
]

async function fetchCustomers() {
  loading.value = true
  try {
    const params: Record<string, string | number> = {
      page: page.value,
      per_page: 15,
    }
    if (search.value) params.search = search.value

    const data = await get<PaginatedResponse<Customer>>('/admin/customers', params)
    customers.value = data.data
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
  fetchCustomers()
}

function handlePageChange(p: number) {
  page.value = p
  fetchCustomers()
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('sr-Latn-RS', {
    day: '2-digit', month: '2-digit', year: 'numeric',
  })
}

function formatPrice(price: string): string {
  return `${parseFloat(price).toLocaleString('sr-RS', { minimumFractionDigits: 2 })} RSD`
}

onMounted(fetchCustomers)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Kupci' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Kupci</h1>
      <span class="text-sm text-gray-400">{{ total }} ukupno</span>
    </div>

    <div class="flex items-center gap-4 mb-4">
      <div class="flex-1 max-w-sm">
        <UiMoleculesSearchBar
          v-model="search"
          placeholder="Pretraži po imenu, emailu, telefonu..."
          @search="handleSearch"
        />
      </div>
    </div>

    <UiMoleculesDataTable
      :columns="columns"
      :rows="(customers as unknown as Record<string, unknown>[])"
      :loading="loading"
      :page="page"
      :total-pages="totalPages"
      :total="total"
      empty-message="Nema registrovanih kupaca."
      @page-change="handlePageChange"
    >
      <template #cell-name="{ row }">
        <NuxtLink
          :to="`/customers/${(row as unknown as Customer).id}`"
          class="font-medium text-gray-900 hover:text-primary-600"
        >
          {{ (row as unknown as Customer).name }}
        </NuxtLink>
      </template>

      <template #cell-email="{ row }">
        <span class="text-sm text-gray-500">{{ (row as unknown as Customer).email }}</span>
      </template>

      <template #cell-orders_count="{ row }">
        <span class="text-sm">{{ (row as unknown as Customer).orders_count }}</span>
      </template>

      <template #cell-total_spent="{ row }">
        <span class="font-medium">{{ formatPrice((row as unknown as Customer).total_spent) }}</span>
      </template>

      <template #cell-created_at="{ row }">
        <span class="text-sm text-gray-500">{{ formatDate((row as unknown as Customer).created_at) }}</span>
      </template>

      <template #cell-actions="{ row }">
        <NuxtLink :to="`/customers/${(row as unknown as Customer).id}`">
          <UiAtomsButton variant="ghost" size="sm">Detalji</UiAtomsButton>
        </NuxtLink>
      </template>
    </UiMoleculesDataTable>
  </div>
</template>
