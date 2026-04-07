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
- [ ] Public ProductController (index sa filterima, show po slug-u)
- [ ] Public CategoryController (index tree, show sa proizvodima)
- [ ] SearchController (full-text pretraga, logovanje u SearchLog)
- [ ] Registracija javnih API ruta

### v0.1.9 — API: Dashboard
- [ ] DashboardController (ukupno proizvoda, kategorija, aktivnih proizvoda)

### v0.1.10 — API: Cene logika
- [ ] Sale price scope (aktivna samo u periodu sale_price_from/to)
- [ ] Price accessor (vraća sale_price ako je aktivan, inače price)
- [ ] Sale percentage calculator (-X%)
- [ ] Unit price formatting

### v0.1.11 — API: Testovi
- [ ] Admin auth testovi (login, logout, me, invalid credentials, rate limit)
- [ ] Product CRUD testovi (index, store, show, update, destroy, validacija, auth guard)
- [ ] Product images testovi (upload, delete, reorder)
- [ ] Category CRUD testovi (CRUD, tree structure)
- [ ] Public product testovi (listing, filteri, sortiranje, paginacija, show by slug)
- [ ] Public category testovi (tree, show sa proizvodima)
- [ ] Search testovi (pretraga, no results, logovanje)
- [ ] Dashboard testovi
- [ ] Sale price logika testovi

### v0.1.12 — API: Konfiguracija
- [ ] CORS setup (dozvoli admin + storefront domene)
- [ ] API rate limiting middleware (60/min javne, 120/min auth, 5/min login)
- [ ] Standardizovan JSON response format ({ data, message })
- [ ] Standardizovan error format ({ message, errors })
- [ ] Exception handler customization
- [ ] Health check endpoint (/api/health)

### v0.1.13 — Admin: Setup i auth
- [ ] Tailwind + base konfiguracija
- [ ] useApi() composable (base URL, token, error handling, retry)
- [ ] Auth store (Pinia) — useAuthStore
- [ ] useAuth() composable (login, logout, isLoggedIn)
- [ ] Login stranica
- [ ] Auth middleware (redirect na login ako nema token)
- [ ] TypeScript tipovi: Admin, AuthResponse, ApiResponse<T>

### v0.1.14 — Admin: Layout
- [ ] Admin layout komponenta (sidebar + header + main content)
- [ ] Sidebar navigacija (Dashboard, Proizvodi, Kategorije, Media Library, Podešavanja)
- [ ] Header (admin ime, notifikacije bell, logout dugme)
- [ ] Breadcrumbs komponenta
- [ ] Global search u headeru

### v0.1.15 — Admin: UI Atoms
- [ ] Button (variants: primary, secondary, danger, ghost; sizes: sm, md, lg; loading state)
- [ ] Input (label, placeholder, error, disabled, types: text, email, password, number)
- [ ] Badge (variants: success, warning, danger, info, neutral)
- [ ] Icon (wrapper za ikonice)
- [ ] Spinner
- [ ] Checkbox
- [ ] Radio
- [ ] Switch (toggle)
- [ ] Skeleton loader
- [ ] Tooltip
- [ ] Avatar

### v0.1.16 — Admin: UI Molecules
- [ ] Modal (open/close, title, footer actions, focus trap, Escape close)
- [ ] Toast/Notification sistem (success, error, warning, info, auto dismiss, stacking)
- [ ] DataTable (sortiranje, paginacija, loading, empty state)
- [ ] Dropdown (trigger + menu items)
- [ ] Tabs (horizontal, content slot)
- [ ] Accordion
- [ ] QuantitySelector (+/− dugmad)
- [ ] SearchBar (input + clear + submit)
- [ ] FileUpload (drag & drop + browse + preview thumbnails)
- [ ] PriceDisplay (regularna, sale precrtana, badge %)
- [ ] Confirm dialog (modal sa confirm/cancel)

### v0.1.17 — Admin: Dashboard
- [ ] Dashboard stranica sa placeholder statistikama (ukupno proizvoda, kategorija, aktivnih)

