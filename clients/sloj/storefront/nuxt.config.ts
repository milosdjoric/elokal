export default defineNuxtConfig({
  // Nasleđuje elokal storefront kao base layer (Flavor A / multi-client).
  // Sloj override-uje samo redizajn fajlove; sve ostalo dolazi iz elokala.
  extends: ['../../../storefront'],

  ssr: true,
  devtools: { enabled: true },
  compatibilityDate: '2025-01-01',

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
    '@nuxt/icon',
  ],

  icon: {
    serverBundle: {
      collections: ['lucide'],
    },
  },

  // Eksplicitan CSS entry da @nuxtjs/tailwindcss ne učita i elokal tailwind.css.
  tailwindcss: {
    cssPath: '~/assets/css/tailwind.css',
  },

  // @pinia/nuxt ne skenira stores/ iz base layera (za razliku od Nuxt nativnih
  // composables/utils). Eksplicitno uključi i elokal stores da se nasleđuju.
  pinia: {
    storesDirs: ['./stores/**', '../../../storefront/stores/**'],
  },

  // Layer ima odvojen node_modules od elokal base-a → dve kopije pinia/vue.
  // dedupe prisiljava jednu instancu (inače "getActivePinia() ... no active Pinia"
  // jer elokal komponente vide drugu pinia instancu od one koju plugin registruje).
  vite: {
    resolve: {
      dedupe: ['pinia', 'vue', 'vue-router'],
    },
  },

  app: {
    head: {
      htmlAttrs: { lang: 'sr-Latn-RS' },
      link: [
        { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' },
        { rel: 'apple-touch-icon', sizes: '180x180', href: '/apple-touch-icon.png' },
        { rel: 'manifest', href: '/site.webmanifest' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap' },
      ],
      meta: [
        { name: 'theme-color', content: '#1a1a1a' },
        { name: 'apple-mobile-web-app-title', content: 'sloj kolektiv' },
        { name: 'application-name', content: 'sloj kolektiv' },
        { property: 'og:type', content: 'website' },
        { property: 'og:site_name', content: 'sloj kolektiv' },
        { property: 'og:locale', content: 'sr_RS' },
        { property: 'og:image', content: '/og-image.png' },
        { property: 'og:image:width', content: '1200' },
        { property: 'og:image:height', content: '630' },
        { property: 'og:image:alt', content: 'sloj kolektiv — slojevit nameštaj, dosledno' },
        { name: 'twitter:card', content: 'summary_large_image' },
        { name: 'twitter:image', content: '/og-image.png' },
      ],
    },
  },

  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000/api',
    },
  },

  typescript: {
    strict: true,
    typeCheck: true,
  },
})
