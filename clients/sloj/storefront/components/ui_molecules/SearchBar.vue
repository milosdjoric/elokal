<script setup lang="ts">
import type { Product } from '~/types'

const { get } = useApi()

const isOpen = ref(false)
const query = ref('')
const results = ref<Product[]>([])
const matchingCategories = ref<{ id: number; name: string; slug: string }[]>([])
const loading = ref(false)
const trendingSearches = ref<string[]>([])
const selectedIndex = ref(-1)
const inputRef = ref<HTMLInputElement | null>(null)
let debounceTimer: ReturnType<typeof setTimeout>

// Expose open za parent komponentu
defineExpose({ open: () => { isOpen.value = true } })

async function fetchTrending() {
  try {
    const data = await get<{ data: Record<string, Record<string, string>> }>('/v1/settings')
    const flat: Record<string, string> = {}
    for (const group of Object.values(data.data)) Object.assign(flat, group)
    if (flat.trending_searches) {
      trendingSearches.value = flat.trending_searches.split(',').map(s => s.trim()).filter(Boolean)
    }
  }
  catch { /* silent */ }
}

async function search() {
  if (query.value.length < 2) {
    results.value = []
    matchingCategories.value = []
    return
  }
  loading.value = true
  selectedIndex.value = -1
  try {
    const data = await get<{ data: Product[]; matching_categories?: { id: number; name: string; slug: string }[] }>('/v1/search', { q: query.value, per_page: 6 })
    results.value = data.data
    matchingCategories.value = data.matching_categories || []
  }
  catch { results.value = [] }
  finally { loading.value = false }
}

function onInput() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(search, 250)
}

function open() {
  isOpen.value = true
  nextTick(() => inputRef.value?.focus())
}

function close() {
  isOpen.value = false
  query.value = ''
  results.value = []
  matchingCategories.value = []
  selectedIndex.value = -1
}

function goToResults() {
  if (!query.value.trim()) return
  close()
  navigateTo({ path: '/pretraga', query: { q: query.value } })
}

function selectProduct(slug: string) {
  close()
  navigateTo(`/proizvodi/${slug}`)
}

function selectCategory(slug: string) {
  close()
  navigateTo(`/kategorije/${slug}`)
}

function useTrending(term: string) {
  query.value = term
  search()
}

function primaryImage(product: Product): string | null {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
}

// Navigacija tastaturom
const totalItems = computed(() => matchingCategories.value.length + results.value.length)

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') { close(); return }
  if (e.key === 'Enter') {
    if (selectedIndex.value >= 0) {
      const catLen = matchingCategories.value.length
      if (selectedIndex.value < catLen) {
        selectCategory(matchingCategories.value[selectedIndex.value].slug)
      } else {
        const prodIdx = selectedIndex.value - catLen
        if (results.value[prodIdx]) selectProduct(results.value[prodIdx].slug)
      }
    } else {
      goToResults()
    }
    return
  }
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    selectedIndex.value = Math.min(selectedIndex.value + 1, totalItems.value - 1)
  }
  if (e.key === 'ArrowUp') {
    e.preventDefault()
    selectedIndex.value = Math.max(selectedIndex.value - 1, -1)
  }
}

// Ctrl+K / Cmd+K global shortcut
function onGlobalKeydown(e: KeyboardEvent) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    open()
  }
}

onMounted(() => {
  document.addEventListener('keydown', onGlobalKeydown)
  fetchTrending()
})

onUnmounted(() => {
  document.removeEventListener('keydown', onGlobalKeydown)
})
</script>

