export function useAuth() {
  const store = useAuthStore()
  const router = useRouter()

  const isLoggedIn = computed(() => store.isLoggedIn)
  const user = computed(() => store.user)

  async function login(email: string, password: string) {
    await store.login(email, password)
    const redirect = useRoute().query.redirect as string
    await router.push(redirect || '/')
  }

  async function register(name: string, email: string, password: string, passwordConfirmation: string, phone?: string) {
    await store.register(name, email, password, passwordConfirmation, phone)
    await router.push('/')
  }

  async function logout() {
    await store.logout()
    await router.push('/')
  }

  return { isLoggedIn, user, login, register, logout }
}
