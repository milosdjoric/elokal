<script setup lang="ts">
import type { Product, Category } from '~/types'

const { get } = useApi()

const query = ref('')
const results = ref<Product[]>([])
const showDropdown = ref(false)
const loading = ref(false)
const categoryScope = ref('')
const categories = ref<Category[]>([])
const trendingSearches = ref<string[]>([])
let debounceTimer: ReturnType<typeof setTimeout>

async function fetchSearchMeta() {
  try {
    const [catData, settingsData] = await Promise.all([
      get<{ data: Category[] }>('/v1/categories'),
      get<Record<string, string>>('/v1/settings'),
    ])
    categories.value = catData.data
    const trending = settingsData.trending_searches
    if (trending) {
      trendingSearches.value = trending.split(',').map(s => s.trim()).filter(Boolean)
    }
  }
  catch { /* silent */ }
}

async function search() {
  if (query.value.length < 2) {
    results.value = []
    if (query.value.length === 0) showDropdown.value = trendingSearches.value.length > 0
    else showDropdown.value = false
    return
  }

  loading.value = true
  try {
    const params: Record<string, string | number> = { q: query.value, per_page: 5 }
    if (categoryScope.value) params.category = categoryScope.value
    const data = await get<{ data: Product[] }>('/v1/search', params)
    results.value = data.data
    showDropdown.value = true
  }
  catch { results.value = [] }
  finally { loading.value = false }
}

function onInput() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(search, 300)
}

function handleSubmit() {
  if (query.value.trim()) {
    showDropdown.value = false
    const routeQuery: Record<string, string> = { q: query.value }
    if (categoryScope.value) routeQuery.category = categoryScope.value
    navigateTo({ path: '/search', query: routeQuery })
  }
}

function useTrending(term: string) {
  query.value = term
  showDropdown.value = false
  search()
}

function selectProduct(slug: string) {
  showDropdown.value = false
  query.value = ''
  navigateTo(`/products/${slug}`)
}

function primaryImage(product: Product): string | null {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
}

function close() { showDropdown.value = false }
function delayedClose() { setTimeout(close, 200) }

function onFocus() {
  if (query.value.length >= 2) showDropdown.value = true
  else if (query.value.length === 0 && trendingSearches.value.length > 0) showDropdown.value = true
}

onMounted(fetchSearchMeta)
</script>

<template>
  <div class="relative" @focusout="delayedClose">
    <form @submit.prevent="handleSubmit" class="flex">
      <!-- Category scope -->
      <select
        v-if="categories.length > 0"
        v-model="categoryScope"
        class="hidden md:block px-3 py-2.5 text-sm border border-gray-300 border-r-0 bg-gray-50 text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500"
        @change="query.length >= 2 && search()"
      >
        <option value="">Sve kategorije</option>
        <option v-for="cat in categories" :key="cat.id" :value="String(cat.id)">{{ cat.name }}</option>
      </select>

      <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
        <input
          v-model="query"
          type="text"
          placeholder="Pretraži proizvode..."
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
          @input="onInput"
          @focus="onFocus"
        />
      </div>
    </form>

    <!-- Dropdown -->
    <div v-if="showDropdown" class="absolute left-0 right-0 top-full bg-white border border-gray-200 shadow-lg z-50 max-h-80 overflow-y-auto">
      <!-- Trending searches (kad je input prazan) -->
      <div v-if="query.length < 2 && trendingSearches.length > 0" class="p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Popularno</p>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="term in trendingSearches"
            :key="term"
            class="text-sm px-3 py-1 bg-gray-100 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-full transition-colors"
            @mousedown.prevent="useTrending(term)"
          >
            {{ term }}
          </button>
        </div>
      </div>
      <div v-else-if="loading" class="p-4 text-center">
        <UiAtomsSpinner size="sm" />
      </div>
      <div v-else-if="results.length === 0 && query.length >= 2" class="p-4 text-sm text-gray-500 text-center">
        Nema rezultata za "{{ query }}"
      </div>
      <div v-else>
        <button
          v-for="product in results"
          :key="product.id"
          class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 text-left"
          @click="selectProduct(product.slug)"
        >
          <img
            v-if="primaryImage(product)"
            :src="primaryImage(product)!"
            class="w-10 h-10 object-cover flex-shrink-0"
            alt=""
          />
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-800 truncate">{{ product.name }}</p>
            <p class="text-xs text-primary-600 font-semibold">{{ Number(product.effective_price).toLocaleString('sr-RS') }} RSD</p>
          </div>
        </button>
        <NuxtLink
          :to="{ path: '/search', query: { q: query } }"
          class="block px-4 py-2 text-sm text-primary-600 font-medium text-center border-t hover:bg-gray-50"
          @click="showDropdown = false"
        >
          Prikaži sve rezultate →
        </NuxtLink>
      </div>
    </div>
  </div>
</template>
