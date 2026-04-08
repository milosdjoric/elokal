import { defineStore } from 'pinia'
import type { Product } from '~/types'

const MAX_COMPARE = 4

export const useCompareStore = defineStore('compare', {
  state: () => ({
    items: [] as Product[],
  }),

  getters: {
    count: (state): number => state.items.length,
    isInCompare: (state) => (productId: number): boolean => state.items.some(p => p.id === productId),
    isFull: (state): boolean => state.items.length >= MAX_COMPARE,
  },

  actions: {
    toggle(product: Product) {
      if (this.isInCompare(product.id)) {
        this.items = this.items.filter(p => p.id !== product.id)
      } else if (!this.isFull) {
        this.items.push(product)
      }
      this.persist()
    },

    remove(productId: number) {
      this.items = this.items.filter(p => p.id !== productId)
      this.persist()
    },

    clear() {
      this.items = []
      this.persist()
    },

    persist() {
      if (import.meta.client) {
        localStorage.setItem('compare', JSON.stringify(this.items.map(p => p.id)))
      }
    },

    async hydrate() {
      if (!import.meta.client) return
      const saved = localStorage.getItem('compare')
      if (!saved) return
      try {
        const ids: number[] = JSON.parse(saved)
        if (ids.length === 0) return
        const { get } = useApi()
        // Učitaj proizvode po ID-jevima
        const data = await get<{ data: Product[] }>('/v1/products', { per_page: MAX_COMPARE })
        this.items = data.data.filter(p => ids.includes(p.id))
      }
      catch { this.items = [] }
    },
  },
})
