# Webshop Template — TODO & Verzije

Verzija format: `v0.{faza}.{sekcija}` — svaka sekcija = jedan release.

---

## v0.1.x — Faza 1: MVP

### v0.1.1 — Inicijalizacija projekata
- [x] Git init + .gitignore
- [x] Laravel 12 projekat u `api/`
- [x] Nuxt 3 projekat u `admin/` (SPA mode)
- [x] Nuxt 3 projekat u `storefront/` (SSR mode)
- [x] CLAUDE.md kreiran
- [x] Tailwind CSS setup (admin + storefront)
- [x] TypeScript konfiguracija (strict mode, oba Nuxt projekta)
- [x] Pinia setup (admin + storefront)
- [x] Folder struktura: components (ui_atoms, ui_molecules, layout, product, cart), composables, stores, types, utils

### v0.1.2 — API: Modeli i migracije
- [x] Product model + migracija (sva polja: name, slug, short_description, description, price, sale_price, sale_price_from, sale_price_to, cost_price, unit_price, unit_label, sku, stock_quantity, is_active, featured, sort_order, meta_title, meta_description, soft_deletes)
- [x] Category model + migracija (parent_id self-referencing, name, slug, description, image_path, sort_order, is_active, meta_title, meta_description)
- [x] ProductImage model + migracija (product_id FK, image_path, alt_text, sort_order, is_primary)
- [x] category_product pivot migracija
- [x] Admin model + migracija (name, email, password, role enum, is_active)
- [x] User model proširiti (phone polje)
- [x] SearchLog model + migracija (query, results_count, user_id nullable, ip, created_at)

### v0.1.3 — API: Factories i seederi
- [x] ProductFactory
- [x] CategoryFactory
- [x] ProductImageFactory
- [x] AdminSeeder (super_admin: admin@webshop.test / password)
- [x] CategorySeeder (6 kategorija sa podkategorijama)
- [x] ProductSeeder (30 proizvoda sa slikama)
- [x] DatabaseSeeder — povezati sve

### v0.1.4 — API: Auth (admin)
- [x] Sanctum setup sa admin guardom
- [x] AdminAuthController (login, logout, me)
- [x] Auth middleware za admin rute
- [x] Rate limiting za login (5 req/min)

### v0.1.5 — API: Product CRUD
- [x] ProductController (index, store, show, update, destroy)
- [x] ProductFormRequest (store validacija)
- [x] ProductUpdateRequest (update validacija)
- [x] ProductResource
- [x] ProductCollection (sa paginacijom)
- [x] Filteri: status, kategorija, featured, search query
- [x] Sortiranje: name, price, created_at, sort_order
- [x] Paginacija sa per_page param

### v0.1.6 — API: Product Images
- [x] ProductImageController (store, destroy, reorder)
- [x] Upload validacija (tip, veličina)
- [x] Image resize queue job (thumbnail, medium, large)
- [x] WebP konverzija
- [x] Storage disk konfiguracija

### v0.1.7 — API: Category CRUD
- [x] CategoryController (index, store, update, destroy)
- [x] CategoryFormRequest
- [x] CategoryResource (sa children tree)
- [x] Tree struktura response (nested children)

### v0.1.8 — API: Javne rute (storefront)
- [x] Public ProductController (index sa filterima, show po slug-u)
- [x] Public CategoryController (index tree, show sa proizvodima)
- [x] SearchController (full-text pretraga, logovanje u SearchLog)
- [x] Registracija javnih API ruta

### v0.1.9 — API: Dashboard
- [x] DashboardController (ukupno proizvoda, kategorija, aktivnih proizvoda)

### v0.1.10 — API: Cene logika
- [x] Sale price scope (aktivna samo u periodu sale_price_from/to)
- [x] Price accessor (vraća sale_price ako je aktivan, inače price)
- [x] Sale percentage calculator (-X%)
- [x] Unit price formatting

### v0.1.11 — API: Testovi
- [x] Admin auth testovi (login, logout, me, invalid credentials, rate limit)
- [x] Product CRUD testovi (index, store, show, update, destroy, validacija, auth guard)
- [x] Product images testovi (upload, delete, reorder)
- [x] Category CRUD testovi (CRUD, tree structure)
- [x] Public product testovi (listing, filteri, sortiranje, paginacija, show by slug)
- [x] Public category testovi (tree, show sa proizvodima)
- [x] Search testovi (pretraga, no results, logovanje)
- [x] Dashboard testovi
- [x] Sale price logika testovi

### v0.1.12 — API: Konfiguracija
- [x] CORS setup (dozvoli admin + storefront domene)
- [x] API rate limiting middleware (60/min javne, 120/min auth, 5/min login)
- [x] Standardizovan JSON response format ({ data, message })
- [x] Standardizovan error format ({ message, errors })
- [x] Exception handler customization
- [x] Health check endpoint (/api/health)

### v0.1.13 — Admin: Setup i auth
- [x] Tailwind + base konfiguracija
- [x] useApi() composable (base URL, token, error handling, retry)
- [x] Auth store (Pinia) — useAuthStore
- [x] useAuth() composable (login, logout, isLoggedIn)
- [x] Login stranica
- [x] Auth middleware (redirect na login ako nema token)
- [x] TypeScript tipovi: Admin, AuthResponse, ApiResponse<T>

### v0.1.14 — Admin: Layout
- [x] Admin layout komponenta (sidebar + header + main content)
- [x] Sidebar navigacija (Dashboard, Proizvodi, Kategorije, Media Library, Podešavanja)
- [x] Header (admin ime, notifikacije bell, logout dugme)
- [x] Breadcrumbs komponenta
- [x] Global search u headeru

