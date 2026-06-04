import { defineStore } from 'pinia'
import type { CartItem, Product } from '~/types'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [] as CartItem[],
    drawerOpen: false,
    lastAddedId: null as number | null,
  }),

  getters: {
    count: (state): number => state.items.reduce((sum, item) => sum + item.quantity, 0),

    total: (state): number => state.items.reduce((sum, item) => {
      const price = parseFloat(item.product.effective_price)
      return sum + price * item.quantity
    }, 0),

    formattedTotal(): string {
      return formatPrice(this.total)
    },
  },

  actions: {
    addItem(product: Product, quantity = 1) {
      const existing = this.items.find(i => i.product.id === product.id)
      if (existing) {
        existing.quantity += quantity
      }
      else {
        this.items.push({ product, quantity })
      }
      this.lastAddedId = product.id
      this.drawerOpen = true
      this.persist()
    },

    openDrawer() {
      this.drawerOpen = true
    },

    closeDrawer() {
      this.drawerOpen = false
      this.lastAddedId = null
    },

    removeItem(productId: number) {
      this.items = this.items.filter(i => i.product.id !== productId)
      this.persist()
    },

    updateQuantity(productId: number, quantity: number) {
      const item = this.items.find(i => i.product.id === productId)
      if (item) {
        if (quantity <= 0) {
          this.removeItem(productId)
        }
        else {
          item.quantity = quantity
          this.persist()
        }
      }
    },

    clear() {
      this.items = []
      this.persist()
    },

    persist() {
      if (import.meta.client) {
        localStorage.setItem('cart', JSON.stringify(this.items))
      }
    },

    hydrate() {
      if (import.meta.client) {
        const saved = localStorage.getItem('cart')
        if (saved) {
          try {
            this.items = JSON.parse(saved)
          }
          catch {
            this.items = []
          }
        }
      }
    },
  },
})
