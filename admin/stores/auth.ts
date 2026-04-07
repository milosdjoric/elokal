import { defineStore } from 'pinia'
import type { Admin, AuthResponse, LoginCredentials } from '~/types'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    admin: null as Admin | null,
    token: null as string | null,
  }),

  getters: {
    isLoggedIn: (state): boolean => !!state.token,
    isSupperAdmin: (state): boolean => state.admin?.role === 'super_admin',
  },

  actions: {
    async login(credentials: LoginCredentials) {
      const { post } = useApi()
      const data = await post<AuthResponse>('/admin/login', credentials)

      this.token = data.token
      this.admin = data.admin
      localStorage.setItem('admin_token', data.token)

      return data
    },

    async logout() {
      const { post } = useApi()

      try {
        await post('/admin/logout')
      }
      finally {
        this.clearAuth()
      }
    },

    async fetchMe() {
      const { get } = useApi()

      try {
        const admin = await get<Admin>('/admin/me')
        this.admin = admin
      }
      catch {
        this.clearAuth()
      }
    },

    initAuth() {
      if (import.meta.server) return

      const token = localStorage.getItem('admin_token')
      if (token) {
        this.token = token
        this.fetchMe()
      }
    },

    clearAuth() {
      this.token = null
      this.admin = null
      localStorage.removeItem('admin_token')
    },
  },
})