### v0.1.18 — Admin: Proizvodi
- [ ] Product listing stranica (DataTable, pretraga, filter po statusu, paginacija)
- [ ] Product create forma (sva polja uključujući cene)
- [ ] Upload slika sa drag & drop i reorder (sortable)
- [ ] Primary image selekcija
- [ ] Kategorije multi-select
- [ ] SEO polja (meta title, meta description)
- [ ] Slug auto-generisanje iz naziva (editabilan)
- [ ] Product edit forma (ista kao create, prefilled)
- [ ] Delete sa confirm modalom
- [ ] Toast notifikacije za uspeh/grešku
- [ ] Form validacija (client-side + server error prikaz)
- [ ] Loading stanja na svim akcijama

### v0.1.19 — Admin: Kategorije
- [ ] Category listing (tree prikaz)
- [ ] Category create/edit (inline edit ili modal)
- [ ] Category delete sa confirm
- [ ] Drag & drop reorder
- [ ] Prikaz broja proizvoda po kategoriji
- [ ] Image upload za kategoriju

### v0.1.20 — Admin: Media Library
- [ ] Media library stranica (grid sa thumbnail-ima)
- [ ] Drag & drop upload (single + bulk)
- [ ] Alt text i title editing
- [ ] Delete sa confirm
- [ ] Media picker komponenta (reusable u product/category formama)

### v0.1.21 — Admin: Podešavanja
- [ ] Settings stranica sa tabovima
- [ ] General tab: naziv, logo, favicon, adresa, telefon, email, social linkovi
- [ ] Storefront layout tab: header varijanta, PLP/PDP/Cart/Blog varijante, products per page
- [ ] Top bar tab: uključen/isključen, tekst, boja
- [ ] Trust & Conversion tab: stock status, urgency bar, countdown, shipping/return/dispatch tekst, trust badges
- [ ] Cart & Checkout tab: add to cart feedback, free shipping threshold, guest checkout
- [ ] Product badges tab: NEW threshold, badge boje
- [ ] SEO tab: default meta title/description pattern, GA ID, FB Pixel ID
- [ ] GDPR tab: cookie consent tekst, privacy/terms linkovi

### v0.1.22 — Storefront: Setup
- [ ] Tailwind + base konfiguracija (boje, fontovi, spacing iz dizajn sistema)
- [ ] useApi() composable (base URL, error handling)
- [ ] useCart() composable + useCartStore (Pinia + localStorage)
- [ ] useFeature() composable (feature flag provera)
- [ ] TypeScript tipovi: Product, Category, ProductImage, CartItem, Cart

### v0.1.23 — Storefront: Layout komponente
- [ ] Header (logo, mega menu, AJAX search, cart ikona sa badge)
- [ ] Mega menu (podkategorije sa slikama/banerima — varijanta A ili B iz podešavanja)
- [ ] Top bar (promo poruka, social ikone — ako uključen u podešavanjima)
- [ ] Footer (4 kolone, social ikone, bottom bar sa payment ikonama)
- [ ] Mobile bottom navigation (home, kategorije, search, korpa, nalog)
- [ ] Back to top dugme
- [ ] Breadcrumbs
- [ ] Cookie consent baner

### v0.1.24 — Storefront: UI Atoms
- [ ] Button (variants, sizes, loading)
- [ ] Input (label, error, disabled)
- [ ] Badge
- [ ] Icon
- [ ] Spinner
- [ ] Skeleton loader

### v0.1.25 — Storefront: UI Molecules
- [ ] SearchBar (AJAX autocomplete dropdown sa proizvodima, kategorijama, recent/trending)
- [ ] QuantitySelector
- [ ] PriceDisplay (regular, sale, unit price)
- [ ] Modal
- [ ] Toast
- [ ] Tabs (horizontal desktop, accordion mobile)
- [ ] Accordion
- [ ] RatingStars (prikaz, ne input — input u Fazi 2)
- [ ] TrustBadges (payment ikone)
- [ ] SocialShare (FB, X, Pinterest, Email, Copy link)
- [ ] Newsletter (email input + dugme)
- [ ] Carousel (reusable, swipe podrška)