### v0.1.15 — Admin: UI Atoms
- [x] Button (variants: primary, secondary, danger, ghost; sizes: sm, md, lg; loading state)
- [x] Input (label, placeholder, error, disabled, types: text, email, password, number)
- [x] Badge (variants: success, warning, danger, info, neutral)
- [x] Icon (wrapper za ikonice)
- [x] Spinner
- [x] Checkbox
- [x] Radio
- [x] Switch (toggle)
- [x] Skeleton loader
- [x] Tooltip
- [x] Avatar

### v0.1.16 — Admin: UI Molecules
- [x] Modal (open/close, title, footer actions, focus trap, Escape close)
- [x] Toast/Notification sistem (success, error, warning, info, auto dismiss, stacking)
- [x] DataTable (sortiranje, paginacija, loading, empty state)
- [x] Dropdown (trigger + menu items)
- [x] Tabs (horizontal, content slot)
- [x] Accordion
- [x] QuantitySelector (+/− dugmad)
- [x] SearchBar (input + clear + submit)
- [x] FileUpload (drag & drop + browse + preview thumbnails)
- [x] PriceDisplay (regularna, sale precrtana, badge %)
- [x] Confirm dialog (modal sa confirm/cancel)

### v0.1.17 — Admin: Dashboard
- [x] Dashboard stranica sa placeholder statistikama (ukupno proizvoda, kategorija, aktivnih)

### v0.1.18 — Admin: Proizvodi
- [x] Product listing stranica (DataTable, pretraga, filter po statusu, paginacija)
- [x] Product create forma (sva polja uključujući cene)
- [x] Upload slika sa drag & drop i reorder (sortable)
- [x] Primary image selekcija
- [x] Kategorije multi-select
- [x] SEO polja (meta title, meta description)
- [x] Slug auto-generisanje iz naziva (editabilan)
- [x] Product edit forma (ista kao create, prefilled)
- [x] Delete sa confirm modalom
- [x] Toast notifikacije za uspeh/grešku
- [x] Form validacija (client-side + server error prikaz)
- [x] Loading stanja na svim akcijama

### v0.1.19 — Admin: Kategorije
- [x] Category listing (tree prikaz)
- [x] Category create/edit (inline edit ili modal)
- [x] Category delete sa confirm
- [x] Drag & drop reorder
- [x] Prikaz broja proizvoda po kategoriji
- [x] Image upload za kategoriju

### v0.1.20 — Admin: Media Library
- [x] Media library stranica (grid sa thumbnail-ima)
- [x] Drag & drop upload (single + bulk)
- [x] Alt text i title editing
- [x] Delete sa confirm
- [x] Media picker komponenta (reusable u product/category formama)

### v0.1.21 — Admin: Podešavanja
- [x] Settings stranica sa tabovima
- [x] General tab: naziv, logo, favicon, adresa, telefon, email, social linkovi
- [x] Storefront layout tab: header varijanta, PLP/PDP/Cart/Blog varijante, products per page
- [x] Top bar tab: uključen/isključen, tekst, boja
- [x] Trust & Conversion tab: stock status, urgency bar, countdown, shipping/return/dispatch tekst, trust badges
- [x] Cart & Checkout tab: add to cart feedback, free shipping threshold, guest checkout
- [x] Product badges tab: NEW threshold, badge boje
- [x] SEO tab: default meta title/description pattern, GA ID, FB Pixel ID
- [x] GDPR tab: cookie consent tekst, privacy/terms linkovi

### v0.1.22 — Storefront: Setup
- [x] Tailwind + base konfiguracija (boje, fontovi, spacing iz dizajn sistema)
- [x] useApi() composable (base URL, error handling)
- [x] useCart() composable + useCartStore (Pinia + localStorage)
- [x] useFeature() composable (feature flag provera)
- [x] TypeScript tipovi: Product, Category, ProductImage, CartItem, Cart

### v0.1.23 — Storefront: Layout komponente
- [x] Header (logo, mega menu, AJAX search, cart ikona sa badge)
- [x] Mega menu (podkategorije sa slikama/banerima — varijanta A ili B iz podešavanja)
- [x] Top bar (promo poruka, social ikone — ako uključen u podešavanjima)
- [x] Footer (4 kolone, social ikone, bottom bar sa payment ikonama)
- [x] Mobile bottom navigation (home, kategorije, search, korpa, nalog)
- [x] Back to top dugme
- [x] Breadcrumbs
- [x] Cookie consent baner

### v0.1.24 — Storefront: UI Atoms
- [x] Button (variants, sizes, loading)
- [x] Input (label, error, disabled)
- [x] Badge
- [x] Icon
- [x] Spinner
- [x] Skeleton loader

### v0.1.25 — Storefront: UI Molecules
- [x] SearchBar (AJAX autocomplete dropdown sa proizvodima, kategorijama, recent/trending)
- [x] QuantitySelector
- [x] PriceDisplay (regular, sale, unit price)
- [x] Modal
- [x] Toast
- [x] Tabs (horizontal desktop, accordion mobile)
- [x] Accordion
- [x] RatingStars (prikaz, ne input — input u Fazi 2)
- [x] TrustBadges (payment ikone)
- [x] SocialShare (FB, X, Pinterest, Email, Copy link)
- [x] Newsletter (email input + dugme)
- [x] Carousel (reusable, swipe podrška)

