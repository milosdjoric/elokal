<script setup lang="ts">
import type { Product, Category, PaginatedResponse } from '~/types'

const { get } = useApi()
const route = useRoute()
const router = useRouter()

interface FilterAttribute {
  id: number
  name: string
  slug: string
  type: 'select' | 'color' | 'image'
  values: Array<{ id: number; value: string; color_hex: string | null; image_path: string | null }>
}

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
const expandedCat = ref<number | null>(null)
const quickViewProduct = ref<Product | null>(null)
const quickViewOpen = ref(false)
const layout = ref<'grid' | 'list' | 'compact'>('grid')

// Price range
const priceRange = ref({ min: 0, max: 100000 })
const minPrice = ref('')
const maxPrice = ref('')

// Attribute filters
const filterAttributes = ref<FilterAttribute[]>([])
const selectedAttributes = reactive<Record<string, Set<number>>>({})

// Read initial params from URL
function readQuery() {
  const q = route.query
  if (q.page) page.value = Number(q.page)
  if (q.per_page) perPage.value = Number(q.per_page)
  if (q.sort) sort.value = String(q.sort)
  if (q.direction) direction.value = String(q.direction)
  if (q.category) categoryFilter.value = String(q.category)
  if (q.featured) categoryFilter.value = '' // handled separately
  if (q.min_price) minPrice.value = String(q.min_price)
  if (q.max_price) maxPrice.value = String(q.max_price)

  // Attribute filters from URL: attr_color=1,3
  for (const [key, val] of Object.entries(q)) {
    if (key.startsWith('attr_') && typeof val === 'string') {
      const slug = key.replace('attr_', '')
      selectedAttributes[slug] = new Set(val.split(',').map(Number).filter(Boolean))
    }
  }
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
  if (minPrice.value) params.min_price = minPrice.value
  if (maxPrice.value) params.max_price = maxPrice.value

  // Attribute filters
  for (const [slug, ids] of Object.entries(selectedAttributes)) {
    if (ids.size > 0) {
      params[`attributes[${slug}]`] = Array.from(ids).join(',')
    }
  }

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

async function fetchFilters() {
  try {
    const data = await get<{ price_range: { min: number; max: number }; attributes: FilterAttribute[] }>('/v1/products/filters')
    priceRange.value = data.price_range
    filterAttributes.value = data.attributes
  }
  catch { /* silent */ }
}

function toggleAttributeFilter(slug: string, valueId: number) {
  if (!selectedAttributes[slug]) selectedAttributes[slug] = new Set()
  if (selectedAttributes[slug].has(valueId)) selectedAttributes[slug].delete(valueId)
  else selectedAttributes[slug].add(valueId)
  page.value = 1
  syncUrl()
  fetchProducts()
}

function applyPriceRange() {
  page.value = 1
  syncUrl()
  fetchProducts()
}

function clearPriceRange() {
  minPrice.value = ''
  maxPrice.value = ''
  page.value = 1
  syncUrl()
  fetchProducts()
}

function updateSort(newSort: string) {
  if (newSort === 'price_asc') {
    sort.value = 'price'
    direction.value = 'asc'
  } else if (newSort === 'price_desc') {
    sort.value = 'price'
    direction.value = 'desc'
  } else if (newSort === 'name_asc') {
    sort.value = 'name'
    direction.value = 'asc'
  } else if (newSort === 'name_desc') {
    sort.value = 'name'
    direction.value = 'desc'
  } else if (newSort === 'discount') {
    sort.value = 'discount'
    direction.value = 'desc'
  } else {
    sort.value = newSort
    direction.value = 'desc'
  }
  page.value = 1
  syncUrl()
  fetchProducts()
}

// Composite vrednost za select
const sortSelectValue = computed(() => {
  if (sort.value === 'price') return direction.value === 'asc' ? 'price_asc' : 'price_desc'
  if (sort.value === 'name') return direction.value === 'asc' ? 'name_asc' : 'name_desc'
  if (sort.value === 'discount') return 'discount'
  return sort.value
})

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
  if (minPrice.value) query.min_price = minPrice.value
  if (maxPrice.value) query.max_price = maxPrice.value

  for (const [slug, ids] of Object.entries(selectedAttributes)) {
    if (ids.size > 0) query[`attr_${slug}`] = Array.from(ids).join(',')
  }

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

  if (minPrice.value || maxPrice.value) {
    const label = `${minPrice.value || '0'} — ${maxPrice.value || '∞'} RSD`
    filters.push({ label, clear: clearPriceRange })
  }

  for (const [slug, ids] of Object.entries(selectedAttributes)) {
    if (ids.size === 0) continue
    const attr = filterAttributes.value.find(a => a.slug === slug)
    if (!attr) continue
    for (const id of ids) {
      const val = attr.values.find(v => v.id === id)
      if (val) {
        filters.push({
          label: `${attr.name}: ${val.value}`,
          clear: () => toggleAttributeFilter(slug, id),
        })
      }
    }
  }

  return filters
})