### v0.1.26 — Storefront: Product komponente
- [ ] ProductCard (slika, swap hover, naziv, kategorija, cena, rating, badges, add to cart, quick view hover)
- [ ] ProductGrid (responsive: 2 kolone mobile, 3 tablet, 3-4 desktop)
- [ ] ProductPrice (regular, sale precrtana, unit price)
- [ ] ProductGallery (glavna slika + thumbnails, zoom/lightbox, video podrška)
- [ ] ProductCarousel (reusable za featured, related, recently viewed)
- [ ] QuickView modal (slika, naziv, cena, opis, add to cart)

### v0.1.27 — Storefront: Cart komponente
- [ ] CartDrawer (sidebar panel, lista stavki, subtotal, CTA dugmad)
- [ ] CartItem (slika, naziv, cena, quantity +/−, remove, line total)
- [ ] CartTotals (subtotal, placeholder za shipping/tax/discount, total)

### v0.1.28 — Storefront: Homepage
- [ ] Hero slideshow (carousel sa slajdovima — slika, naslov, opis, CTA)
- [ ] Featured proizvodi (carousel)
- [ ] Kategorije grid sa slikama
- [ ] Recently viewed (localStorage)
- [ ] Page builder sekcije iz admin podešavanja (JSON → render komponenti)

### v0.1.29 — Storefront: PLP (Product Listing Page)
- [ ] `/prodavnica` stranica
- [ ] `/kategorija/:slug` stranica
- [ ] Toolbar: result count, per page (9/12/18/24), grid density (2/3/4), grid/list toggle, sort dropdown
- [ ] Sidebar filteri: category tree (expand/collapse + count), price range slider, stock status, rating
- [ ] Active filters prikaz (sa X za uklanjanje, "Clear all")
- [ ] Paginacija + "Učitaj još" opcija
- [ ] Layout varijanta iz podešavanja (A sidebar / B top / C off-canvas)
- [ ] Category header (naslov + opis + breadcrumbs)
- [ ] Empty state (0 rezultata, resetuj filtere)
- [ ] Skeleton loaderi dok se učitava

### v0.1.30 — Storefront: PDP (Product Detail Page)
- [ ] `/proizvod/:slug` stranica
- [ ] Galerija sa zoom/lightbox (varijanta iz podešavanja: A/B/C)
- [ ] Product info: naziv, cena (PriceDisplay), short description, add to cart, quantity selector
- [ ] Trust & Conversion blok (stock status, urgency bar, countdown, shipping/return info, trust badges)
- [ ] Sticky Add to Cart bar (pojavi se na scroll, thumbnail + naziv + rating + cena + dugme)
- [ ] PDP Tabs (opis, specifikacije, shipping & returns — recenzije placeholder za Fazu 2)
- [ ] FAQ tab (accordion Q&A)
- [ ] Custom tabovi po proizvodu (iz admin-a)
- [ ] Responsive tabovi → accordion na mobilnom
- [ ] Sticky tabs navigacija
- [ ] Related products carousel
- [ ] Recently viewed carousel
- [ ] Prev/Next product navigacija
- [ ] Social share blok
- [ ] Breadcrumbs

### v0.1.31 — Storefront: Search
- [ ] `/pretraga?q=` stranica (isti layout kao PLP)
- [ ] AJAX autocomplete u headeru (debounced 300ms)
- [ ] Recent searches (localStorage)
- [ ] Trending searches (admin konfiguriše)
- [ ] Search scope po kategoriji (dropdown)
- [ ] No results state + predlozi

### v0.1.32 — Storefront: Korpa
- [ ] `/korpa` stranica
- [ ] Tabela stavki (slika, naziv, cena, quantity, line total, remove)
- [ ] Cart totals (subtotal, placeholder shipping/tax, total)
- [ ] "Checkout" dugme → placeholder stranica
- [ ] Empty cart state (poruka, continue shopping, featured proizvodi)
- [ ] Cart layout varijanta (A klasičan / B dve kolone — iz podešavanja)

### v0.1.33 — Storefront: SEO
- [ ] useHead() i useSeoMeta() na svakoj stranici
- [ ] Dinamički meta tagovi iz API-ja (product, category)
- [ ] Open Graph tagovi (title, description, image)
- [ ] Canonical URL-ovi

### v0.1.34 — Storefront: Error stranice
- [ ] 404 stranica (poruka, search, popular kategorije, featured proizvodi)
- [ ] 500 stranica (friendly poruka, kontakt info)
- [ ] Nuxt error.vue handler

