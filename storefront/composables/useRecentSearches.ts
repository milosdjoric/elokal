const MAX_RECENT = 5

export function useRecentSearches() {
  const searches = ref<string[]>([])

  function load() {
    if (import.meta.client) {
      try {
        searches.value = JSON.parse(localStorage.getItem('recent_searches') || '[]')
      }
      catch { searches.value = [] }
    }
  }

  function add(query: string) {
    const q = query.trim()
    if (!q) return
    searches.value = [q, ...searches.value.filter(s => s !== q)].slice(0, MAX_RECENT)
    if (import.meta.client) {
      localStorage.setItem('recent_searches', JSON.stringify(searches.value))
    }
  }

  function clear() {
    searches.value = []
    if (import.meta.client) {
      localStorage.removeItem('recent_searches')
    }
  }

  load()

  return { searches, add, clear }
}
