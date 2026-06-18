<script setup lang="ts">
import type { Product, Category, PaginatedResponse } from '~/types'

const { get } = useApi()
const { items: recentItems } = useRecentlyViewed()

const featured = ref<Product[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)
const quickViewProduct = ref<Product | null>(null)
const quickViewOpen = ref(false)
const notifyMeProduct = ref<Product | null>(null)
const notifyMeOpen = ref(false)

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

function openNotifyMe(product: Product) {
  notifyMeProduct.value = product
  notifyMeOpen.value = true
}

const heroProduct = computed(() => featured.value[0] || null)
const editorialProducts = computed(() => featured.value.slice(1, 5))
const editorialPair = computed(() => featured.value.slice(5, 7))
const displayCategories = computed(() => categories.value.slice(0, 5))

function primaryImage(product: Product): string | null {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
}

useHead({
  title: 'sloj kolektiv — slojevit nameštaj, dosledno',
  meta: [
    { name: 'description', content: 'Šperploča je slojevita. Brend, sajt i proizvod sve to reflektuju. Jedna ideja, dosledno svuda.' },
  ],
})

onMounted(fetchData)
</script>

<template>
  <div>
    <!-- =============================================== -->
    <!-- 1. HERO — full-bleed image, centered overlay text (Vitra-style) -->
    <!-- =============================================== -->
    <section class="relative">
      <NuxtLink v-if="heroProduct" :to="`/proizvodi/${heroProduct.slug}`" class="block group">
        <div class="relative h-[calc(100vh-7.25rem)] min-h-[520px] max-h-[820px] overflow-hidden bg-ink-50">
          <img
            v-if="primaryImage(heroProduct)"
            :src="primaryImage(heroProduct)!"
            :alt="heroProduct.name"
            fetchpriority="high"
            width="2400"
            height="1400"
            class="w-full h-full object-cover transition-transform duration-[1200ms] group-hover:scale-[1.02]"
          />
          <div v-else class="w-full h-full bg-placeholder" />

          <!-- Subtle bottom gradient za čitljivost belog teksta -->
          <div class="absolute inset-0 bg-gradient-to-t from-ink-900/40 via-transparent to-transparent pointer-events-none" />

          <!-- Centralni tekst overlay -->
          <div class="absolute inset-0 flex flex-col items-center justify-center px-6 text-center">
            <h1 class="text-[40px] sm:text-[52px] md:text-[64px] lg:text-[72px] font-light leading-[1.05] text-paper tracking-[-0.02em] max-w-3xl drop-shadow-lg">
              Priče iz doma za proleće
            </h1>
            <p class="mt-4 text-[16px] md:text-[18px] text-paper/90 max-w-xl leading-relaxed drop-shadow">
              Sezona puna toplih boja i svetla — komadi koji nose priču.
            </p>
            <span class="mt-12 inline-flex flex-col items-center text-[14px] text-paper tracking-[0.04em] group-hover:tracking-[0.12em] transition-all">
              Otkrij
              <span class="block w-24 h-px bg-paper mt-2" />
            </span>
          </div>
        </div>
      </NuxtLink>
      <div v-else class="h-[calc(100vh-7.25rem)] min-h-[520px] bg-placeholder" />
    </section>

    <!-- =============================================== -->
    <!-- 2. KATEGORIJE — Vitra-style full-bleed photo strip, white text overlay -->
    <!-- =============================================== -->
    <section class="bg-paper">
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-px bg-ink-100">
        <NuxtLink
          v-for="cat in displayCategories"
          :key="cat.id"
          :to="`/kategorije/${cat.slug}`"
          class="group relative block aspect-[3/4] overflow-hidden bg-surface"
        >
          <img
            v-if="cat.image_path"
            :src="resolveImageUrl(cat.image_path)"
            :alt="cat.name"
            loading="lazy"
            class="absolute inset-0 m-auto max-w-[80%] max-h-[78%] object-contain transition-transform duration-[1000ms] ease-out-soft group-hover:scale-[1.04]"
          />
          <div v-else class="w-full h-full bg-ink-50" />
          <!-- Bottom-left title (no overlay gradient — proizvod je na beloj) -->
          <div class="absolute left-5 bottom-5 right-5">
            <p class="text-[16px] md:text-[18px] font-medium text-ink-800">{{ cat.name }}</p>
          </div>
        </NuxtLink>
      </div>
    </section>

    <!-- =============================================== -->
    <!-- 3. NAŠ IZBOR — 4 product grid sa names ispod (Vitra carousel-style) -->
    <!-- =============================================== -->
    <section v-if="!loading && editorialProducts.length" class="bg-paper py-24 lg:py-32">
      <div class="container-site">
        <div class="flex items-end justify-between mb-12">
          <h2 class="text-[28px] md:text-[36px] font-normal text-ink-800 tracking-[-0.01em] max-w-2xl leading-[1.1]">
            Naš izbor ovog meseca
          </h2>
          <NuxtLink to="/proizvodi?featured=1" class="hidden md:inline-flex items-center gap-2 text-[14px] text-ink-700 hover:text-terra-600 transition-colors">
            Svi izabrani <Icon name="lucide:arrow-right" class="w-3.5 h-3.5" />
          </NuxtLink>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-8">
          <ProductCard
            v-for="product in editorialProducts"
            :key="product.id"
            :product="product"
            @quick-view="openQuickView"
            @notify-me="openNotifyMe"
          />
        </div>
      </div>
    </section>

    <section v-if="loading" class="bg-paper py-24 lg:py-32">
      <div class="container-site">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-8">
          <UiAtomsSkeleton v-for="i in 4" :key="i" height="380px" />
        </div>
      </div>
    </section>

    <!-- =============================================== -->
    <!-- 4. EDITORIAL MOSAIC — 2-col, title + subtitle ispod (Vitra Scout Work / Accessories style) -->
    <!-- =============================================== -->
    <section class="bg-paper pb-24 lg:pb-32">
      <div class="container-site">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
          <NuxtLink to="/blog" class="group block">
            <div class="aspect-[16/10] overflow-hidden bg-ink-50 mb-5">
              <div class="w-full h-full bg-ply-edge transition-transform duration-700 group-hover:scale-[1.02]" />
            </div>
            <h3 class="text-[22px] md:text-[26px] font-normal text-ink-800 tracking-[-0.01em]">Magazin</h3>
            <p class="text-[15px] text-ink-500 mt-1">Priče o materijalu, dizajnu i ljudima.</p>
          </NuxtLink>

          <NuxtLink to="/proizvodi?sort=created_at" class="group block">
            <div class="aspect-[16/10] overflow-hidden bg-ink-50 mb-5">
              <img
                v-if="editorialPair[0] && primaryImage(editorialPair[0])"
                :src="primaryImage(editorialPair[0])!"
                :alt="editorialPair[0].name"
                loading="lazy"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.02]"
              />
              <div v-else class="w-full h-full bg-placeholder" />
            </div>
            <h3 class="text-[22px] md:text-[26px] font-normal text-ink-800 tracking-[-0.01em]">Novo u kolekciji</h3>
            <p class="text-[15px] text-ink-500 mt-1">Pogledajte ono što tek stiže.</p>
          </NuxtLink>

          <NuxtLink to="/izgled" class="group block">
            <div class="aspect-[16/10] overflow-hidden bg-ink-50 mb-5">
              <img
                v-if="editorialPair[1] && primaryImage(editorialPair[1])"
                :src="primaryImage(editorialPair[1])!"
                :alt="editorialPair[1].name"
                loading="lazy"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.02]"
              />
              <div v-else class="w-full h-full bg-placeholder" />
            </div>
            <h3 class="text-[22px] md:text-[26px] font-normal text-ink-800 tracking-[-0.01em]">Inspiracija</h3>
            <p class="text-[15px] text-ink-500 mt-1">Pogledajte komade u stvarnim domovima.</p>
          </NuxtLink>

          <NuxtLink to="/prodavnice" class="group block">
            <div class="aspect-[16/10] overflow-hidden bg-ink-50 mb-5">
              <div class="w-full h-full bg-placeholder" />
            </div>
            <h3 class="text-[22px] md:text-[26px] font-normal text-terra-600 tracking-[-0.01em] group-hover:text-terra-700 transition-colors">Naša radionica</h3>
            <p class="text-[15px] text-ink-500 mt-1">Posetite nas i pogledajte izradu.</p>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- =============================================== -->
    <!-- 5. STORY — minimalistic, beli prostor, Vitra-restraint -->
    <!-- =============================================== -->
    <section class="bg-surface">
      <div class="container-site py-24 lg:py-32 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
        <div class="lg:col-span-5">
          <h2 class="text-[36px] md:text-[48px] font-light leading-[1.05] text-ink-800 tracking-[-0.02em]">
            Slojevi se ne kriju.<br>
            Oni se <span class="italic">slave.</span>
          </h2>
          <p class="mt-6 text-[15px] text-ink-500 leading-[1.7] max-w-md">
            Šperploča je slojevita. Brend, sajt i proizvod sve to reflektuju — od macro pogleda na ivicu do načina kako kategorija vodi do proizvoda.
          </p>
          <NuxtLink to="/blog" class="mt-8 inline-flex items-center gap-2 text-[14px] text-terra-600 hover:text-terra-700 transition-colors">
            Pročitajte priču <Icon name="lucide:arrow-right" class="w-3.5 h-3.5" />
          </NuxtLink>
        </div>

        <div class="lg:col-span-7 aspect-[5/4] bg-ply-edge" />
      </div>
    </section>

    <!-- =============================================== -->
    <!-- 6. RECENTLY VIEWED -->
    <!-- =============================================== -->
    <section v-if="recentItems.length > 0" class="bg-paper py-20 lg:py-24">
      <div class="container-site">
        <ProductCarousel
          title="Nedavno gledali ste"
          :products="recentItems"
          @quick-view="openQuickView"
        />
      </div>
    </section>

    <ProductQuickView v-model="quickViewOpen" :product="quickViewProduct" />
    <ProductNotifyMeModal v-model="notifyMeOpen" :product="notifyMeProduct" />
  </div>
</template>
