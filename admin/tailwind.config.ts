import type { Config } from 'tailwindcss'

export default {
  content: [
    './components/**/*.{vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './composables/**/*.ts',
    './plugins/**/*.ts',
    './app.vue',
  ],
  theme: {
    fontFamily: {
      sans: ['Quicksand', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
    },
    extend: {
      colors: {
        primary: {
          DEFAULT: '#005f73',
          hover: '#001219',
          light: '#e9d8a6',
          50: '#e9d8a6',
          100: '#94d2bd',
          200: '#0a9396',
          300: '#0a9396',
          400: '#005f73',
          500: '#005f73',
          600: '#005f73',
          700: '#001219',
          800: '#001219',
        },
        accent: {
          DEFAULT: '#ee9b00',
          dark: '#ca6702',
        },
        dark: '#001219',
      },
      borderRadius: {
        none: '0px',
        sm: '2px',
        DEFAULT: '0px',
        md: '0px',
        lg: '0px',
        xl: '0px',
        '2xl': '0px',
        '3xl': '0px',
      },
    },
  },
} satisfies Config
