import type { LoginCredentials } from '~/types'

export function useAuth() {
  const store = useAuthStore()
  const router = useRouter()

  const isLoggedIn = computed(() => store.isLoggedIn)
  const admin = computed(() => store.admin)

  async function login(credentials: LoginCredentials) {
    await store.login(credentials)
    await router.push('/')
  }

  async function logout() {
    await store.logout()
    await router.push('/login')
  }

  return { isLoggedIn, admin, login, logout }
}