---

## v0.2.x — Faza 2: Kupovina & Nalozi

### v0.2.1 — Auth: API
- [ ] User registracija endpoint (ime, prezime, email, telefon, password)
- [ ] User login endpoint
- [ ] User logout endpoint
- [ ] Social login endpoints (Google, Facebook, Apple — Laravel Socialite)
- [ ] Forgot password endpoint
- [ ] Reset password endpoint
- [ ] Email verification endpoint
- [ ] Resend verification endpoint
- [ ] Get me endpoint
- [ ] Update me endpoint
- [ ] Update password endpoint
- [ ] Delete account endpoint (soft delete + anonimizacija)
- [ ] Account lockout (5 pokušaja → 15 min lock)
- [ ] Auth testovi

### v0.2.2 — Auth: Storefront
- [ ] `/nalog/prijava` — login stranica + login modal opcija
- [ ] `/nalog/registracija` — registracija sa password strength meter
- [ ] `/nalog/zaboravljena-lozinka` — forgot password
- [ ] Social login dugmad (Google, Facebook, Apple)
- [ ] Post-login redirect (vrati gde je bio)
- [ ] useAuthStore + useAuth() composable
- [ ] Auth middleware (redirect na login)

### v0.2.3 — Auth: Admin 2FA
- [ ] TOTP setup (QR kod + backup kodovi)
- [ ] 2FA verification pri loginu
- [ ] Obavezno za super_admin, opciono za ostale

### v0.2.4 — Account stranice: Storefront
- [ ] `/nalog` — dashboard (poslednja narudžbina, brzi linkovi)
- [ ] `/nalog/profil` — edit profila, promena lozinke, delete account
- [ ] `/nalog/adrese` — CRUD adresa (label, polja, is_default)
- [ ] `/nalog/narudzbine` — listing narudžbina
- [ ] `/nalog/narudzbine/:number` — detalj (progress bar, stavke, tracking, invoice download)
- [ ] `/nalog/newsletter` — subscription toggle
- [ ] Account sidebar navigacija

### v0.2.5 — Address: API
- [ ] Address model + migracija
- [ ] AddressController (CRUD)
- [ ] AddressFormRequest
- [ ] AddressResource
- [ ] Testovi

### v0.2.6 — Cart (server-side upgrade)
- [ ] Cart totals breakdown: subtotal, shipping estimate, tax, discount, total
- [ ] Coupon field na cart stranici
- [ ] Cross-sell sekcija ispod tabele

### v0.2.7 — Checkout: API
- [ ] Order model + migracija (11 statusa)
- [ ] OrderItem model + migracija
- [ ] CheckoutController (validate, create order)
- [ ] Order number auto-generisanje
- [ ] Stock validacija pri checkout-u
- [ ] Guest checkout flow (bez registracije)
- [ ] OrderResource, OrderItemResource
- [ ] Testovi

### v0.2.8 — Checkout: Storefront
- [ ] `/kasa` — single-page checkout (default varijanta A)
- [ ] Shipping form (ime, prezime, email, telefon, adresa, grad, poštanski, država)
- [ ] Billing vs Shipping checkbox (same by default)
- [ ] Smart defaults (država iz IP geolokacije)
- [ ] Address Autofill (Google Places API)
- [ ] Saved addresses dropdown (za ulogovane)
- [ ] Order summary sidebar (sticky, edit qty/remove)
- [ ] Inline validacija (real-time)
- [ ] Persistent cart (sačuvaj pri napuštanju)
- [ ] Checkout conversion elementi (order bump, trust badges, testimonial)
- [ ] `/kasa/uspeh/:number` — success stranica

### v0.2.9 — Orders: API
- [ ] OrderController admin (index, show, updateStatus)
- [ ] Order status flow validacija (koji status može preći u koji)
- [ ] OrderTimeline model (log svih promena)
- [ ] Order notes (interne + vidljive kupcu)
- [ ] CustomerController admin (index, show)
- [ ] Testovi

