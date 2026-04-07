<script setup lang="ts">
import type { Product, Category, PaginatedResponse } from '~/types'

const route = useRoute()
const { get } = useApi()

const category = ref<Category | null>(null)
const products = ref<Product[]>([])
const loading = ref(true)
const page = ref(1)
const totalPages = ref(1)
const total = ref(0)
const quickViewProduct = ref<Product | null>(null)
const quickViewOpen = ref(false)

async function fetchCategory() {
  loading.value = true
  try {
    const data = await get<{ category: Category; data: Product[]; meta: PaginatedResponse<Product>['meta'] }>(
      `/v1/categories/${route.params.slug}`,
      { page: page.value, per_page: 12 }
    )
    category.value = data.category
    products.value = data.data
    totalPages.value = data.meta.last_page
    total.value = data.meta.total
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function changePage(p: number) {
  page.value = p
  fetchCategory()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function openQuickView(product: Product) {
  quickViewProduct.value = product
  quickViewOpen.value = true
}

onMounted(fetchCategory)

useHead({ title: computed(() => category.value ? `${category.value.name} — eLokal` : 'eLokal') })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <LayoutBreadcrumbs :items="[
      { label: 'Kategorije', to: '/products' },
      { label: category?.name || '...' },
    ]" />

    <div v-if="loading && !category" class="py-20 flex justify-center">
      <UiAtomsSpinner size="lg" />
    </div>

    <template v-else-if="category">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">{{ category.name }}</h1>
        <p v-if="category.description" class="text-gray-600 mt-2">{{ category.description }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ total }} proizvoda</p>
      </div>

      <ProductGrid :products="products" :loading="loading" :columns="4" @quick-view="openQuickView" />

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
    </template>

    <ProductQuickView v-model="quickViewOpen" :product="quickViewProduct" />
  </div>
</template>
