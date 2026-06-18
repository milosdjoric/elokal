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
  <!-- Trigger button — čist search bar, deo header-a (bez tehničkog ⌘K hinta) -->
  <button
    class="flex items-center gap-3 w-full px-6 lg:px-10 py-3 text-[14px] text-ink-400 bg-paper border-b border-ink-100 hover:text-ink-700 transition-colors"
    @click="open"
  >
    <Icon name="lucide:search" class="w-4 h-4" />
    <span class="flex-1 text-left">Pretražite nameštaj, kategorije…</span>
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
        <div class="absolute inset-0 bg-ink-900/40 backdrop-blur-sm" @click="close" />

        <!-- Search panel -->
        <div class="relative bg-paper shadow-2xl w-full max-w-xl overflow-hidden">
          <!-- Input -->
          <div class="flex items-center gap-3 px-5 border-b border-ink-100">
            <Icon name="lucide:search" class="w-5 h-5 text-ink-400 flex-shrink-0" />
            <input
              ref="inputRef"
              v-model="query"
              type="text"
              placeholder="Pretražite nameštaj, kategorije…"
              class="flex-1 py-5 text-base text-ink-800 bg-transparent outline-none placeholder:text-ink-400"
              @input="onInput"
              @keydown="onKeydown"
            />
            <button v-if="query" class="text-ink-400 hover:text-ink-800 transition-colors p-1 -mr-1" aria-label="Obriši" @click="query = ''; results = []; matchingCategories = []">
              <Icon name="lucide:x" class="w-5 h-5" />
            </button>
          </div>

          <!-- Results -->
          <div class="max-h-[55vh] overflow-y-auto">
            <!-- Empty state: trending -->
            <div v-if="query.length < 2 && trendingSearches.length > 0" class="p-5">
              <p class="text-[11px] font-medium text-ink-400 uppercase tracking-[0.12em] mb-3">Popularno traženo</p>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="term in trendingSearches"
                  :key="term"
                  class="text-sm px-3.5 py-1.5 border border-ink-200 text-ink-700 hover:border-terra-400 hover:text-terra-700 transition-colors"
                  @click="useTrending(term)"
                >
                  {{ term }}
                </button>
              </div>
            </div>

            <!-- Loading -->
            <div v-else-if="loading" class="p-10 text-center">
              <UiAtomsSpinner size="sm" />
            </div>

            <!-- No results -->
            <div v-else-if="query.length >= 2 && results.length === 0 && matchingCategories.length === 0" class="p-10 text-center">
              <p class="text-ink-600">Ništa nismo našli za „<span class="font-medium text-ink-800">{{ query }}</span>"</p>
              <p class="text-sm text-ink-400 mt-1">Probajte sa drugim pojmom.</p>
            </div>

            <!-- Results -->
            <div v-else-if="query.length >= 2">
              <!-- Categories -->
              <div v-if="matchingCategories.length" class="px-3 pt-4 pb-1">
                <p class="px-2 text-[11px] font-medium text-ink-400 uppercase tracking-[0.12em] mb-2">Kategorije</p>
                <button
                  v-for="(cat, i) in matchingCategories"
                  :key="cat.id"
                  class="flex items-center gap-3 w-full px-2 py-2.5 text-sm text-left transition-colors"
                  :class="selectedIndex === i ? 'bg-ply-50 text-ink-900' : 'text-ink-700 hover:bg-paper-soft'"
                  @click="selectCategory(cat.slug)"
                  @mouseenter="selectedIndex = i"
                >
                  <Icon name="lucide:folder" class="w-4 h-4 text-ink-400" />
                  {{ cat.name }}
                </button>
              </div>

              <!-- Products -->
              <div v-if="results.length" class="px-3 pb-1">
                <p class="px-2 pt-3 pb-1 text-[11px] font-medium text-ink-400 uppercase tracking-[0.12em]">Proizvodi</p>
                <button
                  v-for="(product, i) in results"
                  :key="product.id"
                  class="w-full flex items-center gap-3 px-2 py-2.5 text-left transition-colors"
                  :class="selectedIndex === (matchingCategories.length + i) ? 'bg-ply-50' : 'hover:bg-paper-soft'"
                  @click="selectProduct(product.slug)"
                  @mouseenter="selectedIndex = matchingCategories.length + i"
                >
                  <img
                    v-if="primaryImage(product)"
                    :src="primaryImage(product)!"
                    class="w-12 h-12 object-cover flex-shrink-0 bg-ply-50"
                    alt=""
                  />
                  <div class="w-12 h-12 bg-ply-100 flex-shrink-0" v-else />
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-ink-800 truncate">{{ product.name }}</p>
                    <p class="text-sm text-terra-600 tabular-nums">{{ formatPrice(product.effective_price) }}</p>
                  </div>
                  <Icon name="lucide:arrow-right" class="w-4 h-4 text-ink-300 flex-shrink-0" />
                </button>
              </div>

              <!-- View all -->
              <button
                class="w-full px-4 py-4 text-sm text-terra-600 hover:text-terra-700 font-medium text-center border-t border-ink-100 hover:bg-paper-soft transition-colors"
                @click="goToResults"
              >
                Pogledajte sve rezultate za „{{ query }}"
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