### v0.1.26 — Storefront: Product komponente
- [x] ProductCard (slika, swap hover, naziv, kategorija, cena, rating, badges, add to cart, quick view hover)
- [x] ProductGrid (responsive: 2 kolone mobile, 3 tablet, 3-4 desktop)
- [x] ProductPrice (regular, sale precrtana, unit price)
- [x] ProductGallery (glavna slika + thumbnails, zoom/lightbox, video podrška)
- [x] ProductCarousel (reusable za featured, related, recently viewed)
- [x] QuickView modal (slika, naziv, cena, opis, add to cart)

### v0.1.27 — Storefront: Cart komponente
- [x] CartDrawer (sidebar panel, lista stavki, subtotal, CTA dugmad)
- [x] CartItem (slika, naziv, cena, quantity +/−, remove, line total)
- [x] CartTotals (subtotal, placeholder za shipping/tax/discount, total)

### v0.1.28 — Storefront: Homepage
- [x] Hero slideshow (carousel sa slajdovima — slika, naslov, opis, CTA)
- [x] Featured proizvodi (carousel)
- [x] Kategorije grid sa slikama
- [x] Recently viewed (localStorage)
- [x] Page builder sekcije iz admin podešavanja (JSON → render komponenti)

### v0.1.29 — Storefront: PLP (Product Listing Page)
- [x] `/prodavnica` stranica
- [x] `/kategorija/:slug` stranica
- [x] Toolbar: result count, per page (9/12/18/24), grid density (2/3/4), grid/list toggle, sort dropdown
- [x] Sidebar filteri: category tree (expand/collapse + count), price range slider, stock status, rating
- [x] Active filters prikaz (sa X za uklanjanje, "Clear all")
- [x] Paginacija + "Učitaj još" opcija
- [x] Layout varijanta iz podešavanja (A sidebar / B top / C off-canvas)
- [x] Category header (naslov + opis + breadcrumbs)
- [x] Empty state (0 rezultata, resetuj filtere)
- [x] Skeleton loaderi dok se učitava

### v0.1.30 — Storefront: PDP (Product Detail Page)
- [x] `/proizvod/:slug` stranica
- [x] Galerija sa zoom/lightbox (varijanta iz podešavanja: A/B/C)
- [x] Product info: naziv, cena (PriceDisplay), short description, add to cart, quantity selector
- [x] Trust & Conversion blok (stock status, urgency bar, countdown, shipping/return info, trust badges)
- [x] Sticky Add to Cart bar (pojavi se na scroll, thumbnail + naziv + rating + cena + dugme)
- [x] PDP Tabs (opis, specifikacije, shipping & returns — recenzije placeholder za Fazu 2)
- [x] FAQ tab (accordion Q&A)
- [x] Custom tabovi po proizvodu (iz admin-a)
- [x] Responsive tabovi → accordion na mobilnom
- [x] Sticky tabs navigacija
- [x] Related products carousel
- [x] Recently viewed carousel
- [x] Prev/Next product navigacija
- [x] Social share blok
- [x] Breadcrumbs

### v0.1.31 — Storefront: Search
- [x] `/pretraga?q=` stranica (isti layout kao PLP)
- [x] AJAX autocomplete u headeru (debounced 300ms)
- [x] Recent searches (localStorage)
- [x] Trending searches (admin konfiguriše)
- [x] Search scope po kategoriji (dropdown)
- [x] No results state + predlozi

### v0.1.32 — Storefront: Korpa
- [x] `/korpa` stranica
- [x] Tabela stavki (slika, naziv, cena, quantity, line total, remove)
- [x] Cart totals (subtotal, placeholder shipping/tax, total)
- [x] "Checkout" dugme → placeholder stranica
- [x] Empty cart state (poruka, continue shopping, featured proizvodi)
- [x] Cart layout varijanta (A klasičan / B dve kolone — iz podešavanja)

### v0.1.33 — Storefront: SEO
- [x] useHead() i useSeoMeta() na svakoj stranici
- [x] Dinamički meta tagovi iz API-ja (product, category)
- [x] Open Graph tagovi (title, description, image)
- [x] Canonical URL-ovi

### v0.1.34 — Storefront: Error stranice
- [x] 404 stranica (poruka, search, popular kategorije, featured proizvodi)
- [x] 500 stranica (friendly poruka, kontakt info)
- [x] Nuxt error.vue handler

### Odloženo — Faza 1
> Stavke koje su preskočene kao manje prioritetne ili zahtevaju dizajn iteracije.

**Admin UI:**
- [x] Drag & drop reorder kategorija — v0.1.19
- [x] Image upload za kategoriju — v0.1.19
- [x] Media picker komponenta (reusable u formama) — v0.1.20

**Storefront UI:**
- [x] Recently viewed (localStorage) — v0.1.28, v0.1.30
- [x] Page builder sekcije iz admin podešavanja — v0.1.28
- [x] Active filters prikaz (sa X, "Clear all") — v0.1.29
- [x] Layout varijanta PLP (A/B/C iz podešavanja) — v0.1.29
- [x] Custom tabovi po proizvodu — v0.1.30
- [x] Responsive tabovi → accordion na mobilnom — v0.1.30
- [x] Sticky tabs navigacija — v0.1.30
- [x] Prev/Next product navigacija — v0.1.30
- [x] Trending searches (admin konfiguriše) — v0.1.31
- [x] Search scope po kategoriji — v0.1.31
- [x] Cart layout varijanta (A/B iz podešavanja) — v0.1.32

---

## v0.2.x — Faza 2: Kupovina & Nalozi