function clearAllFilters() {
  categoryFilter.value = ''
  minPrice.value = ''
  maxPrice.value = ''
  for (const slug of Object.keys(selectedAttributes)) {
    selectedAttributes[slug].clear()
  }
  page.value = 1
  router.replace({ query: {} })
  fetchProducts()
}

onMounted(() => {
  readQuery()
  fetchProducts()
  fetchCategories()
  fetchFilters()
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
            <div class="flex items-center">
              <button
                class="text-sm flex-1 text-left py-1 px-2 transition-colors"
                :class="categoryFilter === String(cat.id) ? 'text-primary-600 font-semibold bg-primary-50' : 'text-gray-600 hover:text-gray-800'"
                @click="updateCategory(String(cat.id))"
              >
                {{ cat.name }}
              </button>
              <button
                v-if="cat.children?.length"
                class="p-1 text-gray-400 hover:text-gray-600"
                @click="expandedCat = expandedCat === cat.id ? null : cat.id"
              >
                <svg class="w-3 h-3 transition-transform" :class="expandedCat === cat.id ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
              </button>
            </div>
            <ul v-if="cat.children?.length && expandedCat === cat.id" class="ml-4">
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

        <!-- Price range -->
        <div class="mt-6">
          <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">Cena</h3>
          <div class="flex items-center gap-2">
            <input
              v-model="minPrice"
              type="number"
              :placeholder="String(Math.floor(priceRange.min))"
              class="w-full px-2 py-1.5 text-sm border border-gray-300 focus:outline-none focus:ring-1 focus:ring-primary-500"
              @keyup.enter="applyPriceRange"
            />
            <span class="text-gray-400 text-sm">—</span>
            <input
              v-model="maxPrice"
              type="number"
              :placeholder="String(Math.ceil(priceRange.max))"
              class="w-full px-2 py-1.5 text-sm border border-gray-300 focus:outline-none focus:ring-1 focus:ring-primary-500"
              @keyup.enter="applyPriceRange"
            />
          </div>
          <button class="text-xs text-primary-600 hover:text-primary-700 mt-2" @click="applyPriceRange">
            Primeni
          </button>
        </div>

        <!-- Attribute filters -->
        <div v-for="attr in filterAttributes" :key="attr.id" class="mt-6">
          <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">{{ attr.name }}</h3>

          <!-- Color swatches -->
          <div v-if="attr.type === 'color'" class="flex flex-wrap gap-2">
            <button
              v-for="val in attr.values"
              :key="val.id"
              type="button"
              :title="val.value"
              class="w-8 h-8 rounded-full border-2 transition-all"
              :class="selectedAttributes[attr.slug]?.has(val.id) ? 'border-primary-600 ring-2 ring-primary-200' : 'border-gray-300 hover:border-gray-400'"
              :style="{ backgroundColor: val.color_hex || '#ccc' }"
              @click="toggleAttributeFilter(attr.slug, val.id)"
            />
          </div>

          <!-- Image swatches -->
          <div v-else-if="attr.type === 'image'" class="flex flex-wrap gap-2">
            <button
              v-for="val in attr.values"
              :key="val.id"
              type="button"
              :title="val.value"
              class="w-10 h-10 border-2 rounded overflow-hidden transition-all"
              :class="selectedAttributes[attr.slug]?.has(val.id) ? 'border-primary-600 ring-2 ring-primary-200' : 'border-gray-300 hover:border-gray-400'"
              @click="toggleAttributeFilter(attr.slug, val.id)"
            >
              <img v-if="val.image_path" :src="val.image_path" :alt="val.value" class="w-full h-full object-cover" />
              <span v-else class="flex items-center justify-center text-xs text-gray-400 h-full">{{ val.value }}</span>
            </button>
          </div>

          <!-- Checkboxes (select type) -->
          <div v-else class="space-y-1">
            <label
              v-for="val in attr.values"
              :key="val.id"
              class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer"
            >
              <input
                type="checkbox"
                :checked="selectedAttributes[attr.slug]?.has(val.id)"
                class="w-4 h-4 text-primary-600 border-gray-300 rounded"
                @change="toggleAttributeFilter(attr.slug, val.id)"
              />
              {{ val.value }}
            </label>
          </div>
        </div>
      </aside>

      <!-- Main -->
      <div class="flex-1">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
          <p class="text-sm text-gray-500">
            <span class="font-medium text-gray-800">{{ total }}</span> proizvoda
          </p>
          <div class="flex items-center gap-3">
            <!-- Layout switcher -->
            <div class="flex border border-gray-300 divide-x divide-gray-300">
              <button
                type="button"
                class="p-1.5 transition-colors"
                :class="layout === 'grid' ? 'bg-primary-50 text-primary-600' : 'text-gray-400 hover:text-gray-600'"
                title="Grid"
                @click="layout = 'grid'"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16"><rect x="1" y="1" width="6" height="6" rx="1" /><rect x="9" y="1" width="6" height="6" rx="1" /><rect x="1" y="9" width="6" height="6" rx="1" /><rect x="9" y="9" width="6" height="6" rx="1" /></svg>
              </button>
              <button
                type="button"
                class="p-1.5 transition-colors"
                :class="layout === 'list' ? 'bg-primary-50 text-primary-600' : 'text-gray-400 hover:text-gray-600'"
                title="Lista"
                @click="layout = 'list'"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16"><rect x="1" y="1" width="14" height="3" rx="1" /><rect x="1" y="6" width="14" height="3" rx="1" /><rect x="1" y="11" width="14" height="3" rx="1" /></svg>
              </button>
              <button
                type="button"
                class="p-1.5 transition-colors"
                :class="layout === 'compact' ? 'bg-primary-50 text-primary-600' : 'text-gray-400 hover:text-gray-600'"
                title="Kompaktno"
                @click="layout = 'compact'"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16"><rect x="1" y="1" width="3" height="3" rx="0.5" /><rect x="5.5" y="1" width="3" height="3" rx="0.5" /><rect x="10" y="1" width="3" height="3" rx="0.5" /><rect x="1" y="5.5" width="3" height="3" rx="0.5" /><rect x="5.5" y="5.5" width="3" height="3" rx="0.5" /><rect x="10" y="5.5" width="3" height="3" rx="0.5" /><rect x="1" y="10" width="3" height="3" rx="0.5" /><rect x="5.5" y="10" width="3" height="3" rx="0.5" /><rect x="10" y="10" width="3" height="3" rx="0.5" /></svg>
              </button>
            </div>
            <select
              :value="sortSelectValue"
              class="text-sm border border-gray-300 px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500"
              @change="updateSort(($event.target as HTMLSelectElement).value)"
            >
              <option value="created_at">Najnovije</option>
              <option value="price_asc">Cena ↑</option>
              <option value="price_desc">Cena ↓</option>
              <option value="name_asc">Naziv A-Z</option>
              <option value="name_desc">Naziv Z-A</option>
              <option value="discount">Najveći popust</option>
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
        <ProductGrid :products="products" :loading="loading" :columns="3" :layout="layout" @quick-view="openQuickView" />

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