### v0.2.10 — Orders: Admin
- [ ] `/orders` — listing (filteri: status, datum, kupac, payment; bulk actions; search)
- [ ] `/orders/:id` — detail (header, stavke, totali, adrese, timeline, notes, akcije)
- [ ] Promena statusa (dropdown + beleška)
- [ ] Tracking unos (broj + carrier)
- [ ] Refund iniciranje (full/partial)
- [ ] Edit order (stavke, adrese — samo pending/processing)
- [ ] Resend email
- [ ] `/customers` — listing
- [ ] `/customers/:id` — profil sa istorijom

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
- [ ] Review model + migracija (product_id, user_id, rating, title, content, is_verified_purchase, status, admin_reply)
- [ ] ReviewController public (index po proizvodu, store)
- [ ] ReviewController admin (index, approve/reject, reply)
- [ ] ReviewHelpful (glasanje)
- [ ] ReviewFormRequest
- [ ] ReviewResource
- [ ] Testovi

### v0.2.13 — Reviews: Storefront
- [ ] PDP Reviews tab (prosečna ocena, distribution chart, lista, sortiranje)
- [ ] Review forma (rating zvezdice, naslov, tekst)
- [ ] "Was this helpful?" glasanje
- [ ] "Verified Purchase" badge

### v0.2.14 — Reviews: Admin
- [ ] Reviews listing (filteri: status, rating, proizvod)
- [ ] Approve/reject/reply akcije
- [ ] Bulk approve/reject
- [ ] Email notifikacija za novu recenziju

### v0.2.15 — Wishlist
- [ ] Wishlist pivot tabela (user_id, product_id)
- [ ] WishlistController (index, add, remove)
- [ ] Wishlist API rute
- [ ] Srce ikonica na product card-u i PDP-u
- [ ] localStorage za neulogovane, sync pri loginu
- [ ] `/nalog/lista-zelja` stranica
- [ ] Wishlist badge u headeru
- [ ] Feature flag: FEATURE_WISHLIST

### v0.2.16 — Cross-sell / Up-sell
- [ ] Related products (ista kategorija — već u Fazi 1)
- [ ] Cross-sell na cart stranici ("Često se kupuje uz...")
- [ ] Up-sell na PDP-u ("Možda vas zanima i...")
- [ ] Admin: ručno podešavanje related/cross-sell/up-sell po proizvodu

### v0.2.17 — Newsletter
- [ ] NewsletterSubscriber model + migracija
- [ ] Subscribe/confirm/unsubscribe endpointi
- [ ] Double opt-in flow
- [ ] Newsletter popup (modal, exit intent, jednom po sesiji)
- [ ] Admin: pregled pretplatnika, export CSV
- [ ] Feature flag: FEATURE_NEWSLETTER

### v0.2.18 — Notify Me (Back in Stock)
- [ ] NotifyMe model (product_id, email)
- [ ] Subscribe endpoint
- [ ] Automatski email kad stock > 0 (queue job)
- [ ] Admin: pregled prijavljenih po proizvodu

### v0.2.19 — Blog: API
- [ ] Post model + migracija (title, slug, content, excerpt, featured_image, status, published_at, meta)
- [ ] BlogCategory model + migracija
- [ ] Tag model + migracija
- [ ] Pivot tabele (blog_category_post, post_tag)
- [ ] BlogController public (index, show)
- [ ] BlogController admin (CRUD)
- [ ] Scheduled publishing (queue job)
- [ ] Testovi

### v0.2.20 — Blog: Storefront
- [ ] `/blog` — listing (varijanta iz podešavanja A/B/C)
- [ ] `/blog/:slug` — post (varijanta A/B)
- [ ] `/blog/kategorija/:slug` — postovi iz kategorije
- [ ] `/blog/tag/:slug` — postovi sa tagom
- [ ] Sidebar widget-i (recent, kategorije, tag cloud)
- [ ] Social share, related posts, reading time, author info

### v0.2.21 — Blog: Admin
- [ ] Blog postovi listing + CRUD
- [ ] Rich text editor
- [ ] Featured image + inline slike (media library)
- [ ] Kategorije i tagovi management
- [ ] Draft/Preview/Scheduled publishing
- [ ] SEO polja

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
- [ ] CallbackRequest model
- [ ] Dugme na PDP-u
- [ ] Modal forma (proizvod info, telefon, kanal: Call/SMS/WhatsApp)
- [ ] Email notifikacija adminu
- [ ] Admin: lista zahteva