### v0.2.1 — Auth: API
- [x] User registracija endpoint (ime, prezime, email, telefon, password)
- [x] User login endpoint
- [x] User logout endpoint
- [ ] Social login endpoints (Google, Facebook, Apple — Laravel Socialite)
- [x] Forgot password endpoint
- [x] Reset password endpoint
- [x] Email verification endpoint
- [x] Resend verification endpoint
- [x] Get me endpoint
- [x] Update me endpoint
- [x] Update password endpoint
- [x] Delete account endpoint (soft delete + anonimizacija)
- [x] Account lockout (5 pokušaja → 15 min lock)
- [x] Auth testovi

### v0.2.2 — Auth: Storefront
- [x] `/nalog/prijava` — login stranica + login modal opcija
- [x] `/nalog/registracija` — registracija sa password strength meter
- [x] `/nalog/zaboravljena-lozinka` — forgot password
- [ ] Social login dugmad (Google, Facebook, Apple)
- [x] Post-login redirect (vrati gde je bio)
- [x] useAuthStore + useAuth() composable
- [x] Auth middleware (redirect na login)

### v0.2.3 — Auth: Admin 2FA
- [ ] TOTP setup (QR kod + backup kodovi)
- [ ] 2FA verification pri loginu
- [ ] Obavezno za super_admin, opciono za ostale

### v0.2.4 — Account stranice: Storefront
- [x] `/nalog` — dashboard (poslednja narudžbina, brzi linkovi)
- [x] `/nalog/profil` — edit profila, promena lozinke, delete account
- [x] `/nalog/adrese` — CRUD adresa (label, polja, is_default)
- [x] `/nalog/narudzbine` — listing narudžbina
- [x] `/nalog/narudzbine/:number` — detalj (progress bar, stavke, tracking, invoice download)
- [x] `/nalog/newsletter` — subscription toggle
- [x] Account sidebar navigacija

### v0.2.5 — Address: API
- [x] Address model + migracija
- [x] AddressController (CRUD)
- [x] AddressFormRequest
- [x] AddressResource
- [x] Testovi

### v0.2.6 — Cart (server-side upgrade)
- [x] Cart totals breakdown: subtotal, shipping estimate, tax, discount, total
- [x] Coupon field na cart stranici
- [x] Cross-sell sekcija ispod tabele

### v0.2.7 — Checkout: API
- [x] Order model + migracija (11 statusa)
- [x] OrderItem model + migracija
- [x] CheckoutController (validate, create order)
- [x] Order number auto-generisanje
- [x] Stock validacija pri checkout-u
- [x] Guest checkout flow (bez registracije)
- [x] OrderResource, OrderItemResource
- [x] Testovi

### v0.2.8 — Checkout: Storefront
- [x] `/kasa` — single-page checkout (default varijanta A)
- [x] Shipping form (ime, prezime, email, telefon, adresa, grad, poštanski, država)
- [x] Billing vs Shipping checkbox (same by default)
- [ ] Smart defaults (država iz IP geolokacije)
- [ ] Address Autofill (Google Places API)
- [x] Saved addresses dropdown (za ulogovane)
- [x] Order summary sidebar (sticky, edit qty/remove)
- [x] Inline validacija (real-time)
- [x] Persistent cart (sačuvaj pri napuštanju)
- [x] Checkout conversion elementi (order bump, trust badges, testimonial)
- [x] `/kasa/uspeh/:number` — success stranica

### v0.2.9 — Orders: API
- [x] OrderController admin (index, show, updateStatus)
- [x] Order status flow validacija (koji status može preći u koji)
- [x] OrderTimeline model (log svih promena)
- [x] Order notes (interne + vidljive kupcu)
- [x] CustomerController admin (index, show)
- [x] Testovi

### v0.2.10 — Orders: Admin
- [x] `/orders` — listing (filteri: status, datum, kupac, payment; bulk actions; search)
- [x] `/orders/:id` — detail (header, stavke, totali, adrese, timeline, notes, akcije)
- [x] Promena statusa (dropdown + beleška)
- [x] Tracking unos (broj + carrier)
- [x] Refund iniciranje (full/partial)
- [x] Edit order (stavke, adrese — samo pending/processing)
- [ ] Resend email
- [x] `/customers` — listing
- [x] `/customers/:id` — profil sa istorijom

### v0.2.11 — Print / PDF
- [ ] Invoice PDF generisanje (logo, podaci prodavca, PIB, kupac, stavke, totali)
- [ ] Invoice numbering (konfigurabilan format, auto-increment)
- [ ] Packing slip PDF (stavke bez cena)
- [ ] Credit note PDF (za refund)
- [ ] Download iz admin order detail-a
- [ ] Download iz kupac account-a
- [ ] Auto-email invoice kao attachment
- [ ] Bulk print (ZIP sa više invoice-a)
- [ ] Admin podešavanja: branding (logo, boje), legal info (PIB, matični broj, PDV tekst)

### v0.2.12 — Reviews: API
- [x] Review model + migracija (product_id, user_id, rating, title, content, is_verified_purchase, status, admin_reply)
- [x] ReviewController public (index po proizvodu, store)
- [x] ReviewController admin (index, approve/reject, reply)
- [x] ReviewHelpful (glasanje)
- [x] ReviewFormRequest
- [x] ReviewResource
- [x] Testovi

### v0.2.13 — Reviews: Storefront
- [x] PDP Reviews tab (prosečna ocena, distribution chart, lista, sortiranje)
- [x] Review forma (rating zvezdice, naslov, tekst)
- [x] "Was this helpful?" glasanje
- [x] "Verified Purchase" badge

### v0.2.14 — Reviews: Admin
- [x] Reviews listing (filteri: status, rating, proizvod)
- [x] Approve/reject/reply akcije
- [x] Bulk approve/reject
- [ ] Email notifikacija za novu recenziju

