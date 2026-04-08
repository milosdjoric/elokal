<script setup lang="ts">
const { get } = useApi()
const { error: toastError } = useToast()

const activeTab = ref('overview')
const tabs = [
  { key: 'overview', label: 'Pregled' },
  { key: 'products', label: 'Proizvodi' },
  { key: 'categories', label: 'Kategorije' },
  { key: 'customers', label: 'Kupci' },
  { key: 'coupons', label: 'Kuponi' },
  { key: 'search', label: 'Pretraga' },
]

const days = ref(30)
const loading = ref(false)

// Data
const overview = ref<Record<string, number>>({})
const salesByDay = ref<{ date: string; revenue: number; orders: number }[]>([])
const topProducts = ref<{ id: number; name: string; sku: string; total_sold: number; total_revenue: number }[]>([])
const topCustomers = ref<{ id: number; name: string; email: string; orders_count: number; orders_sum_total: number }[]>([])
const categories = ref<{ id: number; name: string; total_sold: number; total_revenue: number; orders_count: number }[]>([])
const coupons = ref<{ id: number; code: string; type: string; value: string; times_used: number; total_discount: number }[]>([])
const couponRevenue = ref({ with: 0, without: 0 })
const searchData = ref<{ top_searches: { query: string; count: number; avg_results: number }[]; no_result_searches: { query: string; count: number }[]; total_searches: number }>({ top_searches: [], no_result_searches: [], total_searches: 0 })

async function fetchAll() {
  loading.value = true
  try {
    const [ovRes, salesRes, prodRes, custRes, catRes, coupRes, searchRes] = await Promise.all([
      get<Record<string, number>>(`/admin/reports/overview?days=${days.value}`),
      get<{ data: typeof salesByDay.value }>(`/admin/reports/sales-by-day?days=${days.value}`),
      get<{ data: typeof topProducts.value }>('/admin/reports/top-products?limit=15'),
      get<{ data: typeof topCustomers.value }>('/admin/reports/top-customers?limit=15'),
      get<{ data: typeof categories.value }>(`/admin/reports/categories?days=${days.value}`),
      get<{ data: typeof coupons.value; revenue_with_coupon: number; revenue_without_coupon: number }>(`/admin/reports/coupons?days=${days.value}`),
      get<typeof searchData.value>(`/admin/reports/search?days=${days.value}`),
    ])

    overview.value = ovRes
    salesByDay.value = salesRes.data
    topProducts.value = prodRes.data
    topCustomers.value = custRes.data
    categories.value = catRes.data
    coupons.value = coupRes.data
    couponRevenue.value = { with: coupRes.revenue_with_coupon, without: coupRes.revenue_without_coupon }
    searchData.value = searchRes
  }
  catch { toastError('Greška pri učitavanju izveštaja.') }
  finally { loading.value = false }
}

function formatPrice(val: number | string): string {
  return Number(val).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) + ' RSD'
}

function exportCsv(headers: string[], rows: string[][], filename: string) {
  const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}

function exportProducts() {
  exportCsv(
    ['Naziv', 'SKU', 'Prodato', 'Prihod'],
    topProducts.value.map(p => [p.name, p.sku || '', String(p.total_sold), String(p.total_revenue)]),
    'proizvodi-izvestaj.csv',
  )
}

function exportCategories() {
  exportCsv(
    ['Kategorija', 'Prodato', 'Prihod', 'Narudžbine'],
    categories.value.map(c => [c.name, String(c.total_sold), String(c.total_revenue), String(c.orders_count)]),
    'kategorije-izvestaj.csv',
  )
}

function exportCustomers() {
  exportCsv(
    ['Ime', 'Email', 'Narudžbine', 'Ukupno'],
    topCustomers.value.map(c => [c.name, c.email, String(c.orders_count), String(c.orders_sum_total || 0)]),
    'kupci-izvestaj.csv',
  )
}

