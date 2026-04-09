export default defineNuxtRouteMiddleware((to) => {
  if (import.meta.server) return

  const token = localStorage.getItem('user_token')
  if (!token) {
    return navigateTo({ path: '/nalog/login', query: { redirect: to.fullPath } })
  }
})