### v0.2.15 — Wishlist
- [x] Wishlist pivot tabela (user_id, product_id)
- [x] WishlistController (index, add, remove)
- [x] Wishlist API rute
- [x] Srce ikonica na product card-u i PDP-u
- [x] localStorage za neulogovane, sync pri loginu
- [x] `/nalog/lista-zelja` stranica
- [x] Wishlist badge u headeru
- [ ] Feature flag: FEATURE_WISHLIST

### v0.2.16 — Cross-sell / Up-sell
- [x] Related products (ista kategorija — već u Fazi 1)
- [x] Cross-sell na cart stranici ("Često se kupuje uz...")
- [x] Up-sell na PDP-u ("Možda vas zanima i...")
- [x] Admin: ručno podešavanje related/cross-sell/up-sell po proizvodu

### v0.2.17 — Newsletter
- [x] NewsletterSubscriber model + migracija
- [x] Subscribe/confirm/unsubscribe endpointi
- [x] Double opt-in flow
- [ ] Newsletter popup (modal, exit intent, jednom po sesiji)
- [x] Admin: pregled pretplatnika, export CSV
- [ ] Feature flag: FEATURE_NEWSLETTER

### v0.2.18 — Notify Me (Back in Stock)
- [x] NotifyMe model (product_id, email)
- [x] Subscribe endpoint
- [x] Automatski email kad stock > 0 (queue job)
- [x] Admin: pregled prijavljenih po proizvodu

### v0.2.19 — Blog: API
- [x] Post model + migracija (title, slug, content, excerpt, featured_image, status, published_at, meta)
- [x] BlogCategory model + migracija
- [x] Tag model + migracija
- [x] Pivot tabele (blog_category_post, post_tag)
- [x] BlogController public (index, show)
- [x] BlogController admin (CRUD)
- [x] Scheduled publishing (queue job)
- [x] Testovi

### v0.2.20 — Blog: Storefront
- [x] `/blog` — listing (varijanta iz podešavanja A/B/C)
- [x] `/blog/:slug` — post (varijanta A/B)
- [x] `/blog/kategorija/:slug` — postovi iz kategorije
- [x] `/blog/tag/:slug` — postovi sa tagom
- [x] Sidebar widget-i (recent, kategorije, tag cloud)
- [x] Social share, related posts, reading time, author info

### v0.2.21 — Blog: Admin
- [x] Blog postovi listing + CRUD
- [ ] Rich text editor
- [ ] Featured image + inline slike (media library)
- [x] Kategorije i tagovi management
- [x] Draft/Preview/Scheduled publishing
- [x] SEO polja

### v0.2.22 — Email notifikacije
- [ ] Email branding template (logo, boje, footer)
- [ ] Dobrodošlica (registracija)
- [ ] Reset lozinke
- [ ] Potvrda narudžbine (kupcu)
- [ ] Narudžbina poslata (tracking)
- [ ] Narudžbina isporučena (CTA za recenziju)
- [ ] Narudžbina otkazana
- [ ] Refund
- [ ] Back in stock
- [ ] Newsletter potvrda + dobrodošlica
- [ ] Nova narudžbina (adminu)
- [ ] Low stock alert (adminu)
- [ ] Nova recenzija (adminu)
- [ ] Contact form (adminu)

### v0.2.23 — Request a Call Back
- [x] CallbackRequest model
- [x] Dugme na PDP-u
- [x] Modal forma (proizvod info, telefon, kanal: Call/SMS/WhatsApp)
- [ ] Email notifikacija adminu
- [x] Admin: lista zahteva

### v0.2.24 — API dokumentacija
- [ ] OpenAPI 3.0 spec (annotations na controllerima)
- [ ] Swagger UI na /api/docs
- [ ] Postman collection export

### Odloženo — eksterne zavisnosti
> Stavke iz Faze 2 koje zahtevaju eksterne pakete, API ključeve ili SMTP konfiguraciju.
> Implementirati per-deployment kad budu dostupni kredencijali/servisi.

**Paketi:**
- [ ] Social login — Google, Facebook, Apple (Laravel Socialite + OAuth kredencijali) — v0.2.1, v0.2.2
- [ ] Admin 2FA — TOTP setup, QR kod, backup kodovi (pragmarx/google2fa ili similar) — v0.2.3
- [ ] PDF — Invoice, packing slip, credit note (dompdf ili snappy) — v0.2.11
- [ ] Rich text editor — TipTap ili Quill za blog content editor — v0.2.21
- [ ] API dokumentacija — OpenAPI 3.0 spec + Swagger UI (l5-swagger ili scramble) — v0.2.24
- [ ] Newsletter popup — exit intent modal, jednom po sesiji — v0.2.17

**SMTP (email):**
- [ ] Email branding template (logo, boje, footer) — v0.2.22
- [ ] Dobrodošlica, reset lozinke, potvrda narudžbine — v0.2.22
- [ ] Narudžbina poslata (tracking), isporučena (CTA recenzija) — v0.2.22
- [ ] Otkazivanje, refund, back in stock — v0.2.22
- [ ] Newsletter potvrda + dobrodošlica — v0.2.22
- [ ] Admin notifikacije (nova narudžbina, low stock, nova recenzija) — v0.2.22
- [ ] Callback request email adminu — v0.2.23
- [ ] Nova recenzija email adminu — v0.2.14

**Feature flagovi:**
- [ ] FEATURE_WISHLIST — v0.2.15
- [ ] FEATURE_NEWSLETTER — v0.2.17

---

## v0.3.x — Faza 3: Napredna prodaja

