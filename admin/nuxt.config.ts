export default defineNuxtConfig({
  ssr: false,
  devtools: { enabled: true },
  compatibilityDate: '2025-01-01',

  devServer: {
    port: 3001,
  },

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
  ],

  // Per-klijent API URL preko NUXT_PUBLIC_API_BASE (Vercel build env).
  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000/api',
    },
  },

  app: {
    head: {
      link: [
        {
          rel: 'preconnect',
          href: 'https://fonts.googleapis.com',
        },
        {
          rel: 'preconnect',
          href: 'https://fonts.gstatic.com',
          crossorigin: '',
        },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap',
        },
      ],
    },
  },

  typescript: {
    strict: true,
    typeCheck: true,
  },
})
