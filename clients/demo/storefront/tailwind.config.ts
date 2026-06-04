import type { Config } from 'tailwindcss'

// Demo nema svoju temu — content paths pokazuju na base komponente da Tailwind
// generiše njihove klase. Theme (boje, font) se nasleđuje iz base tailwind.config
// kroz @nuxtjs/tailwindcss layer merge.
export default {
  content: [
    '../../../storefront/components/**/*.{vue,ts}',
    '../../../storefront/layouts/**/*.vue',
    '../../../storefront/pages/**/*.vue',
    '../../../storefront/composables/**/*.ts',
    '../../../storefront/plugins/**/*.ts',
    '../../../storefront/app.vue',
  ],
} satisfies Config
