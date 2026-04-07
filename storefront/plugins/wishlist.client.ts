export default defineNuxtPlugin(() => {
  const wishlistStore = useWishlistStore()
  wishlistStore.hydrate()

  // Sync sa serverom kad je korisnik ulogovan
  const authStore = useAuthStore()
  if (authStore.isLoggedIn) {
    wishlistStore.fetchFromServer()
  }
})
