# eLokal — Webshop Template

## Arhitektura

Monorepo sa 3 projekta:
- `api/` — Laravel 12 (PHP 8.2) — REST API backend
- `admin/` — Nuxt 3 (SPA mode, port 3001) — Admin panel
- `storefront/` — Nuxt 3 (SSR mode, port 3000) — Javni webshop

## Tech stack

### Backend (api/)
- Laravel 12, PHP 8.2
- SQLite (development), MySQL/PostgreSQL (production)
- Sanctum za autentifikaciju
- Queue jobs za image processing

### Frontend (admin/ & storefront/)
- Nuxt 3, Vue 3, TypeScript (strict mode)
- Tailwind CSS (@nuxtjs/tailwindcss)
- Pinia (@pinia/nuxt) za state management

## Konvencije

### Kod
- Srpski komentari, engleski kod (varijable, funkcije, klase)
- TypeScript strict mode — nema `any`, nema `@ts-ignore`
- Vue Composition API (`<script setup lang="ts">`)
- Laravel: Form Requests za validaciju, Resources za API response

### Komponente (Nuxt)
- `components/ui_atoms/` — Button, Input, Badge, itd.
- `components/ui_molecules/` — Card, Modal, Dropdown, itd.
- `components/layout/` — Header, Sidebar, Footer
- `components/product/` — ProductCard, ProductGrid, itd.
- `components/cart/` — CartItem, CartSummary, itd.
- `composables/` — useApi, useAuth, itd.
- `stores/` — Pinia stores
- `types/` — TypeScript interfejsi i tipovi
- `utils/` — Helper funkcije

### API rute
- Admin: `api/admin/*` (zaštićeno Sanctum middleware-om)
- Javno: `api/v1/*` (public storefront endpoints)

## Pokretanje

```bash
# API
cd api && php artisan serve

# Admin
cd admin && npm run dev

# Storefront
cd storefront && npm run dev
```

## Dokumentacija
- `.mdjdocs/todo.md` — task tracker sa verzijama
- `.mdjdocs/notes/` — beleške po sesijama
