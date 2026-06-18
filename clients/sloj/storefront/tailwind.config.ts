import type { Config } from 'tailwindcss'

export default {
  darkMode: 'class',
  content: [
    './components/**/*.{vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './composables/**/*.ts',
    './plugins/**/*.ts',
    './app.vue',
    // Nasleđene komponente žive u elokal base layeru — moraju se skenirati
    // ovde ili renderuju bez stilova (klase ih nema u izlaznom CSS-u).
    '../../../storefront/components/**/*.{vue,ts}',
    '../../../storefront/layouts/**/*.vue',
    '../../../storefront/pages/**/*.vue',
    '../../../storefront/composables/**/*.ts',
    '../../../storefront/app.vue',
  ],
  theme: {
    fontFamily: {
      sans: ['Afacad Flux', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Helvetica Neue', 'sans-serif'],
      mono: ['JetBrains Mono', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
    },
    extend: {
      colors: {
        // surface tokens — `paper` umesto `bg` jer Tailwind koristi `bg-*` kao utility prefix
        paper: {
          DEFAULT: '#ffffff',  // pure white — page bg
          soft: '#f8f8f7',     // very subtle off-white, hover surfaces
          tinted: '#fbf7f0',   // ply tint — koristiti samo za retke warm pause sections
        },
        surface: '#ffffff',    // pure white — kartice, modali
        ply: {
          50:  '#fbf7f0',
          100: '#f5edde',
          200: '#ede1c8',
          300: '#e8d5b0',
          400: '#d9c094',
          500: '#c9a875',
          600: '#a88a5c',
          700: '#876d45',
          800: '#5c4a30',
          900: '#3a2e1e',
          DEFAULT: '#e8d5b0',
        },
        ink: {
          50:  '#f5f5f5',
          100: '#e5e5e5',
          200: '#d4d4d4',
          300: '#a3a3a3',
          400: '#737373',
          500: '#525252',
          600: '#404040',
          700: '#262626',
          800: '#1a1a1a',
          900: '#0a0a0a',
          DEFAULT: '#1a1a1a',
        },
        terra: {
          50:  '#faede6',
          100: '#f5d8c7',
          200: '#edb9a0',
          300: '#e09573',
          400: '#cb7551',
          500: '#b85c38',
          600: '#9f4b2c',
          700: '#823a22',
          800: '#642b19',
          900: '#421c10',
          DEFAULT: '#b85c38',
        },
        success: {
          DEFAULT: '#3c5f32',
          soft: '#ebf0dc',
        },
        warning: {
          DEFAULT: '#9c5f1e',
          soft: '#faebd2',
        },
        danger: {
          DEFAULT: '#9f4b2c',
          soft: '#f5d8c7',
        },
        info: {
          DEFAULT: '#404040',
          soft: '#f5f2eb',
        },

        /* ── compat aliasi (privremeno; obriši kad se rebrand v0.7 završi) ──
         * Stari kod u komponentama koristi primary/accent/warm/dark;
         * mapirano na novu paletu da UI ne pukne dok refaktorišemo fazu po fazu.
         * Semantika: primary→ink (structural/text), accent→terra (single accent), warm→ply (warm tone). */
        primary: {
          50:  '#fbf7f0',  // ply-50
          100: '#f5edde',  // ply-100
          200: '#ede1c8',  // ply-200
          300: '#a3a3a3',  // ink-300
          400: '#525252',  // ink-500
          500: '#404040',  // ink-600
          600: '#262626',  // ink-700
          700: '#1a1a1a',  // ink-800 — bilo "default link/CTA"
          800: '#0a0a0a',  // ink-900 — bilo "deep storytelling bg"
          900: '#0a0a0a',  // ink-900 — bilo "TopBar/footer bg"
          DEFAULT: '#1a1a1a',
          hover: '#262626',
          light: '#fbf7f0',
        },
        accent: {
          50:  '#faede6',
          100: '#f5d8c7',
          200: '#edb9a0',
          300: '#e09573',
          400: '#cb7551',  // bilo "TopBar truck ikona"
          500: '#b85c38',  // bilo "primary CTA boja"
          600: '#9f4b2c',
          700: '#823a22',  // bilo "eyebrow text na warm/svetlim"
          800: '#642b19',
          900: '#421c10',
          DEFAULT: '#b85c38',
          dark: '#9f4b2c',
        },
        warm: {
          DEFAULT: '#ede1c8',  // ply-200
        },
        dark: '#1a1a1a',  // ink-800
      },
      borderRadius: {
        none: '0px',
        sm: '0px',
        DEFAULT: '0px',
        md: '0px',
        lg: '0px',
        xl: '0px',
        '2xl': '0px',
        '3xl': '0px',
        pill: '9999px',
      },
      boxShadow: {
        none: 'none',
        focus: '0 0 0 2px rgb(250 250 250), 0 0 0 4px #b85c38',
      },
      fontSize: {
        micro:    ['11px', { lineHeight: '16px',  letterSpacing: '0.08em' }],
        caption:  ['12px', { lineHeight: '18px',  letterSpacing: '0.04em' }],
        eyebrow:  ['11px', { lineHeight: '16px',  letterSpacing: '0.16em' }],
        display:  ['clamp(2.5rem, 6vw, 5rem)', { lineHeight: '1.05', letterSpacing: '-0.02em' }],
      },
      letterSpacing: {
        wider: '0.16em',
      },
      transitionTimingFunction: {
        'out-soft': 'cubic-bezier(0.16, 1, 0.3, 1)',
      },
      transitionDuration: {
        180: '180ms',
      },
    },
  },
} satisfies Config