### v0.2.24 — API dokumentacija
- [ ] OpenAPI 3.0 spec (annotations na controllerima)
- [ ] Swagger UI na /api/docs
- [ ] Postman collection export

---

## v0.3.x — Faza 3: Napredna prodaja

### v0.3.1 — Varijante: API
- [ ] Attribute model + migracija (name, type: select/color/image, is_filterable, is_visible_on_card)
- [ ] AttributeValue model + migracija (value, color_hex, image_path)
- [ ] ProductVariant model + migracija (product_id, sku, price, weight, stock_quantity, is_active)
- [ ] product_variant_attribute_value pivot
- [ ] product_variant_image pivot
- [ ] AttributeController admin (CRUD atributa i vrednosti)
- [ ] VariantController admin (CRUD varijanti na proizvodu)
- [ ] Public product response sa varijantama
- [ ] Testovi

### v0.3.2 — Varijante: Admin
- [ ] Atributi management stranica (CRUD, color picker, image upload za swatch)
- [ ] Varijante matrica na product formi (auto-generisanje kombinacija)
- [ ] Bulk edit cena/stock/SKU
- [ ] Disable pojedinačnih varijanti
- [ ] Image mapping po varijanti
- [ ] Quick duplicate varijante

### v0.3.3 — Varijante: Storefront
- [ ] PDP: izbor varijante (dropdown/swatch zavisno od type-a)
- [ ] Promena slike/cene/stock-a pri izboru
- [ ] Nedostupne kombinacije disabled
- [ ] Auto-select jedine dostupne varijante
- [ ] Deep link (/proizvod/majica?color=red&size=xl)
- [ ] Price range na card-u i pre selekcije
- [ ] Color/Size swatches na product card-u (PLP)
- [ ] Visual filters na PLP-u (slike/swatch umesto teksta)
- [ ] Size guide popup (konfigurabilan po kategoriji)
- [ ] Varijanta tabela (opciono, B2B)

### v0.3.4 — Kuponi: API
- [ ] Coupon model + migracija (code, type, value, min/max, uses, dates, stacking)
- [ ] CouponCondition model
- [ ] CouponUsage model
- [ ] CouponController admin (CRUD, bulk generate, statistika)
- [ ] Coupon validation endpoint
- [ ] Coupon apply na checkout-u
- [ ] Testovi

### v0.3.5 — Kuponi: Admin + Storefront
- [ ] Admin: CRUD kupona sa svim pravilima
- [ ] Admin: bulk generate kodova
- [ ] Admin: statistika korišćenja
- [ ] Coupon polje na cart-u i checkout-u (AJAX validacija)
- [ ] Prikaz primenjenog kupona (naziv, popust, X za uklanjanje)
- [ ] Error poruke za nevalidne kupone
- [ ] Countdown timer na PDP/card-u za akcije

### v0.3.6 — Inventar: API
- [ ] StockMovement model + migracija
- [ ] Automatsko smanjenje stock-a pri narudžbini
- [ ] Automatsko vraćanje pri otkazivanju/return-u
- [ ] Stock reservation (15 min pri checkout-u)
- [ ] Backorder opcija po proizvodu (restock date polje)
- [ ] Low stock notifikacije (email adminu)
- [ ] Testovi

### v0.3.7 — Inventar: Admin
- [ ] Inventar listing (proizvod, SKU, stock, status, poslednja promena)
- [ ] Inline edit stock-a
- [ ] Stock history modal po proizvodu (timeline)
- [ ] Bulk stock import (CSV: SKU, quantity, operation)
- [ ] Bulk stock export (CSV)
- [ ] Bulk manual adjustment
- [ ] Low stock dashboard widget

### v0.3.8 — Dostava: API
- [ ] ShippingZone model + migracija
- [ ] ShippingMethod model + migracija
- [ ] Shipment model + migracija (tracking, carrier, status)
- [ ] Shipping kalkulacija (flat, weight, price-based, free)
- [ ] Checkout: dostupne metode po zoni
- [ ] Tracking status updates
- [ ] Testovi