### v0.3.1 — Varijante: API
- [x] Attribute model + migracija (name, type: select/color/image, is_filterable, is_visible_on_card)
- [x] AttributeValue model + migracija (value, color_hex, image_path)
- [x] ProductVariant model + migracija (product_id, sku, price, weight, stock_quantity, is_active)
- [x] product_variant_attribute_value pivot
- [x] product_variant_image pivot
- [x] AttributeController admin (CRUD atributa i vrednosti)
- [x] VariantController admin (CRUD varijanti na proizvodu)
- [x] Public product response sa varijantama
- [x] Testovi

### v0.3.2 — Varijante: Admin
- [x] Atributi management stranica (CRUD, color picker, image upload za swatch)
- [x] Varijante matrica na product formi (auto-generisanje kombinacija)
- [x] Bulk edit cena/stock/SKU
- [x] Disable pojedinačnih varijanti
- [ ] Image mapping po varijanti
- [ ] Quick duplicate varijante

### v0.3.3 — Varijante: Storefront
- [x] PDP: izbor varijante (dropdown/swatch zavisno od type-a)
- [x] Promena slike/cene/stock-a pri izboru
- [x] Nedostupne kombinacije disabled
- [x] Auto-select jedine dostupne varijante
- [x] Deep link (/proizvod/majica?color=red&size=xl)
- [x] Price range na card-u i pre selekcije
- [x] Color/Size swatches na product card-u (PLP)
- [x] Visual filters na PLP-u (slike/swatch umesto teksta)
- [x] Size guide popup (konfigurabilan po kategoriji)
- [ ] Varijanta tabela (opciono, B2B)

### v0.3.4 — Kuponi: API
- [x] Coupon model + migracija (code, type, value, min/max, uses, dates, stacking)
- [x] CouponCondition model
- [x] CouponUsage model
- [x] CouponController admin (CRUD, bulk generate, statistika)
- [x] Coupon validation endpoint
- [x] Coupon apply na checkout-u
- [x] Testovi

### v0.3.5 — Kuponi: Admin + Storefront
- [x] Admin: CRUD kupona sa svim pravilima
- [x] Admin: bulk generate kodova
- [x] Admin: statistika korišćenja
- [x] Coupon polje na cart-u i checkout-u (AJAX validacija)
- [x] Prikaz primenjenog kupona (naziv, popust, X za uklanjanje)
- [x] Error poruke za nevalidne kupone
- [x] Countdown timer na PDP/card-u za akcije

### v0.3.6 — Inventar: API
- [x] StockMovement model + migracija
- [x] Automatsko smanjenje stock-a pri narudžbini
- [x] Automatsko vraćanje pri otkazivanju/return-u
- [x] Stock reservation (15 min pri checkout-u)
- [x] Backorder opcija po proizvodu (restock date polje)
- [ ] Low stock notifikacije (email adminu)
- [x] Testovi

### v0.3.7 — Inventar: Admin
- [x] Inventar listing (proizvod, SKU, stock, status, poslednja promena)
- [x] Inline edit stock-a
- [x] Stock history modal po proizvodu (timeline)
- [x] Bulk stock import (CSV: SKU, quantity, operation)
- [x] Bulk stock export (CSV)
- [x] Bulk manual adjustment
- [x] Low stock dashboard widget

### v0.3.8 — Dostava: API
- [x] ShippingZone model + migracija
- [x] ShippingMethod model + migracija
- [x] Shipment model + migracija (tracking, carrier, status)
- [x] Shipping kalkulacija (flat, weight, price-based, free)
- [x] Checkout: dostupne metode po zoni
- [ ] Tracking status updates
- [x] Testovi

### v0.3.9 — Dostava: Admin + Storefront
- [x] Admin: CRUD zona i metoda
- [ ] Admin: carrier konfiguracija
- [x] Admin: tracking unos na order detail-u
- [x] Checkout: prikaz metoda sa cenom i estimated delivery
- [x] Cart: shipping estimate
- [x] Free shipping progress bar
- [x] `/pracenje/:tracking_number` — tracking stranica
- [ ] Email sa tracking brojem

### v0.3.10 — Plaćanje: API
- [x] PaymentMethod model + migracija
- [x] Payment model + migracija (transaction log)
- [x] Pouzeće (COD) implementacija
- [x] Virman/uplata implementacija (instrukcije, manual confirm)
- [ ] Stripe integracija (Elements, 3D Secure, save card)
- [ ] Refund flow (full/partial, auto za online, manual za offline)
- [ ] Testovi

### v0.3.11 — Plaćanje: Admin + Storefront
- [x] Admin: aktiviranje/deaktiviranje metoda
- [ ] Admin: test/live mode toggle
- [ ] Admin: API credentials (enkriptovano)
- [x] Admin: instrukcije za offline metode
- [x] Admin: COD dodatni trošak
- [x] Admin: pregled transakcija
- [x] Checkout: izbor metode plaćanja
- [ ] Checkout: Stripe Elements inline forma
- [ ] Checkout: save card za buduće kupovine
- [ ] Saved payment methods u account-u (`/nalog/sacuvane-kartice`)

### v0.3.12 — Porezi
- [x] TaxRate model + migracija
- [ ] Tax kalkulacija pri checkout-u
- [x] Admin: konfiguracija stopa
- [ ] Prikaz PDV-a na cart/checkout-u

