<script setup lang="ts">
import type { Category } from '~/types'

const { get } = useApi()
const { count } = useCart()
const cartStore = useCartStore()
const router = useRouter()
const wishlistStore = useWishlistStore()
const wishlistCount = computed(() => wishlistStore.count)
const { isLoggedIn } = useAuth()
const { isEnabled } = useFeature()
const wishlistEnabled = computed(() => isEnabled('feature_wishlist', true))

// Announcement bar (Pangaia-style) — uredi tekst/link po potrebi
// (kasnije se može povući iz /v1/settings umesto hardkodovanog teksta)
const announcement = {
  text: 'Ručno rađen nameštaj od šperploče — besplatna dostava širom Srbije.',
  linkText: 'Pogledaj kolekciju',
  to: '/proizvodi',
}

const categories = ref<Category[]>([])
const searchBarRef = ref<{ open: () => void } | null>(null)
const megaMenuOpen = ref<number | null>(null)
const mobileMenuOpen = ref(false)
const mobileExpandedCat = ref<number | null>(null)

let openTimer: ReturnType<typeof setTimeout> | null = null
let closeTimer: ReturnType<typeof setTimeout> | null = null

const activeCategory = computed(() =>
  megaMenuOpen.value !== null ? categories.value.find(c => c.id === megaMenuOpen.value) : null,
)

async function fetchCategories() {
  try {
    const data = await get<{ data: Category[] }>('/v1/categories')
    categories.value = data.data
  }
  catch { /* silent */ }
}

function scheduleOpen(catId: number) {
  if (closeTimer) { clearTimeout(closeTimer); closeTimer = null }
  if (megaMenuOpen.value === catId) return
  if (openTimer) clearTimeout(openTimer)
  openTimer = setTimeout(() => { megaMenuOpen.value = catId }, 100)
}

function scheduleClose() {
  if (openTimer) { clearTimeout(openTimer); openTimer = null }
  if (closeTimer) clearTimeout(closeTimer)
  closeTimer = setTimeout(() => { megaMenuOpen.value = null }, 150)
}

function closeNow() {
  if (openTimer) { clearTimeout(openTimer); openTimer = null }
  if (closeTimer) { clearTimeout(closeTimer); closeTimer = null }
  megaMenuOpen.value = null
}

function cancelClose() {
  if (closeTimer) { clearTimeout(closeTimer); closeTimer = null }
}

function goToCategory(slug: string) {
  router.push(`/kategorije/${slug}`)
  closeNow()
}

function onKey(e: KeyboardEvent) {
  if (e.key === 'Escape' && megaMenuOpen.value !== null) closeNow()
}

function toggleMobileCat(id: number) {
  mobileExpandedCat.value = mobileExpandedCat.value === id ? null : id
}

onMounted(() => {
  fetchCategories()
  document.addEventListener('keydown', onKey)
})
onUnmounted(() => {
  document.removeEventListener('keydown', onKey)
  if (openTimer) clearTimeout(openTimer)
  if (closeTimer) clearTimeout(closeTimer)
})
</script>