### v0.3.9 — Dostava: Admin + Storefront
- [ ] Admin: CRUD zona i metoda
- [ ] Admin: carrier konfiguracija
- [ ] Admin: tracking unos na order detail-u
- [ ] Checkout: prikaz metoda sa cenom i estimated delivery
- [ ] Cart: shipping estimate
- [ ] Free shipping progress bar
- [ ] `/pracenje/:tracking_number` — tracking stranica
- [ ] Email sa tracking brojem

### v0.3.10 — Plaćanje: API
- [ ] PaymentMethod model + migracija
- [ ] Payment model + migracija (transaction log)
- [ ] Pouzeće (COD) implementacija
- [ ] Virman/uplata implementacija (instrukcije, manual confirm)
- [ ] Stripe integracija (Elements, 3D Secure, save card)
- [ ] Refund flow (full/partial, auto za online, manual za offline)
- [ ] Testovi

### v0.3.11 — Plaćanje: Admin + Storefront
- [ ] Admin: aktiviranje/deaktiviranje metoda
- [ ] Admin: test/live mode toggle
- [ ] Admin: API credentials (enkriptovano)
- [ ] Admin: instrukcije za offline metode
- [ ] Admin: COD dodatni trošak
- [ ] Admin: pregled transakcija
- [ ] Checkout: izbor metode plaćanja
- [ ] Checkout: Stripe Elements inline forma
- [ ] Checkout: save card za buduće kupovine
- [ ] Saved payment methods u account-u (`/nalog/sacuvane-kartice`)

### v0.3.12 — Porezi
- [ ] TaxRate model + migracija
- [ ] Tax kalkulacija pri checkout-u
- [ ] Admin: konfiguracija stopa
- [ ] Prikaz PDV-a na cart/checkout-u

### v0.3.13 — Abandoned Cart Recovery
- [ ] AbandonedCart model + migracija
- [ ] Detekcija napuštene korpe (1h timeout)
- [ ] Email serija (1h, 24h, 72h — queue jobs)
- [ ] Recovery link (/korpa/obnovi/:token)
- [ ] Auto-generisan kupon u 2. emailu
- [ ] Exit intent popup (konfigurabilan u adminu)
- [ ] Admin: lista napuštenih korpi, recovery rate izveštaj
- [ ] Feature flag: FEATURE_ABANDONED_CART

### v0.3.14 — Import/Export
- [ ] CSV import proizvoda (sa validacijom, preview, mapping kolona)
- [ ] Image import iz URL-a (queue job)
- [ ] Template download (prazan CSV sa header-ima)
- [ ] Import history log
- [ ] CSV export proizvoda (filter pre exporta)
- [ ] CSV export narudžbina
- [ ] CSV export kupaca
- [ ] Scheduled export (nedeljni/mesečni na email)

---

## v0.4.x — Faza 4: Proširenja

### v0.4.1 — Gift Cards
- [ ] GiftCard model + migracija
- [ ] GiftCardTransaction model
- [ ] Kupovina (poseban proizvod, izbor iznosa, recipient forma)
- [ ] Email primaocu sa kodom
- [ ] Korišćenje na checkout-u (polje za kod, parcijalno korišćenje)
- [ ] `/poklon-kartica/provera` — balance check
- [ ] Admin: pregled, ručno kreiranje, disable, istorija
- [ ] Feature flag: FEATURE_GIFT_CARDS

### v0.4.2 — Loyalty / Reward Points
- [ ] LoyaltyAccount model + migracija
- [ ] LoyaltyTransaction model
- [ ] Pravila za zarađivanje (kupovina, registracija, recenzija, birthday, referral)
- [ ] Korišćenje na checkout-u (slider/input za poene)
- [ ] Tier sistem (bronze/silver/gold/platinum)
- [ ] `/nalog/poeni` — balance, tier, progress, istorija
- [ ] Admin: konfiguracija pravila, pregled po korisniku, ručno dodavanje
- [ ] Expiry pravila
- [ ] Feature flag: FEATURE_LOYALTY