### v0.3.13 — Abandoned Cart Recovery
- [x] AbandonedCart model + migracija
- [x] Detekcija napuštene korpe (1h timeout)
- [ ] Email serija (1h, 24h, 72h — queue jobs)
- [x] Recovery link (/korpa/obnovi/:token)
- [ ] Auto-generisan kupon u 2. emailu
- [ ] Exit intent popup (konfigurabilan u adminu)
- [x] Admin: lista napuštenih korpi, recovery rate izveštaj
- [ ] Feature flag: FEATURE_ABANDONED_CART

### v0.3.14 — Import/Export
- [x] CSV import proizvoda (sa validacijom, preview, mapping kolona)
- [ ] Image import iz URL-a (queue job)
- [x] Template download (prazan CSV sa header-ima)
- [x] Import history log
- [x] CSV export proizvoda (filter pre exporta)
- [x] CSV export narudžbina
- [x] CSV export kupaca
- [ ] Scheduled export (nedeljni/mesečni na email)

### Odloženo — Faza 3
> Stavke koje zahtevaju eksternu zavisnost, velik UI rad ili SMTP.

**Eksterno:**
- [ ] Stripe integracija (API ključ) — v0.3.10
- [ ] Email sa tracking brojem — v0.3.9
- [ ] Low stock email notifikacije — v0.3.6
- [ ] Scheduled export na email — v0.3.14

**UI-heavy (iterativno sa dizajnom):**
- [x] Varijante matrica + bulk edit na product formi — v0.3.2
- [x] PDP varijante (swatch/dropdown, promena slike/cene) — v0.3.3
- [x] Color/Size swatches na PLP card-u — v0.3.3
- [x] Visual filters (slike/swatch) — v0.3.3
- [x] Size guide popup — v0.3.3
- [x] Countdown timer na PDP/card-u — v0.3.5
- [x] Checkout izbor metode plaćanja (UI) — v0.3.11

**Backend (manja prioritetnost):**
- [x] CouponCondition model (napredna pravila) — v0.3.4
- [x] Stock reservation 15 min — v0.3.6
- [x] Backorder opcija — v0.3.6
- [x] Shipment model — v0.3.8
- [x] Abandoned cart detekcija (timeout job) — v0.3.13
- [x] Import history log — v0.3.14
- [ ] Image import iz URL-a — v0.3.14

---

## v0.4.x — Faza 4: Proširenja

### v0.4.1 — Gift Cards
- [x] GiftCard model + migracija
- [x] GiftCardTransaction model
- [ ] Kupovina (poseban proizvod, izbor iznosa, recipient forma)
- [ ] Email primaocu sa kodom
- [x] Korišćenje na checkout-u (polje za kod, parcijalno korišćenje)
- [x] `/poklon-kartica/provera` — balance check
- [x] Admin: pregled, ručno kreiranje, disable, istorija
- [ ] Feature flag: FEATURE_GIFT_CARDS

### v0.4.2 — Loyalty / Reward Points
- [x] LoyaltyAccount model + migracija
- [x] LoyaltyTransaction model
- [x] Pravila za zarađivanje (kupovina, registracija, recenzija, birthday, referral)
- [x] Korišćenje na checkout-u (slider/input za poene)
- [x] Tier sistem (bronze/silver/gold/platinum)
- [x] `/nalog/poeni` — balance, tier, progress, istorija
- [x] Admin: konfiguracija pravila, pregled po korisniku, ručno dodavanje
- [ ] Expiry pravila
- [ ] Feature flag: FEATURE_LOYALTY

### v0.4.3 — Store Credits
- [x] StoreCreditAccount model + migracija
- [x] StoreCreditTransaction model
- [x] Automatska primena na checkout-u
- [x] `/nalog/krediti` — balance + istorija
- [x] Admin: dodela/oduzimanje sa razlogom
- [ ] Feature flag: FEATURE_STORE_CREDITS

### v0.4.4 — Multi-language (i18n)
- [ ] Nuxt i18n modul setup
- [ ] JSON translation fajlovi za UI stringove
- [ ] Translations kolona/tabela u bazi (Product, Category, Post, Page)
- [ ] Admin: jezički tab-ovi na formama
- [ ] Admin: dostupni jezici podešavanje
- [ ] Indikator kompletnosti prevoda
- [ ] Bulk export/import prevoda (CSV/JSON)
- [ ] hreflang tagovi, sitemap po jeziku, OG locale
- [ ] Feature flag: FEATURE_MULTI_LANGUAGE

### v0.4.5 — Multi-valuta
- [x] Kursna lista (ručno ili API)
- [x] Prekidač valuta u headeru
- [x] Frontend konverzija cena
- [x] Admin: valute i kursevi
- [ ] Feature flag: FEATURE_MULTI_CURRENCY

### v0.4.6 — Product Compare
- [x] Compare lista (localStorage)
- [x] Ikonica na product card-u (feature flag)
- [x] Floating bar sa odabranim proizvodima
- [x] `/uporedi` — side-by-side tabela
- [ ] Feature flag: FEATURE_COMPARE

### v0.4.7 — Social Proof
- [x] "Kupljeno X puta" brojač na PDP-u
- [ ] "X ljudi gleda ovaj proizvod"
- [x] Popup notifikacije ("Marko iz Beograda je upravo kupio...")
- [ ] Admin: konfiguracija, uključeno/isključeno
- [ ] Feature flag: FEATURE_SOCIAL_PROOF

### v0.4.8 — Promo Bar
- [x] Top bar sa promotivnom porukom
- [x] Admin: tekst, boja, link, enable/disable
- [x] Dismiss dugme (localStorage)

### v0.4.9 — Free Shipping Indikator
- [x] Progress bar u korpi/mini-cart
- [x] Admin: threshold konfig
- [x] Poruka pre/posle dostizanja

