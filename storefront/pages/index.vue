<script setup lang="ts">
import type { Product, Category, Post, PaginatedResponse } from '~/types'

const { get } = useApi()
const { items: recentItems } = useRecentlyViewed()

const featured = ref<Product[]>([])
const categories = ref<Category[]>([])
const recentPosts = ref<Post[]>([])
const loading = ref(true)
const quickViewProduct = ref<Product | null>(null)
const quickViewOpen = ref(false)

// Sekcije sa servera (default hardkodovano kao fallback)
const sections = ref<string[]>(['hero', 'categories', 'featured', 'trust_badges', 'newsletter'])

async function fetchData() {
  loading.value = true
  try {
    const [productsRes, catsRes, settingsRes] = await Promise.all([
      get<PaginatedResponse<Product>>('/v1/products', { featured: 1, per_page: 12 }),
      get<{ data: Category[] }>('/v1/categories'),
      get<{ data: Record<string, Record<string, string>> }>('/v1/settings').catch(() => null),
    ])
    featured.value = productsRes.data
    categories.value = catsRes.data

    // Čitaj homepage sekcije iz settings-a
    if (settingsRes?.data?.homepage?.sections) {
      try { sections.value = JSON.parse(settingsRes.data.homepage.sections) }
      catch { /* zadrži default */ }
    }

    // Učitaj blog postove ako je sekcija uključena
    if (sections.value.includes('blog_posts')) {
      try {
        const blogRes = await get<PaginatedResponse<Post>>('/v1/blog', { per_page: 3 })
        recentPosts.value = blogRes.data
      }
      catch { /* silent */ }
    }
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function openQuickView(product: Product) {
  quickViewProduct.value = product
  quickViewOpen.value = true
}

function hasSection(name: string): boolean {
  return sections.value.includes(name)
}

onMounted(fetchData)

useHead({
  title: 'eLokal — Kvalitetni proizvodi za vaš dom',
  link: [{ rel: 'canonical', href: 'http://localhost:3000' }],
})

useSeoMeta({
  description: 'Otkrijte širok izbor dečijeg nameštaja i dekoracije po najboljim cenama. Besplatna dostava za narudžbine iznad 5.000 din.',
  ogTitle: 'eLokal — Kvalitetni proizvodi za vaš dom',
  ogDescription: 'Otkrijte širok izbor dečijeg nameštaja i dekoracije po najboljim cenama.',
  ogType: 'website',
})
</script>

<template>
  <div>
    <!-- Hero -->
    <section v-if="hasSection('hero')" class="bg-primary-800 text-white">
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
    <section v-if="hasSection('categories')" class="max-w-7xl mx-auto px-4 py-12">
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
          <img v-if="cat.image_path" :src="resolveImageUrl(cat.image_path)" :alt="cat.name" class="w-12 h-12 mx-auto mb-2 object-cover rounded" />
          <svg v-else class="w-8 h-8 mx-auto text-primary-600 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
          </svg>
          <p class="text-sm font-medium text-gray-800 group-hover:text-primary-700">{{ cat.name }}</p>
        </NuxtLink>
      </div>
    </section>

    <!-- Featured products -->
    <section v-if="hasSection('featured')" class="max-w-7xl mx-auto px-4 py-12">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Istaknuti proizvodi</h2>
      <ProductGrid :products="featured" :loading="loading" :columns="4" @quick-view="openQuickView" />
    </section>

    <!-- Recently viewed -->
    <section v-if="hasSection('recently_viewed') && recentItems.length > 0" class="max-w-7xl mx-auto px-4 py-12">
      <ProductCarousel title="Nedavno pregledano" :products="recentItems" />
    </section>

    <!-- Blog posts -->
    <section v-if="hasSection('blog_posts') && recentPosts.length > 0" class="max-w-7xl mx-auto px-4 py-12">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Iz bloga</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <NuxtLink
          v-for="post in recentPosts"
          :key="post.id"
          :to="`/blog/${post.slug}`"
          class="group border border-gray-200 hover:shadow-md transition-shadow"
        >
          <div class="aspect-video bg-gray-100 overflow-hidden">
            <img v-if="post.featured_image" :src="resolveImageUrl(post.featured_image)" :alt="post.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
          </div>
          <div class="p-4">
            <h3 class="font-semibold text-gray-800 group-hover:text-primary-600 line-clamp-2">{{ post.title }}</h3>
            <p v-if="post.excerpt" class="text-sm text-gray-500 line-clamp-2 mt-1">{{ post.excerpt }}</p>
          </div>
        </NuxtLink>
      </div>
    </section>

    <!-- Trust badges -->
    <UiMoleculesTrustBadges v-if="hasSection('trust_badges')" />

    <!-- Newsletter -->
    <UiMoleculesNewsletter v-if="hasSection('newsletter')" />

    <!-- Quick view -->
    <ProductQuickView v-model="quickViewOpen" :product="quickViewProduct" />
  </div>
</template>
