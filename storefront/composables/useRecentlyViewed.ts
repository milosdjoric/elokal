import type { Product } from '~/types'

const MAX_ITEMS = 12
const STORAGE_KEY = 'recently_viewed'

const items = ref<Product[]>([])
let hydrated = false

export function useRecentlyViewed() {
  function hydrate() {
    if (hydrated || !import.meta.client) return
    try {
      const saved = localStorage.getItem(STORAGE_KEY)
      if (saved) items.value = JSON.parse(saved)
    }
    catch { items.value = [] }
    hydrated = true
  }

  function add(product: Product) {
    hydrate()
    // Ukloni duplikat
    items.value = items.value.filter(p => p.id !== product.id)
    // Dodaj na početak
    items.value.unshift(product)
    // Limitiraj
    if (items.value.length > MAX_ITEMS) {
      items.value = items.value.slice(0, MAX_ITEMS)
    }
    persist()
  }

  function persist() {
    if (!import.meta.client) return
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value))
  }

  function getExcluding(productId: number, limit = 8): Product[] {
    hydrate()
    return items.value.filter(p => p.id !== productId).slice(0, limit)
  }

  return { items: computed(() => items.value), add, getExcluding, hydrate }
}
