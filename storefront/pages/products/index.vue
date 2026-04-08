<script setup lang="ts">
import type { Product, Category, PaginatedResponse } from '~/types'

const { get } = useApi()
const route = useRoute()
const router = useRouter()

const products = ref<Product[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)
const page = ref(1)
const totalPages = ref(1)
const total = ref(0)
const perPage = ref(12)
const sort = ref('created_at')
const direction = ref('desc')
const categoryFilter = ref('')
const quickViewProduct = ref<Product | null>(null)
const quickViewOpen = ref(false)

// Read initial params from URL
function readQuery() {
  const q = route.query
  if (q.page) page.value = Number(q.page)
  if (q.per_page) perPage.value = Number(q.per_page)
  if (q.sort) sort.value = String(q.sort)
  if (q.direction) direction.value = String(q.direction)
  if (q.category) categoryFilter.value = String(q.category)
  if (q.featured) categoryFilter.value = '' // handled separately
}

async function fetchProducts() {
  loading.value = true
  const params: Record<string, string | number | boolean> = {
    page: page.value,
    per_page: perPage.value,
    sort: sort.value,
    direction: direction.value,
  }
  if (categoryFilter.value) params.category = categoryFilter.value
  if (route.query.featured) params.featured = 1
  if (route.query.search) params.search = String(route.query.search)

  try {
    const data = await get<PaginatedResponse<Product>>('/v1/products', params)
    products.value = data.data
    totalPages.value = data.meta.last_page
    total.value = data.meta.total
    page.value = data.meta.current_page
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

async function fetchCategories() {
  try {
    const data = await get<{ data: Category[] }>('/v1/categories')
    categories.value = data.data
  }
  catch { /* silent */ }
}

function updateSort(newSort: string) {
  if (sort.value === newSort) {
    direction.value = direction.value === 'asc' ? 'desc' : 'asc'
  } else {
    sort.value = newSort
    direction.value = newSort === 'price' ? 'asc' : 'desc'
  }
  page.value = 1
  syncUrl()
  fetchProducts()
}

function updatePerPage(val: number) {
  perPage.value = val
  page.value = 1
  syncUrl()
  fetchProducts()
}

function updateCategory(catId: string) {
  categoryFilter.value = catId
  page.value = 1
  syncUrl()
  fetchProducts()
}

function changePage(p: number) {
  page.value = p
  syncUrl()
  fetchProducts()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function syncUrl() {
  const query: Record<string, string> = {}
  if (page.value > 1) query.page = String(page.value)
  if (perPage.value !== 12) query.per_page = String(perPage.value)
  if (sort.value !== 'created_at') query.sort = sort.value
  if (direction.value !== 'desc') query.direction = direction.value
  if (categoryFilter.value) query.category = categoryFilter.value
  if (route.query.search) query.search = String(route.query.search)
  if (route.query.featured) query.featured = '1'
  router.replace({ query })
}

function openQuickView(product: Product) {
  quickViewProduct.value = product
  quickViewOpen.value = true
}

// Active filters
const activeFilters = computed(() => {
  const filters: Array<{ label: string; clear: () => void }> = []

  if (categoryFilter.value) {
    const findCat = (cats: Category[]): string | null => {
      for (const c of cats) {
        if (String(c.id) === categoryFilter.value) return c.name
        if (c.children) {
          const found = findCat(c.children)
          if (found) return found
        }
      }
      return null
    }
    const name = findCat(categories.value) || 'Kategorija'
    filters.push({ label: name, clear: () => updateCategory('') })
  }

  if (route.query.search) {
    filters.push({ label: `"${route.query.search}"`, clear: () => router.replace({ query: { ...route.query, search: undefined } }).then(fetchProducts) })
  }

  if (route.query.featured) {
    filters.push({ label: 'Istaknuto', clear: () => router.replace({ query: { ...route.query, featured: undefined } }).then(fetchProducts) })
  }

  return filters
})

function clearAllFilters() {
  categoryFilter.value = ''
  page.value = 1
  router.replace({ query: {} })
  fetchProducts()
}

onMounted(() => {
  readQuery()
  fetchProducts()
  fetchCategories()
})

useHead({ title: 'Proizvodi — eLokal' })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <LayoutBreadcrumbs :items="[{ label: 'Proizvodi' }]" />

    <div class="flex gap-8">
      <!-- Sidebar -->
      <aside class="hidden lg:block w-60 flex-shrink-0">
        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">Kategorije</h3>
        <ul class="space-y-1">
          <li>
            <button
              class="text-sm w-full text-left py-1 px-2 transition-colors"
              :class="!categoryFilter ? 'text-primary-600 font-semibold bg-primary-50' : 'text-gray-600 hover:text-gray-800'"
              @click="updateCategory('')"
            >
              Sve kategorije
            </button>
          </li>
          <li v-for="cat in categories" :key="cat.id">
            <button
              class="text-sm w-full text-left py-1 px-2 transition-colors"
              :class="categoryFilter === String(cat.id) ? 'text-primary-600 font-semibold bg-primary-50' : 'text-gray-600 hover:text-gray-800'"
              @click="updateCategory(String(cat.id))"
            >
              {{ cat.name }}
            </button>
            <ul v-if="cat.children?.length" class="ml-4">
              <li v-for="child in cat.children" :key="child.id">
                <button
                  class="text-xs w-full text-left py-0.5 px-2 transition-colors"
                  :class="categoryFilter === String(child.id) ? 'text-primary-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                  @click="updateCategory(String(child.id))"
                >
                  {{ child.name }}
                </button>
              </li>
            </ul>
          </li>
        </ul>
      </aside>

      <!-- Main -->
      <div class="flex-1">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
          <p class="text-sm text-gray-500">
            <span class="font-medium text-gray-800">{{ total }}</span> proizvoda
          </p>
          <div class="flex items-center gap-3">
            <select
              :value="sort"
              class="text-sm border border-gray-300 px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500"
              @change="updateSort(($event.target as HTMLSelectElement).value)"
            >
              <option value="created_at">Najnovije</option>
              <option value="price">Cena</option>
              <option value="name">Naziv</option>
            </select>
            <select
              :value="perPage"
              class="text-sm border border-gray-300 px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500"
              @change="updatePerPage(Number(($event.target as HTMLSelectElement).value))"
            >
              <option :value="12">12</option>
              <option :value="24">24</option>
              <option :value="48">48</option>
            </select>
          </div>
        </div>

        <!-- Active filters -->
        <div v-if="activeFilters.length > 0" class="flex flex-wrap items-center gap-2 mb-4">
          <span
            v-for="(filter, i) in activeFilters"
            :key="i"
            class="inline-flex items-center gap-1 bg-gray-100 text-sm text-gray-700 pl-3 pr-1.5 py-1 rounded-full"
          >
            {{ filter.label }}
            <button class="w-5 h-5 flex items-center justify-center text-gray-400 hover:text-gray-700 rounded-full hover:bg-gray-200" @click="filter.clear()">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </span>
          <button v-if="activeFilters.length > 1" class="text-xs text-red-500 hover:text-red-700 ml-1" @click="clearAllFilters">
            Obriši sve
          </button>
        </div>

        <!-- Grid -->
        <ProductGrid :products="products" :loading="loading" :columns="3" @quick-view="openQuickView" />

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-8">
          <button
            v-for="p in totalPages"
            :key="p"
            class="w-10 h-10 text-sm font-medium border transition-colors"
            :class="p === page ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
            @click="changePage(p)"
          >
            {{ p }}
          </button>
        </div>
      </div>
    </div>

    <ProductQuickView v-model="quickViewOpen" :product="quickViewProduct" />
  </div>
</template>