### v0.4.10 — Shop the Look
- [ ] Look model (slika + hotspots sa product linkovima)
- [ ] Admin: upload slike, postavljanje hotspot-ova
- [ ] `/izgled` stranica + homepage sekcija
- [ ] "Add all to cart" dugme
- [ ] Feature flag: FEATURE_SHOP_THE_LOOK

### v0.4.11 — Store Locator
- [ ] StoreLocation model
- [ ] Mapa (Google Maps / Leaflet) + lista
- [ ] Pretraga po gradu/radius
- [ ] Admin: CRUD lokacija
- [ ] Feature flag: FEATURE_STORE_LOCATOR

### v0.4.12 — Downloadable Products
- [ ] Download link po kupovini (account + email)
- [ ] Admin: upload fajlova, ograničenja (max downloads, expiry)
- [ ] `/nalog/preuzimanja` stranica
- [ ] Feature flag: FEATURE_DOWNLOADS

### v0.4.13 — Izveštaji
- [x] Dashboard overview kartice (prihod, narudžbine, AOV, conversion, novi vs returning)
- [ ] Izveštaj: Prodaja (line chart, po periodu, po payment metodi, po zoni, refund-ovi)
- [ ] Izveštaj: Proizvodi (top po prihodu/qty, most viewed not bought, low/out of stock, dead stock)
- [ ] Izveštaj: Kategorije (prihod po kategoriji)
- [ ] Izveštaj: Kupci (top po potrošnji, novi po periodu, CLV, abandoned cart)
- [ ] Izveštaj: Kuponi (korišćenost, prihod sa/bez)
- [ ] Search analytics UI (top pretrage, pretrage bez rezultata, CTR)
- [ ] Export CSV/PDF
- [ ] Scheduled export na email

### v0.4.14 — Media Library (proširenja)
- [x] Folder organizacija ili tag sistem
- [ ] Prikaz gde je slika korišćena (linked entities)
- [ ] Bulk delete sa upozorenjem
- [ ] Search i filter po tipu, datumu, veličini

### v0.4.15 — Admin korisnici & Permisije
- [x] Permisije po modulu (proizvodi, narudžbine, kupci, podešavanja)
- [x] Admin CRUD (dodaj/edituj/deaktiviraj admina)
- [x] Activity log (ko, šta, kada)
- [x] Permisije UI u admin formi

### v0.4.16 — Admin podešavanja (proširenja)
- [ ] Email tab: sender, template boje, uključivanje/isključivanje emailova
- [ ] Languages tab: dostupni jezici, default, picker pozicija
- [ ] Notifications tab: admin email, low stock threshold, notify on order/review

### v0.4.17 — Statičke stranice
- [x] Page model + migracija (title, slug, content, meta)
- [x] Admin: CRUD stranica (rich text editor)
- [x] `/o-nama`, `/kontakt`, `/uslovi-koriscenja`, `/politika-privatnosti`, `/cesta-pitanja`
- [ ] Kontakt forma sa email notifikacijom

### v0.4.18 — Webhooks
- [x] Webhook model (URL, events, secret, status)
- [x] Event dispatch (order.created, order.status_changed, itd.)
- [x] Retry logika (3 pokušaja, exponential backoff)
- [x] HMAC signature
- [x] Admin: CRUD, event selekcija, log, test dugme
- [ ] Feature flag: FEATURE_WEBHOOKS

### v0.4.19 — API Rate Limiting (proširenja)
- [ ] Per-route limiti (search 120, checkout 10, auth 5)
- [ ] Response headers (X-RateLimit-Limit, Remaining, Retry-After)
- [ ] Admin IP whitelist

### v0.4.20 — Performanse & Tehničko
- [x] sitemap.xml (auto-generisan, po jeziku)
- [x] robots.txt
- [ ] Schema.org markup (Product, BreadcrumbList, Organization, FAQPage)
- [ ] Core Web Vitals optimizacija (LCP < 2.5s, FID < 100ms, CLS < 0.1)
- [ ] API response caching (homepage, categories tree)
- [ ] Database indexi optimizacija
- [ ] Lazy loading slika (native + Nuxt Image)
- [ ] Image optimization (WebP, responsive srcset)
- [ ] Font optimization (preload, font-display: swap)
- [ ] Critical CSS inline
- [ ] Dark mode priprema (CSS varijable)

---

## DevOps (paralelno sa razvojem)

### D.1 — CI/CD
- [ ] GitHub Actions pipeline (lint → test → build → deploy)
- [ ] Staging environment setup (Hetzner dev VPS + Vercel preview)
- [ ] Staging seed data (50+ proizvoda, realistični podaci)
- [ ] Zero-downtime deploy (symlink strategy)
- [ ] Health check endpoint (/api/health)

### D.2 — Monitoring
- [ ] Sentry setup (API + admin + storefront)
- [ ] UptimeRobot / Better Stack setup
- [ ] Slow query log (MySQL)
- [ ] Alerting pravila (error spike, response time, disk, memory, security)

### D.3 — Backup
- [ ] Dnevni automatski DB backup (cron, mysqldump)
- [ ] Upload na eksterni storage (R2/S3)
- [ ] Rotacija (7 dnevnih + 4 nedeljnih + 3 mesečnih)
- [ ] Pre-migration backup
- [ ] Media sync na eksterni storage
- [ ] Mesečni test restore na staging-u

### D.4 — Template kloniranje
- [ ] Feature flags config fajl (config/features.php)
- [ ] useFeature() composable
- [ ] Backend feature() helper + middleware
- [ ] Admin sidebar filtriranje po feature flags
- [ ] Setup checklist dokumentacija za novog klijenta
