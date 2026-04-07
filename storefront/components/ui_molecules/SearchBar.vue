<script setup lang="ts">
import type { Product } from '~/types'

const { get } = useApi()

const query = ref('')
const results = ref<Product[]>([])
const showDropdown = ref(false)
const loading = ref(false)
let debounceTimer: ReturnType<typeof setTimeout>

async function search() {
  if (query.value.length < 2) {
    results.value = []
    showDropdown.value = false
    return
  }

  loading.value = true
  try {
    const data = await get<{ data: Product[] }>('/v1/search', { q: query.value, per_page: 5 })
    results.value = data.data
    showDropdown.value = true
  }
  catch { results.value = [] }
  finally { loading.value = false }
}

function onInput() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(search, 300)
}

function handleSubmit() {
  if (query.value.trim()) {
    showDropdown.value = false
    navigateTo({ path: '/search', query: { q: query.value } })
  }
}

function selectProduct(slug: string) {
  showDropdown.value = false
  query.value = ''
  navigateTo(`/products/${slug}`)
}

function primaryImage(product: Product): string | null {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
}

function close() { showDropdown.value = false }
function delayedClose() { setTimeout(close, 200) }
</script>

<template>
  <div class="relative" @focusout="delayedClose">
    <form @submit.prevent="handleSubmit" class="relative">
      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
      </svg>
      <input
        v-model="query"
        type="text"
        placeholder="Pretraži proizvode..."
        class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
        @input="onInput"
        @focus="query.length >= 2 && (showDropdown = true)"
      />
    </form>

    <!-- Dropdown -->
    <div v-if="showDropdown" class="absolute left-0 right-0 top-full bg-white border border-gray-200 shadow-lg z-50 max-h-80 overflow-y-auto">
      <div v-if="loading" class="p-4 text-center">
        <UiAtomsSpinner size="sm" />
      </div>
      <div v-else-if="results.length === 0" class="p-4 text-sm text-gray-500 text-center">
        Nema rezultata za "{{ query }}"
      </div>
      <div v-else>
        <button
          v-for="product in results"
          :key="product.id"
          class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 text-left"
          @click="selectProduct(product.slug)"
        >
          <img
            v-if="primaryImage(product)"
            :src="primaryImage(product)!"
            class="w-10 h-10 object-cover flex-shrink-0"
            alt=""
          />
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-800 truncate">{{ product.name }}</p>
            <p class="text-xs text-primary-600 font-semibold">{{ Number(product.effective_price).toLocaleString('sr-RS') }} RSD</p>
          </div>
        </button>
        <NuxtLink
          :to="{ path: '/search', query: { q: query } }"
          class="block px-4 py-2 text-sm text-primary-600 font-medium text-center border-t hover:bg-gray-50"
          @click="showDropdown = false"
        >
          Prikaži sve rezultate →
        </NuxtLink>
      </div>
    </div>
  </div>
</template>
