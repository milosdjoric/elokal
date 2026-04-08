<script setup lang="ts">
import type { DashboardStats } from '~/types'

const { get, getErrorMessage } = useApi()

const stats = ref<DashboardStats | null>(null)
const loading = ref(true)
const error = ref('')

interface LowStockProduct {
  id: number
  name: string
  sku: string | null
  stock_quantity: number
}
const lowStockProducts = ref<LowStockProduct[]>([])
const lowStockLoading = ref(true)

async function fetchDashboard() {
  loading.value = true
  error.value = ''
  try {
    stats.value = await get<DashboardStats>('/admin/dashboard')
  }
  catch (e) {
    error.value = getErrorMessage(e)
  }
  finally {
    loading.value = false
  }
}

async function fetchLowStock() {
  lowStockLoading.value = true
  try {
    const data = await get<{ data: LowStockProduct[] }>('/admin/dashboard/low-stock')
    lowStockProducts.value = data.data
  }
  catch { /* silent */ }
  finally { lowStockLoading.value = false }
}

onMounted(() => {
  fetchDashboard()
  fetchLowStock()
})

const cards = computed(() => {
  if (!stats.value) return []
  return [
    { label: 'Ukupno proizvoda', value: stats.value.total_products, color: 'blue' },
    { label: 'Aktivnih proizvoda', value: stats.value.active_products, color: 'green' },
    { label: 'Kategorija', value: stats.value.total_categories, color: 'purple' },
    { label: 'Istaknuto', value: stats.value.featured_products, color: 'yellow' },
    { label: 'Nema na stanju', value: stats.value.out_of_stock, color: 'red' },
  ]
})

const colorClasses: Record<string, string> = {
  blue: 'bg-primary-50 text-primary-700 border-primary-200',
  green: 'bg-green-50 text-green-700 border-green-200',
  purple: 'bg-purple-50 text-purple-700 border-purple-200',
  yellow: 'bg-yellow-50 text-yellow-700 border-yellow-200',
  red: 'bg-red-50 text-red-700 border-red-200',
}
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Dashboard' }]" />

    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
      {{ error }}
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
      <template v-if="loading">
        <div v-for="i in 5" :key="i" class="bg-white rounded-lg border border-gray-200 p-5">
          <UiAtomsSkeleton width="60%" height="0.875rem" class="mb-3" />
          <UiAtomsSkeleton width="40%" height="2rem" />
        </div>
      </template>

      <div
        v-else
        v-for="card in cards"
        :key="card.label"
        class="rounded-lg border p-5"
        :class="colorClasses[card.color]"
      >
        <p class="text-sm font-medium opacity-80">{{ card.label }}</p>
        <p class="text-3xl font-bold mt-1">{{ card.value }}</p>
      </div>
    </div>

    <!-- Low stock widget -->
    <div class="mt-8">
      <h2 class="text-lg font-bold text-gray-800 mb-4">Nizak nivo zaliha</h2>
      <div v-if="lowStockLoading" class="space-y-2">
        <UiAtomsSkeleton v-for="i in 5" :key="i" height="40px" />
      </div>
      <div v-else-if="lowStockProducts.length === 0" class="text-sm text-gray-400 py-4">
        Svi proizvodi imaju dovoljno zaliha.
      </div>
      <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
        <div v-for="p in lowStockProducts" :key="p.id" class="flex items-center justify-between px-4 py-2.5">
          <div class="min-w-0">
            <NuxtLink :to="`/products/${p.id}/edit`" class="text-sm font-medium text-gray-800 hover:text-primary-600">
              {{ p.name }}
            </NuxtLink>
            <span v-if="p.sku" class="text-xs text-gray-400 ml-2">{{ p.sku }}</span>
          </div>
          <span
            class="text-sm font-semibold flex-shrink-0 px-2 py-0.5 rounded"
            :class="p.stock_quantity <= 3 ? 'bg-red-50 text-red-600' : 'bg-yellow-50 text-yellow-700'"
          >
            {{ p.stock_quantity }} kom
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
