<script setup lang="ts">
import type { Category } from '~/types'

const { get } = useApi()
const { count } = useCart()

const categories = ref<Category[]>([])
const searchQuery = ref('')
const searchOpen = ref(false)
const megaMenuOpen = ref<number | null>(null)

async function fetchCategories() {
  try {
    const data = await get<{ data: Category[] }>('/v1/categories')
    categories.value = data.data
  }
  catch { /* silent */ }
}

function handleSearch() {
  if (searchQuery.value.trim()) {
    navigateTo({ path: '/search', query: { q: searchQuery.value } })
    searchQuery.value = ''
    searchOpen.value = false
  }
}

onMounted(fetchCategories)
</script>

<template>
  <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <NuxtLink to="/" class="text-2xl font-bold text-dark">
          eLokal
        </NuxtLink>

        <!-- Desktop nav -->
        <nav class="hidden lg:flex items-center gap-1">
          <div
            v-for="cat in categories"
            :key="cat.id"
            class="relative"
            @mouseenter="megaMenuOpen = cat.id"
            @mouseleave="megaMenuOpen = null"
          >
            <NuxtLink
              :to="`/categories/${cat.slug}`"
              class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors"
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
                class="absolute left-0 top-full bg-white border border-gray-200 shadow-lg py-4 px-6 min-w-[300px] z-50"
              >
                <div class="grid grid-cols-2 gap-4">
                  <NuxtLink
                    v-for="child in cat.children"
                    :key="child.id"
                    :to="`/categories/${child.slug}`"
                    class="text-sm text-gray-600 hover:text-primary-600 py-1"
                  >
                    {{ child.name }}
                  </NuxtLink>
                </div>
              </div>
            </Transition>
          </div>
        </nav>

        <!-- Right: search + cart -->
        <div class="flex items-center gap-4">
          <!-- Search -->
          <div class="relative">
            <button
              class="p-2 text-gray-600 hover:text-primary-600"
              @click="searchOpen = !searchOpen"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
              </svg>
            </button>

            <Transition
              enter-active-class="transition duration-150"
              enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition duration-100"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0 scale-95"
            >
              <form
                v-if="searchOpen"
                class="absolute right-0 top-full mt-2 w-72 z-50"
                @submit.prevent="handleSearch"
              >
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Pretraži proizvode..."
                  class="w-full px-4 py-2 text-sm border border-gray-300 shadow-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                  autofocus
                />
              </form>
            </Transition>
          </div>

          <!-- Cart -->
          <NuxtLink to="/cart" class="relative p-2 text-gray-600 hover:text-primary-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            <span
              v-if="count > 0"
              class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-[10px] font-bold flex items-center justify-center rounded-full"
            >
              {{ count }}
            </span>
          </NuxtLink>
        </div>
      </div>
    </div>
  </header>
</template>