### v0.4.3 — Store Credits
- [ ] StoreCreditAccount model + migracija
- [ ] StoreCreditTransaction model
- [ ] Automatska primena na checkout-u
- [ ] `/nalog/krediti` — balance + istorija
- [ ] Admin: dodela/oduzimanje sa razlogom
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
- [ ] Kursna lista (ručno ili API)
- [ ] Prekidač valuta u headeru
- [ ] Frontend konverzija cena
- [ ] Admin: valute i kursevi
- [ ] Feature flag: FEATURE_MULTI_CURRENCY

### v0.4.6 — Product Compare
- [ ] Compare lista (localStorage)
- [ ] Ikonica na product card-u (feature flag)
- [ ] Floating bar sa odabranim proizvodima
- [ ] `/uporedi` — side-by-side tabela
- [ ] Feature flag: FEATURE_COMPARE

### v0.4.7 — Social Proof
- [ ] "Kupljeno X puta" brojač na PDP-u
- [ ] "X ljudi gleda ovaj proizvod"
- [ ] Popup notifikacije ("Marko iz Beograda je upravo kupio...")
- [ ] Admin: konfiguracija, uključeno/isključeno
- [ ] Feature flag: FEATURE_SOCIAL_PROOF

### v0.4.8 — Promo Bar
- [ ] Top bar sa promotivnom porukom
- [ ] Admin: tekst, boja, link, enable/disable
- [ ] Dismiss dugme (localStorage)

### v0.4.9 — Free Shipping Indikator
- [ ] Progress bar u korpi/mini-cart
- [ ] Admin: threshold konfig
- [ ] Poruka pre/posle dostizanja

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
- [ ] Dashboard overview kartice (prihod, narudžbine, AOV, conversion, novi vs returning)
- [ ] Izveštaj: Prodaja (line chart, po periodu, po payment metodi, po zoni, refund-ovi)
- [ ] Izveštaj: Proizvodi (top po prihodu/qty, most viewed not bought, low/out of stock, dead stock)
- [ ] Izveštaj: Kategorije (prihod po kategoriji)
- [ ] Izveštaj: Kupci (top po potrošnji, novi po periodu, CLV, abandoned cart)
- [ ] Izveštaj: Kuponi (korišćenost, prihod sa/bez)
- [ ] Search analytics UI (top pretrage, pretrage bez rezultata, CTR)
- [ ] Export CSV/PDF
- [ ] Scheduled export na email

### v0.4.14 — Media Library (proširenja)
- [ ] Folder organizacija ili tag sistem
- [ ] Prikaz gde je slika korišćena (linked entities)
- [ ] Bulk delete sa upozorenjem
- [ ] Search i filter po tipu, datumu, veličini

### v0.4.15 — Admin korisnici & Permisije
- [ ] Permisije po modulu (proizvodi, narudžbine, kupci, podešavanja)
- [ ] Admin CRUD (dodaj/edituj/deaktiviraj admina)
- [ ] Activity log (ko, šta, kada)
- [ ] Permisije UI u admin formi

### v0.4.16 — Admin podešavanja (proširenja)
- [ ] Email tab: sender, template boje, uključivanje/isključivanje emailova
- [ ] Languages tab: dostupni jezici, default, picker pozicija
- [ ] Notifications tab: admin email, low stock threshold, notify on order/review

### v0.4.17 — Statičke stranice
- [ ] Page model + migracija (title, slug, content, meta)
- [ ] Admin: CRUD stranica (rich text editor)
- [ ] `/o-nama`, `/kontakt`, `/uslovi-koriscenja`, `/politika-privatnosti`, `/cesta-pitanja`
- [ ] Kontakt forma sa email notifikacijom

### v0.4.18 — Webhooks
- [ ] Webhook model (URL, events, secret, status)
- [ ] Event dispatch (order.created, order.status_changed, itd.)
- [ ] Retry logika (3 pokušaja, exponential backoff)
- [ ] HMAC signature
- [ ] Admin: CRUD, event selekcija, log, test dugme
- [ ] Feature flag: FEATURE_WEBHOOKS

### v0.4.19 — API Rate Limiting (proširenja)
- [ ] Per-route limiti (search 120, checkout 10, auth 5)
- [ ] Response headers (X-RateLimit-Limit, Remaining, Retry-After)
- [ ] Admin IP whitelist

### v0.4.20 — Performanse & Tehničko
- [ ] sitemap.xml (auto-generisan, po jeziku)
- [ ] robots.txt
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
