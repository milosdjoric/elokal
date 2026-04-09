<script setup lang="ts">
import type { Product, PaginatedResponse } from '~/types'

const route = useRoute()
const router = useRouter()
const { get } = useApi()
const { searches: recentSearches, add: addRecent, clear: clearRecent } = useRecentSearches()

const products = ref<Product[]>([])
const loading = ref(true)
const total = ref(0)
const page = ref(1)
const totalPages = ref(1)
const sortBy = ref('relevance')
const perPage = ref(12)
const query = computed(() => String(route.query.q || ''))
const quickViewProduct = ref<Product | null>(null)
const quickViewOpen = ref(false)

const sortOptions = [
  { value: 'relevance', label: 'Relevantnost' },
  { value: 'created_at', label: 'Najnovije' },
  { value: 'price_asc', label: 'Cena: niska → visoka' },
  { value: 'price_desc', label: 'Cena: visoka → niska' },
  { value: 'name', label: 'Naziv A-Z' },
]

async function fetchResults() {
  if (!query.value) {
    products.value = []
    total.value = 0
    loading.value = false
    return
  }
  loading.value = true
  try {
    const data = await get<PaginatedResponse<Product>>('/v1/search', {
      q: query.value,
      per_page: perPage.value,
      page: page.value,
      sort: sortBy.value,
    })
    products.value = data.data
    total.value = data.meta.total
    totalPages.value = data.meta.last_page
    addRecent(query.value)
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function changePage(p: number) {
  page.value = p
  fetchResults()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function openQuickView(product: Product) {
  quickViewProduct.value = product
  quickViewOpen.value = true
}

function searchRecent(q: string) {
  navigateTo({ path: '/pretraga', query: { q } })
}

watch(sortBy, () => { page.value = 1; fetchResults() })
watch(perPage, () => { page.value = 1; fetchResults() })
watch(query, () => { page.value = 1; fetchResults() })
onMounted(fetchResults)

useHead({ title: computed(() => query.value ? `Pretraga: ${query.value} — eLokal` : 'Pretraga — eLokal') })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <LayoutBreadcrumbs :items="[{ label: query ? `Pretraga: ${query}` : 'Pretraga' }]" />

    <h1 class="text-2xl font-bold text-gray-800 mb-2">Rezultati pretrage</h1>

    <template v-if="query">
      <!-- Toolbar -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200">
        <p class="text-sm text-gray-500">{{ total }} rezultata za "<span class="font-medium text-gray-800">{{ query }}</span>"</p>
        <div class="flex items-center gap-3">
          <select v-model="sortBy" class="px-2 py-1.5 text-xs border border-gray-300 focus:outline-none focus:ring-1 focus:ring-primary-500">
            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
          <select v-model="perPage" class="px-2 py-1.5 text-xs border border-gray-300 focus:outline-none focus:ring-1 focus:ring-primary-500">
            <option :value="12">12</option>
            <option :value="24">24</option>
            <option :value="48">48</option>
          </select>
        </div>
      </div>

      <ProductGrid :products="products" :loading="loading" :columns="4" @quick-view="openQuickView" />

      <!-- Paginacija -->
      <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-8">
        <button
          :disabled="page <= 1"
          class="px-3 py-2 text-sm border border-gray-300 disabled:opacity-30"
          @click="changePage(page - 1)"
        >
          ←
        </button>
        <button
          v-for="p in totalPages"
          :key="p"
          class="w-10 h-10 text-sm font-medium border transition-colors"
          :class="p === page ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
          @click="changePage(p)"
        >
          {{ p }}
        </button>
        <button
          :disabled="page >= totalPages"
          class="px-3 py-2 text-sm border border-gray-300 disabled:opacity-30"
          @click="changePage(page + 1)"
        >
          →
        </button>
      </div>

      <div v-if="!loading && products.length === 0" class="text-center py-12">
        <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
        <p class="text-gray-500 mb-2">Nismo pronašli proizvode za "{{ query }}".</p>
        <p class="text-sm text-gray-400 mb-6">Pokušajte sa drugim pojmom ili pregledajte naše kategorije.</p>
        <NuxtLink to="/proizvodi">
          <UiAtomsButton variant="outline">Pogledaj sve proizvode</UiAtomsButton>
        </NuxtLink>
      </div>
    </template>

    <template v-else>
      <div class="py-12">
        <div v-if="recentSearches.length" class="mb-8">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Nedavne pretrage</h2>
            <button class="text-xs text-gray-400 hover:text-red-500" @click="clearRecent">Obriši</button>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="q in recentSearches"
              :key="q"
              class="px-4 py-2 text-sm border border-gray-200 text-gray-700 hover:border-primary-300 hover:text-primary-600 transition-colors"
              @click="searchRecent(q)"
            >
              {{ q }}
            </button>
          </div>
        </div>
        <p class="text-gray-500">Unesite pojam za pretragu.</p>
      </div>
    </template>

    <ProductQuickView v-model="quickViewOpen" :product="quickViewProduct" />
  </div>
</template>
