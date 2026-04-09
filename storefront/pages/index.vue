<script setup lang="ts">
import type { Product, Category, Post, PaginatedResponse } from '~/types'

interface PageSection {
  id: number
  type: string
  data: Record<string, unknown> | null
  sort_order: number
}

interface Slide {
  title: string
  subtitle: string
  cta_text: string
  cta_link: string
  bg_color?: string
  image?: string
}

const { get } = useApi()
const { items: recentItems } = useRecentlyViewed()

const sections = ref<PageSection[]>([])
const featured = ref<Product[]>([])
const newArrivals = ref<Product[]>([])
const categories = ref<Category[]>([])
const recentPosts = ref<Post[]>([])
const loading = ref(true)
const quickViewProduct = ref<Product | null>(null)
const quickViewOpen = ref(false)
const activeSlide = ref(0)
let slideInterval: ReturnType<typeof setInterval> | null = null

function hasSection(type: string): boolean {
  return sections.value.some(s => s.type === type)
}

function sectionData(type: string): Record<string, unknown> | null {
  return sections.value.find(s => s.type === type)?.data || null
}

async function fetchData() {
  loading.value = true
  try {
    const [sectionsRes, productsRes, catsRes] = await Promise.all([
      get<{ data: PageSection[] }>('/v1/page-sections/homepage').catch(() => ({ data: [] })),
      get<PaginatedResponse<Product>>('/v1/products', { featured: 1, per_page: 12 }),
      get<{ data: Category[] }>('/v1/categories'),
    ])

    sections.value = sectionsRes.data
    featured.value = productsRes.data
    categories.value = catsRes.data

    // Fallback ako nema sekcija u bazi — default
    if (sections.value.length === 0) {
      sections.value = [
        { id: 0, type: 'hero_slideshow', data: { slides: [{ title: 'Kvalitetni proizvodi za vaš dom', subtitle: 'Otkrijte širok izbor dečijeg nameštaja i dekoracije.', cta_text: 'Pogledaj ponudu', cta_link: '/proizvodi' }] }, sort_order: 0 },
        { id: 1, type: 'category_grid', data: { title: 'Kupujte po kategorijama' }, sort_order: 1 },
        { id: 2, type: 'featured_products', data: { title: 'Istaknuti proizvodi' }, sort_order: 2 },
        { id: 3, type: 'trust_badges', data: null, sort_order: 3 },
        { id: 4, type: 'newsletter', data: null, sort_order: 4 },
      ]
    }

    // Lazy load za opcione sekcije — čitaj limit iz section data
    if (hasSection('new_arrivals')) {
      try {
        const limit = (sectionData('new_arrivals')?.limit as number) || 8
        const res = await get<PaginatedResponse<Product>>('/v1/products', { sort: 'created_at', per_page: limit })
        newArrivals.value = res.data
      }
      catch { /* silent */ }
    }

    if (hasSection('blog_preview')) {
      try {
        const blogLimit = (sectionData('blog_preview')?.limit as number) || 3
        const res = await get<PaginatedResponse<Post>>('/v1/blog', { per_page: blogLimit })
        recentPosts.value = res.data
      }
      catch { /* silent */ }
    }

    // Hero slideshow auto-play
    startSlideshow()
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function startSlideshow() {
  const heroData = sectionData('hero_slideshow')
  const slides = (heroData?.slides as Slide[]) || []
  if (slides.length > 1) {
    slideInterval = setInterval(() => {
      activeSlide.value = (activeSlide.value + 1) % slides.length
    }, 5000)
  }
}

function goToSlide(index: number) {
  activeSlide.value = index
  if (slideInterval) clearInterval(slideInterval)
  startSlideshow()
}

function openQuickView(product: Product) {
  quickViewProduct.value = product
  quickViewOpen.value = true
}

onMounted(fetchData)
onUnmounted(() => { if (slideInterval) clearInterval(slideInterval) })

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
    <template v-for="section in sections" :key="section.id">

      <!-- Hero Slideshow with Category Sidebar -->
      <section v-if="section.type === 'hero_slideshow'" class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex gap-6">
          <!-- Category sidebar (desktop only) -->
          <div class="hidden lg:block w-64 flex-shrink-0">
            <div class="bg-primary-800 text-white px-4 py-3 font-semibold text-sm flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
              Kupuj po kategorijama
            </div>
            <div class="border border-t-0 border-gray-200 bg-white">
              <NuxtLink
                v-for="cat in categories"
                :key="cat.id"
                :to="`/proizvodi?category=${cat.id}`"
                class="flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600 border-b border-gray-100 last:border-b-0 transition-colors"
              >
                {{ cat.name }}
                <svg v-if="cat.children?.length" class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
              </NuxtLink>
            </div>
          </div>

          <!-- Slideshow -->
          <div class="flex-1 relative overflow-hidden">
            <div
              v-for="(slide, i) in (section.data?.slides as Slide[] || [])"
              :key="i"
              class="transition-opacity duration-700"
              :class="activeSlide === i ? 'block' : 'hidden'"
              :style="{ backgroundColor: slide.bg_color || '#001219' }"
            >
              <div class="px-8 py-12 md:py-16 flex flex-col md:flex-row items-center gap-8 text-white min-h-[360px]">
                <div class="md:w-1/2">
                  <h1 class="text-3xl md:text-4xl font-bold leading-tight mb-4">{{ slide.title }}</h1>
                  <p class="text-white/80 text-base mb-6">{{ slide.subtitle }}</p>
                  <NuxtLink :to="slide.cta_link || '/proizvodi'">
                    <UiAtomsButton variant="secondary" size="lg">{{ slide.cta_text || 'Pogledaj' }}</UiAtomsButton>
                  </NuxtLink>
                </div>
                <div class="md:w-1/2">
                  <img
                    v-if="slide.image || featured[i]?.images?.[0]"
                    :src="slide.image || resolveImageUrl(featured[i]?.images?.[0]?.image_path || '')"
                    :alt="slide.title"
                    :fetchpriority="i === 0 ? 'high' : undefined"
                    width="500"
                    height="500"
                    class="w-full max-w-sm mx-auto aspect-square object-cover"
                  />
                </div>
              </div>
            </div>
            <!-- Dots -->
            <div v-if="(section.data?.slides as Slide[] || []).length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
              <button
                v-for="(_, i) in (section.data?.slides as Slide[] || [])"
                :key="i"
                class="w-2.5 h-2.5 rounded-full transition-colors"
                :class="activeSlide === i ? 'bg-white' : 'bg-white/40'"
                @click="goToSlide(i)"
          />
            </div>
          </div>
        </div>
      </section>

      <!-- Category Grid -->
      <section v-else-if="section.type === 'category_grid'" class="max-w-7xl mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">{{ (section.data?.title as string) || 'Kategorije' }}</h2>
        <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 gap-4" :class="{ 'lg:grid-cols-3': (section.data?.columns as number) === 3, 'lg:grid-cols-4': (section.data?.columns as number) === 4, 'lg:grid-cols-5': (section.data?.columns as number) === 5, 'lg:grid-cols-6': !section.data?.columns || (section.data?.columns as number) === 6 }">
          <UiAtomsSkeleton v-for="i in ((section.data?.columns as number) || 6)" :key="i" height="120px" />
        </div>
        <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-4" :class="{ 'lg:grid-cols-3': (section.data?.columns as number) === 3, 'lg:grid-cols-4': (section.data?.columns as number) === 4, 'lg:grid-cols-5': (section.data?.columns as number) === 5, 'lg:grid-cols-6': !section.data?.columns || (section.data?.columns as number) === 6 }">
          <NuxtLink
            v-for="cat in categories"
            :key="cat.id"
            :to="`/kategorije/${cat.slug}`"
            class="group bg-primary-50 border border-primary-100 p-4 text-center hover:bg-primary-100 transition-colors"
          >
            <img v-if="cat.image_path" :src="resolveImageUrl(cat.image_path)" :alt="cat.name" class="w-12 h-12 mx-auto mb-2 object-cover rounded" loading="lazy" />
            <svg v-else class="w-8 h-8 mx-auto text-primary-600 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
            </svg>
            <p class="text-sm font-medium text-gray-800 group-hover:text-primary-700">{{ cat.name }}</p>
          </NuxtLink>
        </div>
      </section>

      <!-- Featured Products -->
      <section v-else-if="section.type === 'featured_products'" class="max-w-7xl mx-auto px-4 py-12">
        <ProductCarousel
          :title="(section.data?.title as string) || 'Istaknuti proizvodi'"
          :products="featured.slice(0, (section.data?.limit as number) || 12)"
          :items-per-row="(section.data?.items_per_row as number) || 5"
          :mobile-items-per-row="(section.data?.mobile_items_per_row as number) || 2"
          @quick-view="openQuickView"
        />
      </section>

      <!-- New Arrivals -->
      <section v-else-if="section.type === 'new_arrivals' && newArrivals.length" class="max-w-7xl mx-auto px-4 py-12">
        <ProductCarousel
          :title="(section.data?.title as string) || 'Novo u ponudi'"
          :products="newArrivals"
          :items-per-row="(section.data?.items_per_row as number) || 5"
          :mobile-items-per-row="(section.data?.mobile_items_per_row as number) || 2"
          @quick-view="openQuickView"
        />
      </section>

      <!-- Banner Split -->
      <section v-else-if="section.type === 'banner_split'" class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-primary-50 p-8 text-center">
            <p class="text-lg font-bold text-gray-800">{{ (section.data?.left_title as string) }}</p>
            <p class="text-sm text-gray-600 mt-1">{{ (section.data?.left_text as string) }}</p>
          </div>
          <div class="bg-warm p-8 text-center">
            <p class="text-lg font-bold text-gray-800">{{ (section.data?.right_title as string) }}</p>
            <p class="text-sm text-gray-600 mt-1">{{ (section.data?.right_text as string) }}</p>
          </div>
        </div>
      </section>

      <!-- Blog Preview -->
      <section v-else-if="section.type === 'blog_preview' && recentPosts.length > 0" class="max-w-7xl mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">{{ (section.data?.title as string) || 'Iz bloga' }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <NuxtLink v-for="post in recentPosts" :key="post.id" :to="`/blog/${post.slug}`" class="group border border-gray-200 hover:shadow-md transition-shadow">
            <div class="aspect-video bg-gray-100 overflow-hidden">
              <img v-if="post.featured_image" :src="resolveImageUrl(post.featured_image)" :alt="post.title" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
            </div>
            <div class="p-4">
              <h3 class="font-semibold text-gray-800 group-hover:text-primary-600 line-clamp-2">{{ post.title }}</h3>
              <p v-if="post.excerpt" class="text-sm text-gray-500 line-clamp-2 mt-1">{{ post.excerpt }}</p>
            </div>
          </NuxtLink>
        </div>
      </section>

      <!-- Recently Viewed -->
      <section v-else-if="section.type === 'recently_viewed' && recentItems.length > 0" class="max-w-7xl mx-auto px-4 py-12">
        <ProductCarousel :title="(section.data?.title as string) || 'Nedavno pregledano'" :products="recentItems" @quick-view="openQuickView" />
      </section>

      <!-- Trust Badges -->
      <UiMoleculesTrustBadges v-else-if="section.type === 'trust_badges'" />

      <!-- Newsletter -->
      <UiMoleculesNewsletter v-else-if="section.type === 'newsletter'" />

      <!-- Text Block -->
      <section v-else-if="section.type === 'text_block'" class="max-w-4xl mx-auto px-4 py-12">
        <div class="prose prose-lg max-w-none" v-html="(section.data?.content as string) || ''" />
      </section>

      <!-- Spacer -->
      <div v-else-if="section.type === 'spacer'" :style="{ height: `${(section.data?.height as number) || 40}px` }" />

    </template>

    <ProductQuickView v-model="quickViewOpen" :product="quickViewProduct" />
  </div>
</template>
