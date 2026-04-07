<script setup lang="ts">
import type { Product, PaginatedResponse } from '~/types'

const route = useRoute()
const { get } = useApi()
const { searches: recentSearches, add: addRecent, clear: clearRecent } = useRecentSearches()

const products = ref<Product[]>([])
const loading = ref(true)
const total = ref(0)
const query = computed(() => String(route.query.q || ''))
const quickViewProduct = ref<Product | null>(null)
const quickViewOpen = ref(false)

async function fetchResults() {
  if (!query.value) {
    products.value = []
    total.value = 0
    loading.value = false
    return
  }
  loading.value = true
  try {
    const data = await get<PaginatedResponse<Product>>('/v1/search', { q: query.value, per_page: 24 })
    products.value = data.data
    total.value = data.meta.total
    addRecent(query.value)
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function openQuickView(product: Product) {
  quickViewProduct.value = product
  quickViewOpen.value = true
}

function searchRecent(q: string) {
  navigateTo({ path: '/search', query: { q } })
}

watch(query, fetchResults)
onMounted(fetchResults)

useHead({ title: computed(() => query.value ? `Pretraga: ${query.value} — eLokal` : 'Pretraga — eLokal') })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <LayoutBreadcrumbs :items="[{ label: query ? `Pretraga: ${query}` : 'Pretraga' }]" />

    <h1 class="text-2xl font-bold text-gray-800 mb-2">Rezultati pretrage</h1>

    <template v-if="query">
      <p class="text-gray-500 mb-6">{{ total }} rezultata za "<span class="font-medium text-gray-800">{{ query }}</span>"</p>

      <ProductGrid :products="products" :loading="loading" :columns="4" @quick-view="openQuickView" />

      <div v-if="!loading && products.length === 0" class="text-center py-12">
        <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
        <p class="text-gray-500 mb-2">Nismo pronašli proizvode za "{{ query }}".</p>
        <p class="text-sm text-gray-400 mb-6">Pokušajte sa drugim pojmom ili pregledajte naše kategorije.</p>
        <NuxtLink to="/products">
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
