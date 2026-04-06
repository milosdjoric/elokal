export default defineNuxtConfig({
  ssr: false,
  devtools: { enabled: true },
  compatibilityDate: '2025-01-01',

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
  ],

  typescript: {
    strict: true,
    typeCheck: true,
  },
})