<template>
  <!-- Announcement bar — Pangaia style: crni, centriran, podvučen link (scroll-uje sa stranicom) -->
  <div class="bg-ink-900 text-paper">
    <div class="px-6 lg:px-10 min-h-9 py-2 flex items-center justify-center text-center">
      <p class="text-[12px] tracking-[0.04em]">
        {{ announcement.text }}
        <NuxtLink
          v-if="announcement.linkText"
          :to="announcement.to"
          class="underline underline-offset-2 decoration-paper/60 hover:decoration-paper ml-1 transition-colors"
        >
          {{ announcement.linkText }}
        </NuxtLink>
      </p>
    </div>
  </div>

  <header class="bg-paper sticky top-0 z-40">
    <!-- Main row: nav (levo) · logo (centar) · ikone (desno) — Pangaia layout, full-bleed -->
    <div class="border-b border-ink-100">
      <div class="px-6 lg:px-10">
        <div class="grid grid-cols-[1fr_auto_1fr] items-center h-14 gap-4">
          <!-- LEVO: desktop nav / mobilni hamburger -->
          <div class="flex items-center justify-start min-w-0">
            <button
              class="min-[1440px]:hidden p-2 -ml-2 text-ink-800 hover:text-ink-900 transition-colors"
              aria-label="Otvori meni"
              @click="mobileMenuOpen = true"
            >
              <Icon name="lucide:menu" class="w-5 h-5" />
            </button>

            <nav class="hidden min-[1440px]:flex items-center gap-5 flex-nowrap" aria-label="Glavna navigacija">
              <button
                v-for="cat in categories"
                :key="cat.id"
                type="button"
                class="nav-link py-2 uppercase text-[12px] whitespace-nowrap"
                :class="megaMenuOpen === cat.id ? 'is-active' : ''"
                :aria-expanded="megaMenuOpen === cat.id"
                @mouseenter="scheduleOpen(cat.id)"
                @mouseleave="scheduleClose()"
                @focus="scheduleOpen(cat.id)"
                @click="goToCategory(cat.slug)"
              >
                {{ cat.name }}
              </button>
              <NuxtLink to="/blog" class="nav-link py-2 uppercase text-[12px] whitespace-nowrap">
                Magazin
              </NuxtLink>
              <NuxtLink to="/izgled" class="nav-link py-2 uppercase text-[12px] whitespace-nowrap">
                Inspiracija
              </NuxtLink>
            </nav>
          </div>

          <!-- CENTAR: brend wordmark -->
          <NuxtLink to="/" class="justify-self-center flex-shrink-0 py-2" aria-label="sloj kolektiv — naslovna">
            <UiAtomsBrandMark size="md" :with-descriptor="false" />
          </NuxtLink>

          <!-- DESNO: ikone -->
          <div class="flex items-center justify-end gap-0.5 sm:gap-1">
            <button
              class="p-2.5 text-ink-700 hover:text-ink-900 transition-colors"
              aria-label="Pretraga"
              @click="searchBarRef?.open()"
            >
              <Icon name="lucide:search" class="w-[18px] h-[18px]" />
            </button>

            <NuxtLink
              :to="isLoggedIn ? '/nalog' : '/nalog/login'"
              class="hidden sm:inline-flex p-2.5 text-ink-700 hover:text-ink-900 transition-colors"
              aria-label="Moj nalog"
            >
              <Icon name="lucide:user" class="w-[18px] h-[18px]" />
            </NuxtLink>

            <NuxtLink
              v-if="wishlistEnabled"
              to="/nalog/wishlist"
              class="relative hidden sm:inline-flex p-2.5 text-ink-700 hover:text-ink-900 transition-colors"
              aria-label="Lista želja"
            >
              <Icon name="lucide:heart" class="w-[18px] h-[18px]" />
              <span
                v-if="wishlistCount > 0"
                class="absolute top-1 right-1 min-w-[16px] h-[16px] px-1 bg-terra-500 text-paper text-[10px] font-medium flex items-center justify-center tabular-nums"
              >
                {{ wishlistCount > 99 ? '99+' : wishlistCount }}
              </span>
            </NuxtLink>

            <button
              class="relative p-2.5 text-ink-700 hover:text-ink-900 transition-colors"
              aria-label="Korpa"
              @click="cartStore.openDrawer()"
            >
              <Icon name="lucide:shopping-bag" class="w-[18px] h-[18px]" />
              <span
                v-if="count > 0"
                class="absolute top-1 right-1 min-w-[16px] h-[16px] px-1 bg-ink-800 text-paper text-[10px] font-medium flex items-center justify-center tabular-nums"
              >
                {{ count > 99 ? '99+' : count }}
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Search bar (modal trigger) -->
    <UiMoleculesSearchBar ref="searchBarRef" />

    <!-- Edge-to-edge mega menu dropdown — minimalistic, no featured products -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="activeCategory && activeCategory.children?.length"
        class="hidden min-[1440px]:block absolute left-0 right-0 top-full bg-paper border-b border-ink-100 z-50"
        @mouseenter="cancelClose()"
        @mouseleave="scheduleClose()"
      >
        <div class="container-site py-12 grid grid-cols-12 gap-8">
          <div class="col-span-3">
            <p class="text-[12px] tracking-[0.04em] text-ink-500 mb-5">{{ activeCategory.name }}</p>
            <NuxtLink
              :to="`/kategorije/${activeCategory.slug}`"
              class="text-terra-600 hover:text-terra-700 text-[15px] inline-flex items-center gap-1.5 transition-colors"
              @click="closeNow()"
            >
              Pogledaj sve <Icon name="lucide:arrow-right" class="w-3.5 h-3.5" />
            </NuxtLink>
          </div>
          <div class="col-span-9 grid grid-cols-3 gap-x-8 gap-y-1">
            <NuxtLink
              v-for="child in activeCategory.children"
              :key="child.id"
              :to="`/kategorije/${child.slug}`"
              class="block py-2 text-[15px] text-ink-800 hover:text-terra-600 transition-colors"
              @click="closeNow()"
            >
              {{ child.name }}
            </NuxtLink>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Backdrop when mega menu is open -->
    <Transition enter-active-class="transition duration-150" enter-from-class="opacity-0" leave-active-class="transition duration-100" leave-to-class="opacity-0">
      <div
        v-if="megaMenuOpen !== null && activeCategory?.children?.length"
        class="hidden min-[1440px]:block fixed inset-0 top-14 bg-ink-900/10 z-30"
        aria-hidden="true"
        @click="closeNow()"
      />
    </Transition>
  </header>

  <!-- Mobile drawer menu -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 min-[1440px]:hidden">
        <div class="absolute inset-0 bg-ink-900/40" @click="mobileMenuOpen = false" />
        <div class="absolute left-0 top-0 bottom-0 w-[85vw] max-w-sm bg-paper flex flex-col overflow-y-auto">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-5 border-b border-ink-100">
            <UiAtomsBrandMark size="sm" />
            <button class="p-1 text-ink-500 hover:text-ink-800" aria-label="Zatvori meni" @click="mobileMenuOpen = false">
              <Icon name="lucide:x" class="w-5 h-5" />
            </button>
          </div>

          <!-- Categories accordion -->
          <div class="flex-1 py-4">
            <div v-for="cat in categories" :key="cat.id" class="border-b border-ink-100 last:border-0">
              <div class="flex items-center justify-between">
                <NuxtLink
                  :to="`/kategorije/${cat.slug}`"
                  class="flex-1 px-6 py-4 text-[16px] text-ink-800"
                  @click="mobileMenuOpen = false"
                >
                  {{ cat.name }}
                </NuxtLink>
                <button
                  v-if="cat.children?.length"
                  class="px-6 py-4 text-ink-400"
                  :aria-label="mobileExpandedCat === cat.id ? 'Sakrij' : 'Prikaži'"
                  @click="toggleMobileCat(cat.id)"
                >
                  <Icon
                    name="lucide:plus"
                    class="w-4 h-4 transition-transform"
                    :class="mobileExpandedCat === cat.id ? 'rotate-45' : ''"
                  />
                </button>
              </div>
              <div v-if="mobileExpandedCat === cat.id && cat.children?.length" class="bg-ink-50 pb-3">
                <NuxtLink
                  v-for="child in cat.children"
                  :key="child.id"
                  :to="`/kategorije/${child.slug}`"
                  class="block px-10 py-2.5 text-[14px] text-ink-700"
                  @click="mobileMenuOpen = false"
                >
                  {{ child.name }}
                </NuxtLink>
              </div>
            </div>
          </div>

          <!-- Footer links -->
          <div class="border-t border-ink-100 px-6 py-5 space-y-3">
            <NuxtLink to="/nalog" class="block text-[14px] text-ink-700" @click="mobileMenuOpen = false">Moj nalog</NuxtLink>
            <NuxtLink v-if="wishlistEnabled" to="/nalog/wishlist" class="block text-[14px] text-ink-700" @click="mobileMenuOpen = false">Lista želja</NuxtLink>
            <NuxtLink to="/nalog/orders" class="block text-[14px] text-ink-700" @click="mobileMenuOpen = false">Narudžbine</NuxtLink>
            <NuxtLink to="/prodavnice" class="block text-[14px] text-ink-700" @click="mobileMenuOpen = false">Pronađi nas</NuxtLink>
            <NuxtLink to="/kontakt" class="block text-[14px] text-ink-700" @click="mobileMenuOpen = false">Kontakt</NuxtLink>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <CartDrawer />
</template>
