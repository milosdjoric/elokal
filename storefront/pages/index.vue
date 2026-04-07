<script setup lang="ts">
import type { Product, Category, PaginatedResponse } from '~/types'

const { get } = useApi()

const featured = ref<Product[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)
const quickViewProduct = ref<Product | null>(null)
const quickViewOpen = ref(false)

async function fetchData() {
  loading.value = true
  try {
    const [productsRes, catsRes] = await Promise.all([
      get<PaginatedResponse<Product>>('/v1/products', { featured: 1, per_page: 12 }),
      get<{ data: Category[] }>('/v1/categories'),
    ])
    featured.value = productsRes.data
    categories.value = catsRes.data
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function openQuickView(product: Product) {
  quickViewProduct.value = product
  quickViewOpen.value = true
}

onMounted(fetchData)

useHead({ title: 'eLokal — Početna' })
</script>

<template>
  <div>
    <!-- Hero -->
    <section class="bg-primary-800 text-white">
      <div class="max-w-7xl mx-auto px-4 py-16 md:py-24 flex flex-col md:flex-row items-center gap-8">
        <div class="md:w-1/2">
          <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-4">
            Kvalitetni proizvodi za vaš dom
          </h1>
          <p class="text-primary-100 text-lg mb-6">
            Otkrijte širok izbor dečijeg nameštaja i dekoracije po najboljim cenama.
          </p>
          <NuxtLink to="/products">
            <UiAtomsButton variant="secondary" size="lg">Pogledaj ponudu</UiAtomsButton>
          </NuxtLink>
        </div>
        <div class="md:w-1/2">
          <img
            v-if="featured[0]?.images?.[0]"
            :src="resolveImageUrl(featured[0].images[0].image_path)"
            :alt="featured[0].name"
            class="w-full max-w-md mx-auto aspect-square object-cover"
          />
        </div>
      </div>
    </section>

    <!-- Categories grid -->
    <section class="max-w-7xl mx-auto px-4 py-12">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Kupujte po kategorijama</h2>
      <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <UiAtomsSkeleton v-for="i in 6" :key="i" height="120px" />
      </div>
      <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <NuxtLink
          v-for="cat in categories"
          :key="cat.id"
          :to="`/categories/${cat.slug}`"
          class="group bg-primary-50 border border-primary-100 p-4 text-center hover:bg-primary-100 transition-colors"
        >
          <svg class="w-8 h-8 mx-auto text-primary-600 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
          </svg>
          <p class="text-sm font-medium text-gray-800 group-hover:text-primary-700">{{ cat.name }}</p>
        </NuxtLink>
      </div>
    </section>

    <!-- Featured products -->
    <section class="max-w-7xl mx-auto px-4 py-12">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Istaknuti proizvodi</h2>
      <ProductGrid :products="featured" :loading="loading" :columns="4" @quick-view="openQuickView" />
    </section>

    <!-- Trust badges -->
    <UiMoleculesTrustBadges />

    <!-- Newsletter -->
    <UiMoleculesNewsletter />

    <!-- Quick view -->
    <ProductQuickView v-model="quickViewOpen" :product="quickViewProduct" />
  </div>
</template>
