<script setup lang="ts">
import type { Category } from '~/types'
// resolveImageUrl se koristi golo u template-u; eksplicitan import jer vue-tsc ne
// aplicira auto-import na nasleđene base komponente kad ih klijent layer build-uje.
import { resolveImageUrl } from '../../utils/image'

const { get } = useApi()
const { count } = useCart()
const wishlistStore = useWishlistStore()
const wishlistCount = computed(() => wishlistStore.count)

const { isFeatureEnabled, getValue } = useFeature()
const wishlistEnabled = computed(() => isFeatureEnabled(FEATURES.wishlist))
const { isLoggedIn } = useAuth()

const categories = ref<Category[]>([])
const cartDrawerOpen = ref(false)
const searchBarRef = ref<{ open: () => void } | null>(null)
const megaMenuOpen = ref<number | null>(null)
const mobileMenuOpen = ref(false)
const mobileExpandedCat = ref<number | null>(null)

async function fetchCategories() {
  try {
    const data = await get<{ data: Category[] }>('/v1/categories')
    categories.value = data.data
  }
  catch { /* silent */ }
}

function toggleMobileCat(id: number) {
  mobileExpandedCat.value = mobileExpandedCat.value === id ? null : id
}

onMounted(fetchCategories)
</script>

<template>
  <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Top row: logo + search + icons -->
      <div class="flex items-center justify-between h-16 gap-4">
        <!-- Mobile hamburger -->
        <button class="lg:hidden p-2 text-gray-600" @click="mobileMenuOpen = true">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
        </button>

        <!-- Logo -->
        <NuxtLink to="/" class="text-2xl font-bold text-dark flex-shrink-0">
          {{ getValue('site_name', 'eLokal') }}
        </NuxtLink>

        <!-- Search bar (desktop) -->
        <div class="hidden lg:block flex-1 max-w-xl mx-8">
          <UiMoleculesSearchBar ref="searchBarRef" />
        </div>

        <!-- Icons -->
        <div class="flex items-center gap-2">
          <!-- Search (mobile) -->
          <button class="lg:hidden p-2 text-gray-600" @click="searchBarRef?.open()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
          </button>

          <!-- Account -->
          <NuxtLink :to="isLoggedIn ? '/nalog' : '/nalog/login'" class="p-2 text-gray-600 hover:text-primary-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
          </NuxtLink>

          <!-- Wishlist -->
          <NuxtLink v-if="wishlistEnabled" to="/nalog/wishlist" class="relative p-2 text-gray-600 hover:text-red-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
            <span v-if="wishlistCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-bold flex items-center justify-center rounded-full">
              {{ wishlistCount }}
            </span>
          </NuxtLink>

          <!-- Cart -->
          <button class="relative p-2 text-gray-600 hover:text-primary-600" @click="cartDrawerOpen = true">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            <span v-if="count > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-[10px] font-bold flex items-center justify-center rounded-full">
              {{ count }}
            </span>
          </button>
        </div>
      </div>

      <!-- Desktop category nav + mega menu -->
      <nav class="hidden lg:flex items-center gap-0 border-t border-gray-100 -mx-4 px-4">
        <div
          v-for="cat in categories"
          :key="cat.id"
          class="relative"
          @mouseenter="megaMenuOpen = cat.id"
          @mouseleave="megaMenuOpen = null"
        >
          <NuxtLink
            :to="`/kategorije/${cat.slug}`"
            class="block px-4 py-3 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors"
          >
            {{ cat.name }}
          </NuxtLink>

          <!-- Mega menu dropdown -->
          <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div
              v-if="megaMenuOpen === cat.id && cat.children?.length"
              class="absolute left-0 top-full bg-white border border-gray-200 shadow-xl py-6 px-8 min-w-[500px] z-50"
            >
              <div class="grid grid-cols-3 gap-6">
                <!-- Subcategory links -->
                <div class="col-span-2">
                  <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">{{ cat.name }}</p>
                  <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                    <NuxtLink
                      v-for="child in cat.children"
                      :key="child.id"
                      :to="`/kategorije/${child.slug}`"
                      class="flex items-center gap-2 text-sm text-gray-600 hover:text-primary-600 py-1.5"
                      @click="megaMenuOpen = null"
                    >
                      <img
                        v-if="child.image_path"
                        :src="resolveImageUrl(child.image_path)"
                        :alt="child.name"
                        class="w-8 h-8 object-cover rounded-sm flex-shrink-0"
                      />
                      {{ child.name }}
                    </NuxtLink>
                  </div>
                  <NuxtLink
                    :to="`/kategorije/${cat.slug}`"
                    class="inline-block mt-3 text-sm font-medium text-primary-600 hover:underline"
                    @click="megaMenuOpen = null"
                  >
                    Pogledaj sve →
                  </NuxtLink>
                </div>

                <!-- Promo banner slot -->
                <div class="col-span-1 bg-primary-50 p-4 flex flex-col justify-center items-center text-center">
                  <p class="text-sm font-bold text-gray-800 mb-1">Novo u ponudi</p>
                  <p class="text-xs text-gray-500 mb-3">Pogledajte najnovije proizvode</p>
                  <NuxtLink
                    :to="`/kategorije/${cat.slug}`"
                    class="text-xs font-medium text-primary-600 hover:underline"
                    @click="megaMenuOpen = null"
                  >
                    Istraži →
                  </NuxtLink>
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </nav>
    </div>
  </header>

  <!-- Mobile drawer menu -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="mobileMenuOpen" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/50" @click="mobileMenuOpen = false" />
        <div class="absolute left-0 top-0 bottom-0 w-80 max-w-[85vw] bg-white shadow-xl flex flex-col overflow-y-auto">
          <!-- Header -->
          <div class="flex items-center justify-between px-4 py-4 border-b">
            <span class="text-lg font-bold text-dark">{{ getValue('site_name', 'eLokal') }}</span>
            <button class="p-1 text-gray-400 hover:text-gray-600" @click="mobileMenuOpen = false">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Categories accordion -->
          <div class="flex-1 py-2">
            <div v-for="cat in categories" :key="cat.id">
              <div class="flex items-center justify-between">
                <NuxtLink
                  :to="`/kategorije/${cat.slug}`"
                  class="flex-1 px-4 py-3 text-sm font-medium text-gray-800"
                  @click="mobileMenuOpen = false"
                >
                  {{ cat.name }}
                </NuxtLink>
                <button
                  v-if="cat.children?.length"
                  class="px-4 py-3 text-gray-400"
                  @click="toggleMobileCat(cat.id)"
                >
                  <svg
                    class="w-4 h-4 transition-transform"
                    :class="mobileExpandedCat === cat.id ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                  </svg>
                </button>
              </div>
              <div v-if="mobileExpandedCat === cat.id && cat.children?.length" class="bg-gray-50 pb-2">
                <NuxtLink
                  v-for="child in cat.children"
                  :key="child.id"
                  :to="`/kategorije/${child.slug}`"
                  class="block px-8 py-2 text-sm text-gray-600 hover:text-primary-600"
                  @click="mobileMenuOpen = false"
                >
                  {{ child.name }}
                </NuxtLink>
              </div>
            </div>
          </div>

          <!-- Footer links -->
          <div class="border-t px-4 py-4 space-y-2">
            <NuxtLink to="/nalog" class="block text-sm text-gray-600 hover:text-primary-600" @click="mobileMenuOpen = false">
              Moj nalog
            </NuxtLink>
            <NuxtLink v-if="wishlistEnabled" to="/nalog/wishlist" class="block text-sm text-gray-600 hover:text-primary-600" @click="mobileMenuOpen = false">
              Lista želja
            </NuxtLink>
            <NuxtLink to="/nalog/orders" class="block text-sm text-gray-600 hover:text-primary-600" @click="mobileMenuOpen = false">
              Narudžbine
            </NuxtLink>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <CartDrawer v-model="cartDrawerOpen" />
</template>
