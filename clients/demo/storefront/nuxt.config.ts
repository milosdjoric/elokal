import { createResolver } from '@nuxt/kit'

const { resolve } = createResolver(import.meta.url)

export default defineNuxtConfig({
  // Demo klijent — nasleđuje SAV izgled iz elokal base-a (nula override).
  // Ogledalo base-a: sve što se napravi u elokal/storefront, demo dobija automatski.
  extends: ['../../../storefront'],

  // U Nuxt layeru `~` pokazuje na root KONZUMENTA (demo), a ne na base. Base fajlovi
  // koji rade `import ... from '~/types'` / `'~/locales/...'` bi pucali (demo nema te
  // foldere) → "implicitly any". Preusmeri ta dva ne-auto-import puta na base.
  alias: {
    '~/types': resolve('../../../storefront/types'),
    '~/locales': resolve('../../../storefront/locales'),
  },

  ssr: true,
  devtools: { enabled: true },
  compatibilityDate: '2025-01-01',

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
  ],

  // Koristi base tailwind.css direktno (demo nema svoj — nasleđuje stil).
  tailwindcss: {
    cssPath: '../../../storefront/assets/css/tailwind.css',
  },

  // @pinia/nuxt ne skenira stores/ iz base layera — uključi ih eksplicitno.
  pinia: {
    storesDirs: ['../../../storefront/stores/**'],
  },

  // Layer ima odvojen node_modules → dedupe prisiljava jednu instancu.
  vite: {
    resolve: {
      dedupe: ['pinia', 'vue', 'vue-router'],
    },
  },

  runtimeConfig: {
    public: {
      // Demo ima svoj nezavisan api/bazu (elokal-demo-api → elokal-demo-db).
      apiBase: 'https://elokal-demo-api-production.up.railway.app/api',
    },
  },

  typescript: {
    strict: true,
    typeCheck: true,
  },
})
