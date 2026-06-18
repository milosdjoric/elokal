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
const notifyMeProduct = ref<Product | null>(null)
const notifyMeOpen = ref(false)
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

function openNotifyMe(product: Product) {
  notifyMeProduct.value = product
  notifyMeOpen.value = true
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

// Filter panel toggle — Vitra-style hidden by default
const filterPanelOpen = ref(false)

// Trenutna kategorija (za hero header)
const activeCategory = computed(() => {
  if (!categoryFilter.value) return null
  const find = (cats: Category[]): Category | null => {
    for (const c of cats) {
      if (String(c.id) === categoryFilter.value) return c
      if (c.children) {
        const found = find(c.children)
        if (found) return found
      }
    }
    return null
  }
  return find(categories.value)
})

// Resolve product image
function productImage(p: Product): string | null {
  const img = p.images?.find(i => i.is_primary) || p.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
}

// Designer line (iz meta_title koji ima format "Name — Designer · Year")
function productDesigner(p: Product): string | null {
  if (!p.meta_title) return null
  const match = p.meta_title.match(/—\s*([^·]+?)(?:\s*·|\s*$)/)
  return match ? match[1].trim() : null
}

onMounted(() => {
  readQuery()
  fetchProducts()
  fetchCategories()
  fetchFilters()
})

useHead({ title: 'Proizvodi — sloj kolektiv' })
</script>

<template>
  <div class="bg-paper">
    <!-- 1. EDITORIAL ZAGLAVLJE — svetli breadcrumb + centriran bold naslov/opis (Vitra PLP stil) -->
    <div class="border-b border-ink-100">
      <div class="max-w-[1400px] mx-auto px-6 lg:px-10">
        <!-- Breadcrumb (svetli, gore-levo) -->
        <nav class="flex items-center gap-2 py-4 text-[12px] text-ink-400" aria-label="Breadcrumb">
          <NuxtLink to="/" class="hover:text-ink-700 transition-colors">Početna</NuxtLink>
          <Icon name="lucide:chevron-right" class="w-3 h-3 text-ink-300" />
          <NuxtLink to="/proizvodi" class="hover:text-ink-700 transition-colors">Kategorije</NuxtLink>
          <template v-if="activeCategory">
            <Icon name="lucide:chevron-right" class="w-3 h-3 text-ink-300" />
            <span class="text-ink-800">{{ activeCategory.name }}</span>
          </template>
        </nav>

        <!-- Centriran naslov + opis -->
        <div class="max-w-2xl mx-auto text-center pt-8 pb-14 md:pt-14 md:pb-20">
          <h1 class="text-[34px] md:text-[48px] lg:text-[56px] font-bold text-ink-900 tracking-[-0.02em] leading-[1.05]">
            {{ activeCategory?.name || 'Svi proizvodi' }}
          </h1>
          <p class="mt-5 text-[15px] md:text-[16px] text-ink-600 leading-[1.7]">
            <span v-if="activeCategory?.description">{{ activeCategory.description }}</span>
            <span v-else>
              Opendesk dizajni izrađeni od breze šperploče. Svaki komad sklopljen po radioničkom standardu — bez šrafova gde nisu potrebni, isporuka 14–21 dan.
            </span>
          </p>
        </div>
      </div>
    </div>

    <!-- 3. TOOLBAR — Filter toggle, sort, count -->
    <div class="border-b border-ink-100">
      <div class="max-w-[1400px] mx-auto px-6 lg:px-10 h-14 flex items-center justify-between text-[14px]">
        <div class="flex items-center gap-6">
          <button
            type="button"
            class="inline-flex items-center gap-2 text-ink-700 hover:text-ink-800 transition-colors"
            :class="filterPanelOpen ? 'text-ink-800' : ''"
            @click="filterPanelOpen = !filterPanelOpen"
          >
            <Icon :name="filterPanelOpen ? 'lucide:x' : 'lucide:sliders-horizontal'" class="w-4 h-4" />
            <span>{{ filterPanelOpen ? 'Zatvori filtere' : 'Filteri' }}</span>
          </button>
          <span class="text-ink-400 hidden md:inline">·</span>
          <span class="text-ink-500"><span class="text-ink-800 tabular-nums">{{ total }}</span> proizvoda</span>
        </div>
        <div class="flex items-center gap-2 text-ink-700">
          <Icon name="lucide:arrow-up-down" class="w-4 h-4 text-ink-500" />
          <select
            :value="sortSelectValue"
            class="text-[14px] bg-transparent border-0 px-0 py-1.5 focus:outline-none cursor-pointer"
            @change="updateSort(($event.target as HTMLSelectElement).value)"
          >
            <option value="created_at">Najnovije</option>
            <option value="price_asc">Cena ↑</option>
            <option value="price_desc">Cena ↓</option>
            <option value="name_asc">Naziv A–Z</option>
            <option value="name_desc">Naziv Z–A</option>
            <option value="discount">Najveći popust</option>
          </select>
        </div>
      </div>
    </div>

    <!-- 4. FILTER PANEL — collapse, ispod toolbar-a -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out-soft"
      enter-from-class="opacity-0 max-h-0"
      enter-to-class="opacity-100 max-h-[800px]"
      leave-active-class="transition-all duration-200"
      leave-from-class="opacity-100 max-h-[800px]"
      leave-to-class="opacity-0 max-h-0"
    >
      <div v-if="filterPanelOpen" class="border-b border-ink-100 bg-ink-50 overflow-hidden">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-10 grid grid-cols-1 md:grid-cols-4 gap-10">
          <!-- Kategorija -->
          <div>
            <h3 class="text-[13px] font-medium text-ink-800 mb-4 pb-2 border-b border-ink-200">Kategorija</h3>
            <ul class="space-y-1.5">
              <li>
                <button
                  class="text-[14px] w-full text-left transition-colors"
                  :class="!categoryFilter ? 'text-terra-600' : 'text-ink-700 hover:text-ink-800'"
                  @click="updateCategory('')"
                >
                  Sve
                </button>
              </li>
              <li v-for="cat in categories" :key="cat.id">
                <button
                  class="text-[14px] w-full text-left transition-colors"
                  :class="categoryFilter === String(cat.id) ? 'text-terra-600' : 'text-ink-700 hover:text-ink-800'"
                  @click="updateCategory(String(cat.id))"
                >
                  {{ cat.name }}
                </button>
              </li>
            </ul>
          </div>

          <!-- Cena -->
          <div>
            <h3 class="text-[13px] font-medium text-ink-800 mb-4 pb-2 border-b border-ink-200">Cena (RSD)</h3>
            <div class="flex items-center gap-2 mb-3">
              <input
                v-model="minPrice"
                type="number"
                :placeholder="String(Math.floor(priceRange.min))"
                class="w-full px-0 py-2 text-[14px] bg-transparent border-0 border-b border-ink-300 focus:outline-none focus:border-ink-800 placeholder:text-ink-300 tabular-nums"
                @keyup.enter="applyPriceRange"
              />
              <span class="text-ink-300">—</span>
              <input
                v-model="maxPrice"
                type="number"
                :placeholder="String(Math.ceil(priceRange.max))"
                class="w-full px-0 py-2 text-[14px] bg-transparent border-0 border-b border-ink-300 focus:outline-none focus:border-ink-800 placeholder:text-ink-300 tabular-nums"
                @keyup.enter="applyPriceRange"
              />
            </div>
            <button class="text-[13px] text-terra-600 hover:text-terra-700 transition-colors" @click="applyPriceRange">
              Primeni →
            </button>
          </div>

          <!-- Attribute filteri -->
          <div v-for="attr in filterAttributes.slice(0, 2)" :key="attr.id">
            <h3 class="text-[13px] font-medium text-ink-800 mb-4 pb-2 border-b border-ink-200">{{ attr.name }}</h3>
            <div v-if="attr.type === 'color'" class="flex flex-wrap gap-2">
              <button
                v-for="val in attr.values"
                :key="val.id"
                type="button"
                :title="val.value"
                class="w-7 h-7 transition-all"
                :class="selectedAttributes[attr.slug]?.has(val.id) ? 'ring-1 ring-offset-2 ring-ink-800' : 'ring-1 ring-ink-200 hover:ring-ink-700'"
                :style="{ backgroundColor: val.color_hex || '#ccc' }"
                @click="toggleAttributeFilter(attr.slug, val.id)"
              />
            </div>
            <div v-else class="space-y-2">
              <label
                v-for="val in attr.values"
                :key="val.id"
                class="flex items-center gap-2 text-[14px] text-ink-700 cursor-pointer hover:text-ink-800"
              >
                <input
                  type="checkbox"
                  :checked="selectedAttributes[attr.slug]?.has(val.id)"
                  class="w-3.5 h-3.5 accent-ink-800"
                  @change="toggleAttributeFilter(attr.slug, val.id)"
                />
                {{ val.value }}
              </label>
            </div>
          </div>
        </div>

        <!-- Active filters + clear -->
        <div v-if="activeFilters.length > 0" class="border-t border-ink-100 bg-paper">
          <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-4 flex flex-wrap items-center gap-2">
            <span class="text-[12px] text-ink-500 mr-2">Aktivni:</span>
            <span
              v-for="(filter, i) in activeFilters"
              :key="i"
              class="inline-flex items-center gap-2 bg-ink-50 text-[13px] text-ink-700 pl-3 pr-1.5 py-1.5"
            >
              {{ filter.label }}
              <button class="w-4 h-4 flex items-center justify-center text-ink-400 hover:text-terra-600" @click="filter.clear()">
                <Icon name="lucide:x" class="w-3 h-3" />
              </button>
            </span>
            <button v-if="activeFilters.length > 1" class="text-[13px] text-terra-600 hover:text-terra-700 ml-2 transition-colors" @click="clearAllFilters">
              Obriši sve →
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- 5. PRODUCT GRID — Vitra-style full-bleed 4 col, čisto bele kartice -->
    <div class="bg-ink-100">
      <div class="w-full">
        <!-- Loading skeleton -->
        <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-px">
          <div v-for="i in 8" :key="i" class="bg-surface p-8 min-h-[480px] md:min-h-[540px]">
            <div class="h-[300px] md:h-[340px]">
              <UiAtomsSkeleton height="100%" />
            </div>
            <div class="pt-6 space-y-2">
              <UiAtomsSkeleton height="14px" width="60%" class="mx-auto" />
              <UiAtomsSkeleton height="12px" width="40%" class="mx-auto" />
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="!products.length" class="bg-surface py-20 px-6 text-center">
          <p class="text-[20px] font-light text-ink-800">Nema proizvoda po vašim filterima.</p>
          <button class="mt-6 text-[14px] text-terra-600 hover:text-terra-700 transition-colors" @click="clearAllFilters">
            Obriši filtere →
          </button>
        </div>

        <!-- Products -->
        <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-px">
          <NuxtLink
            v-for="product in products"
            :key="product.id"
            :to="`/proizvodi/${product.slug}`"
            class="group relative bg-surface flex flex-col items-stretch px-6 lg:px-10 pt-12 pb-10 min-h-[480px] md:min-h-[540px] transition-colors hover:bg-ink-50"
          >
            <!-- Sale signal — apsolutni gornji-levi, vrlo suptilan -->
            <p v-if="product.is_on_sale && product.sale_percentage" class="absolute top-5 left-5 text-[11px] text-terra-600 tabular-nums tracking-[0.04em] z-10">
              −{{ product.sale_percentage }}%
            </p>
            <p v-else-if="product.stock_quantity === 0" class="absolute top-5 left-5 text-[11px] text-ink-400 tracking-[0.04em] z-10">
              Rasprodato
            </p>

            <!-- Wishlist — apsolutni gornji-desni, fade on hover -->
            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-10" @click.prevent>
              <ProductWishlistButton :product-id="product.id" size="sm" />
            </div>

            <!-- Slika centralno — kartica bg matches Opendesk studio gray, slika se slijeva bez vidljive granice -->
            <div class="flex-1 flex items-center justify-center min-h-[280px] md:min-h-[320px]">
              <img
                v-if="productImage(product)"
                :src="productImage(product)!"
                :alt="product.name"
                loading="lazy"
                class="max-w-full max-h-[300px] md:max-h-[340px] object-contain transition-transform duration-500 ease-out-soft group-hover:scale-[1.04]"
              />
              <div v-else class="w-full h-[280px] bg-placeholder" />
            </div>

            <!-- Naziv + dizajner — centralno, blizak slici -->
            <div class="pt-6 text-center">
              <h3 class="text-[16px] font-medium text-ink-800 leading-snug">{{ product.name }}</h3>
              <p v-if="productDesigner(product)" class="mt-1 text-[14px] text-ink-500 leading-snug">
                {{ productDesigner(product) }}
              </p>
              <p v-else class="mt-1 text-[14px] text-ink-500 tabular-nums">
                {{ formatPrice(product.effective_price) }}
              </p>
            </div>
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="bg-paper border-t border-ink-100">
      <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-12 flex justify-center gap-1">
        <button
          v-for="p in totalPages"
          :key="p"
          class="w-10 h-10 text-[14px] tabular-nums transition-colors"
          :class="p === page ? 'text-ink-800 border-b border-ink-800' : 'text-ink-400 hover:text-ink-800'"
          @click="changePage(p)"
        >
          {{ p }}
        </button>
      </div>
    </div>

    <ProductQuickView v-model="quickViewOpen" :product="quickViewProduct" />
    <ProductNotifyMeModal v-model="notifyMeOpen" :product="notifyMeProduct" />
  </div>
</template>
