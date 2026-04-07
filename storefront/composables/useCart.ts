import type { Product } from '~/types'

export function useCart() {
  const store = useCartStore()

  const count = computed(() => store.count)
  const total = computed(() => store.formattedTotal)
  const items = computed(() => store.items)
  const isEmpty = computed(() => store.items.length === 0)

  function addToCart(product: Product, quantity = 1) {
    store.addItem(product, quantity)
  }

  function removeFromCart(productId: number) {
    store.removeItem(productId)
  }

  function updateQuantity(productId: number, quantity: number) {
    store.updateQuantity(productId, quantity)
  }

  function clearCart() {
    store.clear()
  }

  return { items, count, total, isEmpty, addToCart, removeFromCart, updateQuantity, clearCart }
}
