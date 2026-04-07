<script setup lang="ts">
import type { Product, PaginatedResponse } from '~/types'

const route = useRoute()
const { get } = useApi()

const products = ref<Product[]>([])
const loading = ref(true)
const total = ref(0)
const query = computed(() => String(route.query.q || ''))
const quickViewProduct = ref<Product | null>(null)
const quickViewOpen = ref(false)

async function fetchResults() {
  if (!query.value) return
  loading.value = true
  try {
    const data = await get<PaginatedResponse<Product>>('/v1/search', { q: query.value, per_page: 24 })
    products.value = data.data
    total.value = data.meta.total
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function openQuickView(product: Product) {
  quickViewProduct.value = product
  quickViewOpen.value = true
}

watch(query, fetchResults)
onMounted(fetchResults)

useHead({ title: computed(() => `Pretraga: ${query.value} — eLokal`) })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <LayoutBreadcrumbs :items="[{ label: `Pretraga: ${query}` }]" />

    <h1 class="text-2xl font-bold text-gray-800 mb-2">Rezultati pretrage</h1>
    <p class="text-gray-500 mb-6">{{ total }} rezultata za "<span class="font-medium text-gray-800">{{ query }}</span>"</p>

    <ProductGrid :products="products" :loading="loading" :columns="4" @quick-view="openQuickView" />

    <div v-if="!loading && products.length === 0" class="text-center py-12">
      <p class="text-gray-500 mb-4">Nismo pronašli proizvode za vašu pretragu.</p>
      <NuxtLink to="/products">
        <UiAtomsButton variant="outline">Pogledaj sve proizvode</UiAtomsButton>
      </NuxtLink>
    </div>

    <ProductQuickView v-model="quickViewOpen" :product="quickViewProduct" />
  </div>
</template>