<template>
  <!-- Trigger button — vizuelno deo header-a (bela pozadina, hairline border, full-width) -->
  <button
    class="flex items-center gap-3 w-full px-6 lg:px-10 py-3 text-[14px] text-ink-400 bg-paper border-b border-ink-100 hover:text-ink-700 transition-colors"
    @click="open"
  >
    <Icon name="lucide:search" class="w-4 h-4" />
    <span class="flex-1 text-left">Pretraži proizvode...</span>
    <kbd class="hidden md:inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[11px] font-mono text-ink-400 bg-paper border border-ink-100">
      <span class="text-xs">⌘</span>K
    </kbd>
  </button>

  <!-- Modal overlay -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-150"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-100"
      leave-to-class="opacity-0"
    >
      <div v-if="isOpen" class="fixed inset-0 z-[60] flex items-start justify-center pt-[15vh] px-4" @click.self="close">
        <div class="absolute inset-0 bg-black/50" @click="close" />

        <!-- Search panel -->
        <div class="relative bg-white shadow-2xl w-full max-w-xl overflow-hidden">
          <!-- Input -->
          <div class="flex items-center gap-3 px-4 border-b border-gray-200">
            <Icon name="lucide:search" class="w-5 h-5 text-gray-400 flex-shrink-0" />
            <input
              ref="inputRef"
              v-model="query"
              type="text"
              placeholder="Pretraži proizvode, kategorije..."
              class="flex-1 py-4 text-base bg-transparent outline-none placeholder:text-gray-400"
              @input="onInput"
              @keydown="onKeydown"
            />
            <button v-if="query" class="text-gray-400 hover:text-gray-600" aria-label="Obriši" @click="query = ''; results = []; matchingCategories = []">
              <Icon name="lucide:x" class="w-4 h-4" />
            </button>
            <kbd class="hidden md:inline-flex px-1.5 py-0.5 text-[11px] font-mono text-gray-400 bg-gray-100 border border-gray-200 rounded">ESC</kbd>
          </div>

          <!-- Results -->
          <div class="max-h-[50vh] overflow-y-auto">
            <!-- Empty state: trending -->
            <div v-if="query.length < 2 && trendingSearches.length > 0" class="p-4">
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Popularno</p>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="term in trendingSearches"
                  :key="term"
                  class="text-sm px-3 py-1.5 bg-gray-100 text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                  @click="useTrending(term)"
                >
                  {{ term }}
                </button>
              </div>
            </div>

            <!-- Loading -->
            <div v-else-if="loading" class="p-8 text-center">
              <UiAtomsSpinner size="sm" />
            </div>

            <!-- No results -->
            <div v-else-if="query.length >= 2 && results.length === 0 && matchingCategories.length === 0" class="p-8 text-center">
              <p class="text-gray-500">Nema rezultata za "<span class="font-medium text-gray-700">{{ query }}</span>"</p>
              <p class="text-sm text-gray-400 mt-1">Pokušajte sa drugim pojmom.</p>
            </div>

            <!-- Results -->
            <div v-else-if="query.length >= 2">
              <!-- Categories -->
              <div v-if="matchingCategories.length" class="px-4 pt-3 pb-2">
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Kategorije</p>
                <button
                  v-for="(cat, i) in matchingCategories"
                  :key="cat.id"
                  class="flex items-center gap-2 w-full px-3 py-2 text-sm text-left transition-colors"
                  :class="selectedIndex === i ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50'"
                  @click="selectCategory(cat.slug)"
                  @mouseenter="selectedIndex = i"
                >
                  <Icon name="lucide:folder" class="w-4 h-4 text-gray-400" />
                  {{ cat.name }}
                </button>
              </div>

              <!-- Products -->
              <div v-if="results.length">
                <p class="px-4 pt-3 pb-1 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Proizvodi</p>
                <button
                  v-for="(product, i) in results"
                  :key="product.id"
                  class="w-full flex items-center gap-3 px-4 py-2.5 text-left transition-colors"
                  :class="selectedIndex === (matchingCategories.length + i) ? 'bg-primary-50' : 'hover:bg-gray-50'"
                  @click="selectProduct(product.slug)"
                  @mouseenter="selectedIndex = matchingCategories.length + i"
                >
                  <img
                    v-if="primaryImage(product)"
                    :src="primaryImage(product)!"
                    class="w-10 h-10 object-cover flex-shrink-0"
                    alt=""
                  />
                  <div class="w-10 h-10 bg-gray-100 flex-shrink-0" v-else />
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ product.name }}</p>
                    <p class="text-xs text-primary-600 font-semibold tabular-nums">{{ formatPrice(product.effective_price) }}</p>
                  </div>
                  <Icon name="lucide:chevron-right" class="w-4 h-4 text-gray-300 flex-shrink-0" />
                </button>
              </div>

              <!-- View all -->
              <button
                class="w-full px-4 py-3 text-sm text-primary-600 font-medium text-center border-t border-gray-100 hover:bg-gray-50 transition-colors"
                @click="goToResults"
              >
                Prikaži sve rezultate za "{{ query }}" →
              </button>
            </div>
          </div>

          <!-- Footer hint -->
          <div class="hidden md:flex items-center gap-4 px-4 py-2 bg-gray-50 border-t border-gray-100 text-[11px] text-gray-400">
            <span><kbd class="px-1 py-0.5 bg-white border rounded">↑↓</kbd> navigacija</span>
            <span><kbd class="px-1 py-0.5 bg-white border rounded">Enter</kbd> otvori</span>
            <span><kbd class="px-1 py-0.5 bg-white border rounded">ESC</kbd> zatvori</span>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
