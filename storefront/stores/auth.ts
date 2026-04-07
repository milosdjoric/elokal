import { defineStore } from 'pinia'

interface User {
  id: number
  name: string
  email: string
  phone: string | null
  email_verified_at: string | null
  newsletter_subscribed: boolean
}

interface AuthResponse {
  token: string
  user: User
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    token: null as string | null,
  }),

  getters: {
    isLoggedIn: (state): boolean => !!state.token,
  },

  actions: {
    async login(email: string, password: string) {
      const { apiBase } = useApi()
      const data = await $fetch<AuthResponse>(`${apiBase}/v1/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ email, password }),
      })
      this.setAuth(data)
      return data
    },

    async register(name: string, email: string, password: string, passwordConfirmation: string, phone?: string) {
      const { apiBase } = useApi()
      const data = await $fetch<AuthResponse>(`${apiBase}/v1/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ name, email, password, password_confirmation: passwordConfirmation, phone }),
      })
      this.setAuth(data)
      return data
    },

    async logout() {
      const { apiBase } = useApi()
      try {
        await $fetch(`${apiBase}/v1/logout`, {
          method: 'POST',
          headers: { Authorization: `Bearer ${this.token}`, Accept: 'application/json' },
        })
      }
      finally {
        this.clearAuth()
      }
    },

    async fetchMe() {
      if (!this.token) return
      const { apiBase } = useApi()
      try {
        const user = await $fetch<User>(`${apiBase}/v1/me`, {
          headers: { Authorization: `Bearer ${this.token}`, Accept: 'application/json' },
        })
        this.user = user
      }
      catch {
        this.clearAuth()
      }
    },

    setAuth(data: AuthResponse) {
      this.token = data.token
      this.user = data.user
      if (import.meta.client) {
        localStorage.setItem('user_token', data.token)
      }
    },

    clearAuth() {
      this.token = null
      this.user = null
      if (import.meta.client) {
        localStorage.removeItem('user_token')
      }
    },

    hydrate() {
      if (import.meta.client) {
        const token = localStorage.getItem('user_token')
        if (token) {
          this.token = token
          this.fetchMe()
        }
      }
    },
  },
})
