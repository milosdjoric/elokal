# Playwright E2E Testiranje — eLokal

Datum: 2026-04-08

---

## Setup

Playwright je postavljen u root-u monorepo-a u `e2e/` folderu. Testira oba frontenda iz jednog mesta.

### Preduslovi za pokretanje

Sva tri servera moraju da rade:
```bash
cd api && php artisan serve          # port 8000
cd storefront && npm run dev         # port 3000
cd admin && npm run dev              # port 3001
```

Baza mora biti seedovana:
```bash
cd api && php artisan migrate:fresh --seed
```

### Komande

```bash
npm test                    # svi testovi (storefront + admin)
npm run test:storefront     # samo storefront testovi
npm run test:admin          # samo admin testovi
npm run test:ui             # Playwright UI mode (vizuelni debugger)
```

### Kredencijali za testove

| Korisnik | Email | Lozinka |
|----------|-------|---------|
| Admin | admin@webshop.test | password |
| Kupac | kupac@test.com | password |

---

## Struktura

```
e2e/
├── playwright.config.ts          — config (2 projekta: storefront :3000, admin :3001)
├── helpers/
│   ├── api.ts                    — direktni API fetch pozivi za seed/cleanup
│   └── auth.ts                   — login/register/logout helperi
├── fixtures/
│   └── index.ts                  — custom fixtures (authenticatedPage)
├── storefront/                   — 12 spec fajlova, 77 testova
│   ├── auth.spec.ts
│   ├── cart.spec.ts
│   ├── checkout.spec.ts
│   ├── plp.spec.ts
│   ├── pdp.spec.ts
│   ├── search.spec.ts
│   ├── account.spec.ts
│   ├── homepage.spec.ts
│   ├── blog.spec.ts
│   ├── static-pages.spec.ts
│   ├── compare.spec.ts
│   └── misc.spec.ts
└── admin/                        — 14 spec fajlova, 62 testova
    ├── auth.spec.ts
    ├── orders.spec.ts
    ├── products.spec.ts
    ├── categories.spec.ts
    ├── customers.spec.ts
    ├── reviews.spec.ts
    ├── coupons.spec.ts
    ├── inventory.spec.ts
    ├── settings.spec.ts
    ├── dashboard.spec.ts
    ├── media.spec.ts
    ├── blog.spec.ts
    ├── admins.spec.ts
    └── misc.spec.ts
```

**Ukupno: 139 testova u 26 spec fajlova.**

---

## Prioriteti

### P0 — Kritično (34 testova)
Bez ovoga nema kupovine. Mora uvek da prolazi.

| Spec | Testova | Šta pokriva |
|------|---------|-------------|
| storefront/auth | 10 | Login, register, logout, guard, redirect, navigacija |
| storefront/cart | 7 | Add to cart, remove, persist, totals, coupon, empty state |
| storefront/checkout | 10 | Guest/auth flow, validacija, billing, coupon, gift card, trust |
| admin/auth | 7 | Login, logout, guard, sidebar |

### P1 — Važno (32 testova)
Core korisničko iskustvo i admin operacije.

| Spec | Testova | Šta pokriva |
|------|---------|-------------|
| storefront/plp | 10 | Filteri, sortiranje, paginacija, layout, kategorije, cena |
| storefront/pdp | 8 | Galerija, tabovi, add to cart, quantity, breadcrumbs |
| admin/orders | 6 | Listing, pretraga, filter, detail, status |
| admin/products | 8 | CRUD, pretraga, filter, kolone, delete confirm |

### P2 — Srednji prioritet (36 testova)
Sekundarne funkcionalnosti.

| Spec | Testova | Šta pokriva |
|------|---------|-------------|
| storefront/search | 4 | Autocomplete, search stranica, recent, empty |
| storefront/account | 8 | Dashboard, profil, adrese, narudžbine, wishlist, poeni, krediti |
| admin/categories | 3 | Tree, create, delete |
| admin/customers | 3 | Listing, pretraga, detail |
| admin/reviews | 3 | Listing, filter, akcije |
| admin/coupons | 3 | Listing, create, bulk |
| admin/inventory | 4 | Listing, kolone, export, import |
| admin/settings | 4 | Renderovanje, tabovi, polja, save |

### P3 — Nice to have (37 testova)
Content, sporedne stranice, admin alati.

| Spec | Testova | Šta pokriva |
|------|---------|-------------|
| storefront/homepage | 5 | Hero, featured, kategorije, newsletter |
| storefront/blog | 4 | Listing, post, sidebar |
| storefront/static-pages | 5 | /o-nama, /kontakt, /uslovi, /privatnost, /cesta-pitanja |
| storefront/compare | 2 | Prazna stranica, dugme |
| storefront/misc | 8 | Gift card, tracking, 404, cookie, mobile nav, top bar, currency, back to top |
| admin/dashboard | 3 | Statistike, low stock, sidebar navigacija |
| admin/media | 3 | Renderovanje, grid, upload |
| admin/blog | 4 | Listing, create, edit |
| admin/admins | 3 | Listing, create, tabela |
| admin/misc | 8 | Atributi, tax, webhooks, import/export, newsletter, activity, shipping, payments |

---

## Helpers

### `helpers/auth.ts`
- `storefrontLogin(page, email, password)` — login na storefront
- `storefrontRegister(page, { name, email, password, phone })` — registracija
- `storefrontLogout(page)` — logout
- `adminLogin(page, email, password)` — login na admin
- `adminLogout(page)` — logout iz admina
- `fillInput(page, labelText, value)` — popunjava UiAtomsInput (label bez `for` atributa)

### `helpers/api.ts`
- `apiRequest(path, options)` — generički fetch na API
- `loginStorefrontUser(email, password)` — vraća token
- `registerStorefrontUser(user)` — vraća token
- `loginAdmin(email, password)` — vraća token
- `getProducts(params)` — lista proizvoda
- `getProductBySlug(slug)` — jedan proizvod

### `fixtures/index.ts`
- `authenticatedStorefrontPage` — page sa ulogovanim kupcem
- `authenticatedAdminPage` — page sa ulogovanim adminom

---

## Config highlights

- **Browser:** Chromium (Desktop Chrome)
- **Retries:** 0 lokalno, 1 na CI
- **Screenshot:** samo na failure
- **Video:** čuva samo na failure
- **Trace:** na prvom retry-u
- **Parallel:** da (fullyParallel: true)
- **webServer:** NE — serveri se pokreću ručno

---

## Napomene

- `UiAtomsInput` komponenta nema `for` atribut na `<label>`, pa `getByLabel()` ne radi. Helper `fillInput()` locira kontejner po tekstu labela pa bira `input` unutar njega.
- Admin login koristi standard HTML `<label for="email">` pa radi `page.locator('#email')`.
- Storefront rute su `/account/login`, `/account/register`, itd. (ne `/nalog/prijava`).
- Testovi koriste seed podatke iz `DatabaseSeeder`.
- Svaki test je nezavisan — ne zavisi od redosleda izvršavanja.
- P3 testovi su defanzivni — ne padaju ako feature ne postoji (koriste `isVisible()` provere).

---

## Sledeći koraci

- [ ] Pokrenuti testove i popraviti selektore koji ne rade sa stvarnim DOM-om
- [ ] Dodati `data-testid` atribute na ključne elemente (opciono, za robusnije selektore)
- [ ] Dodati test za varijante (PDP swatch selekcija, promena cene/slike)
- [ ] Dodati test za drag & drop reorder (kategorije, slike)
- [ ] Integrisati u CI/CD pipeline (GitHub Actions)
- [ ] Dodati visual regression testove (Playwright screenshot comparison)