watch(days, fetchAll)
onMounted(fetchAll)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Izveštaji' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Izveštaji</h1>
      <div class="flex items-center gap-3">
        <select v-model.number="days" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
          <option :value="7">Poslednjih 7 dana</option>
          <option :value="30">Poslednjih 30 dana</option>
          <option :value="90">Poslednjih 90 dana</option>
          <option :value="365">Poslednjih 365 dana</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="py-20 flex justify-center">
      <UiAtomsSpinner size="lg" />
    </div>

    <div v-else class="bg-white border border-gray-200 p-6">
      <UiMoleculesTabs v-model="activeTab" :tabs="tabs">
        <template #default="{ active }">

          <!-- Pregled -->
          <div v-show="active === 'overview'" class="space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">Prihod</p>
                <p class="text-xl font-bold text-gray-800">{{ formatPrice(overview.revenue || 0) }}</p>
              </div>
              <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">Narudžbine</p>
                <p class="text-xl font-bold text-gray-800">{{ overview.orders_count || 0 }}</p>
              </div>
              <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">AOV</p>
                <p class="text-xl font-bold text-gray-800">{{ formatPrice(overview.aov || 0) }}</p>
              </div>
              <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">Novi kupci</p>
                <p class="text-xl font-bold text-gray-800">{{ overview.new_customers || 0 }}</p>
              </div>
            </div>

            <!-- Sales by day tabela -->
            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Prodaja po danu</h3>
              <div v-if="salesByDay.length === 0" class="text-sm text-gray-400">Nema podataka.</div>
              <div v-else class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50 border-b">
                    <tr>
                      <th class="px-4 py-2 text-left font-medium text-gray-600">Datum</th>
                      <th class="px-4 py-2 text-right font-medium text-gray-600">Narudžbine</th>
                      <th class="px-4 py-2 text-right font-medium text-gray-600">Prihod</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <tr v-for="row in salesByDay" :key="row.date" class="hover:bg-gray-50">
                      <td class="px-4 py-2 text-gray-700">{{ row.date }}</td>
                      <td class="px-4 py-2 text-right text-gray-600">{{ row.orders }}</td>
                      <td class="px-4 py-2 text-right font-medium">{{ formatPrice(row.revenue) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Proizvodi -->
          <div v-show="active === 'products'" class="space-y-4">
            <div class="flex justify-end">
              <UiAtomsButton variant="secondary" size="sm" @click="exportProducts">Export CSV</UiAtomsButton>
            </div>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Proizvod</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">SKU</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-600">Prodato</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-600">Prihod</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="p in topProducts" :key="p.id" class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium text-gray-800">{{ p.name }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ p.sku || '—' }}</td>
                    <td class="px-4 py-2 text-right">{{ p.total_sold }}</td>
                    <td class="px-4 py-2 text-right font-medium">{{ formatPrice(p.total_revenue) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Kategorije -->
          <div v-show="active === 'categories'" class="space-y-4">
            <div class="flex justify-end">
              <UiAtomsButton variant="secondary" size="sm" @click="exportCategories">Export CSV</UiAtomsButton>
            </div>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Kategorija</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-600">Prodato</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-600">Prihod</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-600">Narudžbine</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="c in categories" :key="c.id" class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium text-gray-800">{{ c.name }}</td>
                    <td class="px-4 py-2 text-right">{{ c.total_sold }}</td>
                    <td class="px-4 py-2 text-right font-medium">{{ formatPrice(c.total_revenue) }}</td>
                    <td class="px-4 py-2 text-right">{{ c.orders_count }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Kupci -->
          <div v-show="active === 'customers'" class="space-y-4">
            <div class="flex justify-end">
              <UiAtomsButton variant="secondary" size="sm" @click="exportCustomers">Export CSV</UiAtomsButton>
            </div>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Ime</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Email</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-600">Narudžbine</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-600">Ukupno</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="c in topCustomers" :key="c.id" class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium text-gray-800">{{ c.name }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ c.email }}</td>
                    <td class="px-4 py-2 text-right">{{ c.orders_count }}</td>
                    <td class="px-4 py-2 text-right font-medium">{{ formatPrice(c.orders_sum_total || 0) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Kuponi -->
          <div v-show="active === 'coupons'" class="space-y-4">
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">Prihod sa kuponom</p>
                <p class="text-lg font-bold text-gray-800">{{ formatPrice(couponRevenue.with) }}</p>
              </div>
              <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">Prihod bez kupona</p>
                <p class="text-lg font-bold text-gray-800">{{ formatPrice(couponRevenue.without) }}</p>
              </div>
            </div>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Kod</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-600">Korišćen</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-600">Ukupan popust</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="c in coupons" :key="c.id" class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium text-gray-800">{{ c.code }}</td>
                    <td class="px-4 py-2 text-right">{{ c.times_used }}×</td>
                    <td class="px-4 py-2 text-right font-medium">{{ formatPrice(c.total_discount) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Pretraga -->
          <div v-show="active === 'search'" class="space-y-6">
            <div class="border border-gray-200 rounded-lg p-4">
              <p class="text-xs text-gray-500 uppercase">Ukupno pretraga</p>
              <p class="text-xl font-bold text-gray-800">{{ searchData.total_searches }}</p>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Najpopularnije pretrage</h3>
              <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50 border-b">
                    <tr>
                      <th class="px-4 py-2 text-left font-medium text-gray-600">Upit</th>
                      <th class="px-4 py-2 text-right font-medium text-gray-600">Broj</th>
                      <th class="px-4 py-2 text-right font-medium text-gray-600">Prosek rezultata</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <tr v-for="s in searchData.top_searches" :key="s.query" class="hover:bg-gray-50">
                      <td class="px-4 py-2 text-gray-800">{{ s.query }}</td>
                      <td class="px-4 py-2 text-right">{{ s.count }}</td>
                      <td class="px-4 py-2 text-right text-gray-500">{{ Math.round(Number(s.avg_results)) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Pretrage bez rezultata</h3>
              <div v-if="searchData.no_result_searches.length === 0" class="text-sm text-gray-400">Sve pretrage imaju rezultate.</div>
              <div v-else class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50 border-b">
                    <tr>
                      <th class="px-4 py-2 text-left font-medium text-gray-600">Upit</th>
                      <th class="px-4 py-2 text-right font-medium text-gray-600">Broj</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <tr v-for="s in searchData.no_result_searches" :key="s.query" class="hover:bg-gray-50">
                      <td class="px-4 py-2 text-gray-800">{{ s.query }}</td>
                      <td class="px-4 py-2 text-right">{{ s.count }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </template>
      </UiMoleculesTabs>
    </div>
  </div>
</template>
