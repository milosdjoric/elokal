import { defineStore } from 'pinia'

export const useWishlistStore = defineStore('wishlist', {
  state: () => ({
    ids: [] as number[],
    synced: false,
  }),

  getters: {
    count: (state): number => state.ids.length,
    isInWishlist: (state) => (productId: number): boolean => state.ids.includes(productId),
  },

  actions: {
    async toggle(productId: number) {
      const authStore = useAuthStore()

      if (this.isInWishlist(productId)) {
        this.ids = this.ids.filter(id => id !== productId)
        if (authStore.isLoggedIn) {
          const { apiBase } = useApi()
          try {
            await $fetch(`${apiBase}/v1/wishlist/${productId}`, {
              method: 'DELETE',
              headers: { Authorization: `Bearer ${authStore.token}`, Accept: 'application/json' },
            })
          }
          catch { /* revert silently handled by persist */ }
        }
      }
      else {
        this.ids.push(productId)
        if (authStore.isLoggedIn) {
          const { apiBase } = useApi()
          try {
            await $fetch(`${apiBase}/v1/wishlist/${productId}`, {
              method: 'POST',
              headers: { Authorization: `Bearer ${authStore.token}`, Accept: 'application/json' },
            })
          }
          catch { /* silent */ }
        }
      }
      this.persist()
    },

    async fetchFromServer() {
      const authStore = useAuthStore()
      if (!authStore.isLoggedIn) return

      const { apiBase } = useApi()
      try {
        const data = await $fetch<{ data: number[] }>(`${apiBase}/v1/wishlist/ids`, {
          headers: { Authorization: `Bearer ${authStore.token}`, Accept: 'application/json' },
        })
        // Merge local + server
        const merged = [...new Set([...this.ids, ...data.data])]
        if (merged.length !== data.data.length) {
          // Sync local items to server
          await $fetch(`${apiBase}/v1/wishlist/sync`, {
            method: 'POST',
            headers: {
              Authorization: `Bearer ${authStore.token}`,
              'Content-Type': 'application/json',
              Accept: 'application/json',
            },
            body: JSON.stringify({ product_ids: merged }),
          })
        }
        this.ids = merged
        this.synced = true
        this.persist()
      }
      catch { /* silent */ }
    },

    persist() {
      if (import.meta.client) {
        localStorage.setItem('wishlist', JSON.stringify(this.ids))
      }
    },

    hydrate() {
      if (import.meta.client) {
        const saved = localStorage.getItem('wishlist')
        if (saved) {
          try { this.ids = JSON.parse(saved) }
          catch { this.ids = [] }
        }
      }
    },
  },
})
