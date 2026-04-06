# Webshop Template — Projektna specifikacija

## 1. Pregled & Arhitektura

CMS-nezavisan webshop template koji služi kao baza za brzo prilagođavanje klijentima. Potpuno custom rešenje, bez WooCommerce, Magento i sličnih platformi.

### Tech stack

| Sloj | Tehnologija |
|------|------------|
| Backend API | Laravel 11 (PHP 8.2+), headless / API-only |
| Auth | Laravel Sanctum (odvojeni guard za admin i kupce) |
| Baza | MySQL 8 |
| Storefront | Nuxt 3 (Vue 3, SSR režim) |
| Admin panel | Nuxt 3 (Vue 3, SPA režim) |
| Stilizacija | Tailwind CSS 3 |
| File storage | Laravel Storage (local → later S3) |
| Queue / Jobs | Laravel Queue (database driver → later Redis) |
| Email | Laravel Mail (Mailtrap za dev) |

### Mono-repo struktura

```
webshop-template/
├── api/                  # Laravel 11 API-only
├── storefront/           # Nuxt 3 (SSR) — kupac vidi ovo
├── admin/                # Nuxt 3 (SPA) — admin panel
└── docs/                 # Dokumentacija
```

Tri odvojena projekta u jednom mono-repo-u. API komunicira sa oba frontenda putem REST JSON API-ja. Admin i storefront imaju potpuno odvojen login, izgled i routing.

---

## 2. Tipovi stranica & Varijante

Sve storefront stranice dele se na dva tipa prema načinu na koji admin kontroliše layout.

### Tip 1: Tipske stranice (fiksni elementi, varijante layouta)

Stranice sa definisanim setom elemenata — admin bira varijantu (layout preset) u podešavanjima, ali ne dodaje/briše sekcije.

#### Header / Navigacija
Elementi: logo, search bar (sa category dropdown opciono), auth linkovi, wishlist/compare/cart ikone, navigacija kategorija

| Varijanta | Opis |
|-----------|------|
| **A — Horizontalni mega menu** | Kategorije u header baru, dropdown sa podkategorijama, slikama i promo banerima. Čist, moderan izgled |
| **B — Vertikalni sidebar + mega menu** | "Browse Categories" dugme otvara vertikalnu listu sa ikonama levo na homepage-u, ostatak prostora za hero/banere. Na ostalim stranicama se sklapa u dropdown. Horizontalni menu ostaje za ostale linkove (Home, Shop, Blog, itd.) |

Search bar opciono ima "Select Category" dropdown za filtriranje pretrage po kategoriji.

Top bar (iznad headera, opciono): social ikone, kontakt linkovi (Newsletter, Contact Us, FAQs), promo poruka (free shipping), language/country picker. Konfigurabilan u admin podešavanjima — može se uključiti/isključiti.

#### PLP — Product Listing Page (`/prodavnica`, `/kategorija/:slug`, `/pretraga`)
Elementi: filteri, sortiranje, product grid/list, paginacija, breadcrumbs, category header

**Toolbar (iznad grida):**
- "Showing all X results" — result count
- "Show: 9 / 12 / 18 / 24" — products per page selector
- Grid density toggle (2 / 3 / 4 kolone) + list view ikone
- Sort by dropdown (popularity, rating, price low→high, price high→low, newest, name A→Z)

**Category header:**
- Naslov kategorije + opis (ako postoji)
- Breadcrumbs: Home / Parent Category / Current Category

**Filteri:**
- Category tree sa expand/collapse i brojem proizvoda po kategoriji (npr. "Bakery (32)")
- Price range slider (min–max, dual handle)
- Stock status filter (In stock / Out of stock)
- Rating filter (po broju zvezdica)
- Custom attribute filteri (dinamički iz Faze 3 — boja, veličina, brend, itd.)
- Active filters: prikaz izabranih filtera sa X za uklanjanje, "Clear all" dugme

| Varijanta | Opis |
|-----------|------|
| **A — Sidebar levo** | Filteri u levom sidebaru, grid desno (klasičan e-commerce) |
| **B — Top filteri** | Filteri horizontalno iznad grida, cela širina za proizvode |
| **C — Off-canvas filteri** | Dugme "Filter" otvara filter drawer sa desne strane, grid koristi punu širinu. Category header (naslov + opis) iznad grida. Najbolji za minimalistički/luxury izgled |

#### PDP — Product Detail Page (`/proizvod/:slug`)
Elementi: galerija, naziv, cena, opis, add to cart, varijante, tabs (opis/specifikacije/recenzije), related products, breadcrumbs

| Varijanta | Opis |
|-----------|------|
| **A — Klasičan** | Galerija levo (thumbnails ispod), info desno, tabs ispod |
| **B — Sticky info** | Galerija levo, info desno sa sticky scroll-om, široka galerija |
| **C — Full-width galerija** | Galerija preko cele širine gore, info ispod u dve kolone |

#### Cart — Korpa (`/korpa`)
Elementi: tabela stavki, količina, subtotal, kupon polje, totali, CTA dugmad, cross-sell

| Varijanta | Opis |
|-----------|------|
| **A — Klasičan** | Tabela stavki gore, totali desno, cross-sell ispod |
| **B — Dve kolone** | Stavke levo (veći prostor), sticky sidebar desno sa totalima i CTA |

#### Checkout (`/kasa`)
Elementi: podaci kupca, adresa dostave, metoda dostave, plaćanje, rezime narudžbine, kupon

| Varijanta | Opis |
|-----------|------|
| **A — Single-page (default)** | Sve na jednoj stranici, dve kolone — forma levo, rezime desno (sticky). Sekcije u accordion stilu |
| **B — Multi-step** | Korak po korak (podaci → dostava → plaćanje → potvrda), progress bar. Alternativa za klijente koji preferiraju korake |

#### Blog listing (`/blog`)
Elementi: post card (slika, naslov, excerpt, datum, autor, tag), paginacija, sidebar

| Varijanta | Opis |
|-----------|------|
| **A — Grid + sidebar** | Postovi u gridu (2 kolone), sidebar desno sa kategorijama/tagovima |
| **B — Full-width grid** | Postovi u gridu (3 kolone), bez sidebara |
| **C — Lista** | Postovi kao horizontalne kartice (slika levo, tekst desno), full width |

#### Blog post (`/blog/:slug`)
Elementi: featured image, naslov, datum, autor, sadržaj, tagovi, share dugmad, related posts

| Varijanta | Opis |
|-----------|------|
| **A — Sa sidebarom** | Sadržaj levo, sidebar desno (recent posts, kategorije) |
| **B — Centered** | Sadržaj centriran (uži, čitljiviji), bez sidebara, related posts ispod |

#### Customer account (`/nalog/*`)
Jedan layout: sidebar levo sa navigacijom (profil, adrese, narudžbine, wishlist), sadržaj desno. Nema varijanti — konzistentan kroz sve account stranice.

#### Implementacija varijanti u adminu
Admin → Podešavanja → Layout — dropdown za svaku tipsku stranicu (npr. "PLP layout: A / B / C"). Globalno podešavanje, primenjuje se na sve stranice tog tipa.

---

### Tip 2: Custom stranice (slobodan raspored sekcija)

Stranice gde admin bira i ređa sekcije iz biblioteke dostupnih blokova — page-builder pristup.

**Koristi se za:** Homepage (`/`) i Landing stranice (`/stranica/:slug`)

#### Biblioteka sekcija

| Sekcija | Opis |
|---------|------|
| **Hero Slideshow** | Carousel sa slikama, naslovom, opisom, CTA dugmetom |
| **Category Grid** | Grid kategorija sa slikama (2, 3, 4 ili 6 kolona) |
| **Featured Products** | Carousel ili grid izabranih proizvoda |
| **New Arrivals** | Poslednje dodati proizvodi (carousel) |
| **On Sale Products** | Proizvodi sa sniženom cenom |
| **Banner — Full Width** | Jedna slika sa tekstom i CTA |
| **Banner — Split (2 kolone)** | Dva banera jedan pored drugog |
| **Banner — Triple (3 kolone)** | Tri banera u redu |
| **Product Tabs** | Tab-ovi sa grupama proizvoda (npr. "Popularno / Novo / Na akciji") |
| **Testimonials** | Carousel sa recenzijama kupaca |
| **Newsletter CTA** | Veći signup blok sa pozadinom i tekstom |
| **Brands / Partners** | Logo carousel |
| **USP Bar** | Ikone sa prednostima (besplatna dostava, garancija, podrška 24/7) |
| **Blog Preview** | Poslednji blog postovi (3 kartice) |
| **Rich Text** | Slobodan tekst blok (WYSIWYG) |
| **Video** | Embed YouTube/Vimeo ili self-hosted video |
| **Image + Text** | Slika sa jedne strane, tekst sa druge (levo/desno konfigurabilan) |
| **Countdown Timer** | Odbrojavanje do datuma (kraj akcije, lansiranje) sa CTA dugmetom |
| **Recently Viewed** | Poslednje gledani proizvodi korisnika (iz localStorage) |
| **Instagram Feed** | Grid sa poslednjim Instagram postovima (API integracija ili embed) |
| **Social Block** | Social media linkovi sa ikonama |
| **Spacer** | Prazan prostor (konfigurabilan) |

#### Implementacija u adminu
- Admin → Stranice → Homepage / Nova stranica
- Drag & drop interfejs za dodavanje, brisanje, reorder sekcija
- Svaka sekcija ima svoja podešavanja (tekst, slike, linkovi, broj kolona, itd.)
- Preview pre publish-a
- Čuva se kao JSON niz sekcija u bazi:
```json
[
  { "type": "hero_slideshow", "data": { "slides": [...] } },
  { "type": "category_grid", "data": { "columns": 3, "categories": [...] } },
  { "type": "featured_products", "data": { "title": "Izdvajamo", "product_ids": [...] } }
]
```

---

## 3. UI/UX Standardi

### Dizajn principi
- **Clean i minimalistički** — beli prostor je feature, ne prazan prostor
- **Konzistentnost** — isti element izgleda isto svuda. Nema "malo drugačije dugme na ovoj stranici"
- **Content-first** — proizvod je zvezda, UI mu ne sme smetati
- **Mobile-first** — dizajniraj za mobile, pa proširi za desktop
- **Feedback na svaku akciju** — korisnik uvek mora znati šta se dešava (loading, success, error)
- **Jedan primarni CTA po sekciji** — jasna hijerarhija akcija

### Dizajn sistem (Tailwind baziran)

#### Paleta boja (template default)

Ocean paleta — konfigurabilan u `tailwind.config` po klijentu:

```
--deep-twilight:    #03045e   ← najtamnija, za headings, footer bg
--french-blue:      #023e8a   ← tamni akcent, hover na primary
--bright-teal-blue: #0077b6   ← PRIMARY — CTA dugmad, aktivni elementi, linkovi
--blue-green:       #0096c7   ← secondary, hover states
--turquoise-surf:   #00b4d8   ← akcenti, ikone, active states
--sky-aqua:         #48cae4   ← light akcent, badges, tags
--frosted-blue:     #90e0ef   ← pozadine sekcija, hover bg
--frosted-blue-2:   #ade8f4   ← svetlija pozadina, card hover
--light-cyan:       #caf0f8   ← najsvetlija, subtle bg, input focus ring
```

Mapiranje na Tailwind:
- **Primary:** `#0077b6` (bright-teal-blue) — CTA dugmad, linkovi, aktivni elementi
- **Primary hover:** `#023e8a` (french-blue) — hover/active state na primary
- **Primary light:** `#caf0f8` (light-cyan) — focus ring, selected bg, badge bg
- **Secondary:** `#0096c7` (blue-green) — sekundarni CTA, manje bitne akcije
- **Dark:** `#03045e` (deep-twilight) — headings, footer bg, tamni tekst
- **Neutral:** Tailwind default gray skala (50-950) — tekst, borderi, pozadine
- **Success:** `#16a34a` (green-600) — stock in, uspeh, potvrda
- **Warning:** `#d97706` (amber-600) — low stock, upozorenja
- **Danger:** `#dc2626` (red-600) — error, out of stock, delete, required
- **Background:** `#ffffff` body, `#f8fafc` (slate-50) za sekcije, `#caf0f8` za accent sekcije

#### Ikone
- **Set:** Lucide (`lucide-vue-next`)
- Lightweight, konzistentne, odlična pokrivenost za e-commerce: ShoppingCart, Heart, Search, Filter, Truck, CreditCard, Package, Star, ChevronDown, X, Menu, User, MapPin, Phone, Mail, Eye, Trash, Edit, Plus, Minus
- Veličine: 16px (inline), 20px (default), 24px (large), 32px (hero)
- Stroke width: 2px (default), 1.5px za veće ikone

#### Tipografija
- **Font:** Quicksand (Google Fonts) — za headings I body
- **Fallback:** system font stack (`-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`)
- **Veličine:** Tailwind default scale (text-xs do text-6xl)
- **Hijerarhija:** h1 samo jednom po stranici, h2 za sekcije, h3 za podsekcije
- **Line height:** relaxed za body tekst (1.6-1.75), tight za headings (1.1-1.3)
- **Font weight:** regular (400) za body, medium (500) za labele, semibold (600) za headings, bold (700) za CTA i cene
- **Loading:** preload, `font-display: swap`

#### Spacing
- Tailwind default scale — 4px grid (p-1 = 4px, p-2 = 8px, p-4 = 16px...)
- Sekcije na stranici: `py-12` do `py-20` (48-80px vertikalno)
- Card padding: `p-4` do `p-6`
- Gap između elemenata: `gap-4` do `gap-8`
- Konzistentnost: ne mešati custom px vrednosti sa Tailwind skalom

#### Border radius — OŠTRI UGLOVI
- Dugmad: `rounded-none` (0px) ili `rounded-sm` (2px) — oštro
- Kartice: `rounded-none` (0px)
- Inputi: `rounded-none` (0px) ili `rounded-sm` (2px)
- Badges: `rounded-sm` (2px) — ne pill, oštri
- Modali: `rounded-none` (0px)
- Jedini izuzetak: avatari i ikone ostaju `rounded-full`

#### Shadows
- Kartice: `shadow-sm` default, `shadow-md` na hover
- Modali/Draweri: `shadow-2xl`
- Dropdown-i: `shadow-lg`
- Sticky header: `shadow-sm` kad je scrolled

### Responsive & Breakpoints

#### Breakpoints (Tailwind default)
| Breakpoint | Min width | Upotreba |
|------------|-----------|----------|
| `sm` | 640px | Veliki telefoni landscape |
| `md` | 768px | Tableti portrait |
| `lg` | 1024px | Tableti landscape / mali laptopi |
| `xl` | 1280px | Desktop |
| `2xl` | 1536px | Veliki monitori |

#### Šta se menja po breakpoint-u

**Mobile (< 640px):**
- Hamburger menu umesto mega menija
- Product grid: 2 kolone
- Bottom navigation bar (home, kategorije, search, cart, account)
- Filteri: off-canvas drawer (ne sidebar)
- PDP: galerija full-width iznad info-a (vertikalno)
- Cart drawer: full-screen
- Footer: accordion sekcije
- Search: full-screen overlay
- Sticky add-to-cart: kompaktan (cena + dugme)
- Hover akcije: zamenjene sa vidljivim ikonama (nema hover-a na touch)
- Quick view: disabled (direktan link na PDP)

**Tablet (640px - 1024px):**
- Product grid: 3 kolone
- Sidebar filteri ili top filteri (zavisi od varijante)
- PDP: galerija i info side-by-side ali uži
- Mega menu: funkcioniše ali sa manjim slikama

**Desktop (> 1024px):**
- Puni layout sa svim elementima
- Hover efekti aktivni
- Mega menu sa podkategorijama i banerima
- Product grid: 3-4 kolone (konfigurabilno)

#### Max width
- Sadržaj u kontejneru: `max-w-7xl` (1280px) centriran
- Full-width sekcije: baneri, hero — puna širina, sadržaj unutra u kontejneru

### Micro-interactions & Animacije

#### Principi
- Animacije su **funkcionalne** — pomažu korisniku da razume šta se dešava, ne služe za dekoraciju
- Brze: 150-300ms za većinu interakcija, max 500ms za kompleksne transicije
- Easing: `ease-out` za ulazne, `ease-in` za izlazne animacije
- Redukovane animacije za korisnike koji preferiraju: `@media (prefers-reduced-motion: reduce)`

#### Konkretne animacije

| Element | Animacija | Trajanje |
|---------|-----------|----------|
| **Button hover** | Background color transition | 150ms |
| **Button click** | Scale down (0.97) pa nazad | 100ms |
| **Button loading** | Spinner zameni tekst, dugme disabled + opacity | — |
| **Card hover** | Shadow increase + subtle lift (translateY -2px) | 200ms |
| **Image hover swap** | Crossfade na drugu sliku | 300ms |
| **Drawer open** | Slide in sa desne strane + overlay fade | 300ms |
| **Drawer close** | Slide out + overlay fade | 200ms |
| **Modal open** | Scale from 0.95 + fade in + overlay | 200ms |
| **Modal close** | Scale to 0.95 + fade out | 150ms |
| **Dropdown open** | Scale Y from top + fade | 150ms |
| **Toast appear** | Slide in from top-right + fade | 300ms |
| **Toast dismiss** | Fade out + slide right | 200ms |
| **Skeleton loader** | Pulse animacija (opacity 0.5 → 1) | looping |
| **Add to Cart** | Konfigurabilan: drawer slide / toast / fly-to-cart | 300-500ms |
| **Page scroll** | Smooth scroll za anchor linkove | native |
| **Accordion open** | Height auto transition + rotate chevron | 200ms |
| **Tab switch** | Content fade in | 150ms |
| **Badge count change** | Scale bounce (1 → 1.2 → 1) | 300ms |
| **Sticky header** | Slide down on scroll up, hide on scroll down | 300ms |
| **Back to top** | Fade in posle 300px scroll-a | 200ms |
| **Image zoom (PDP)** | Scale transform na hover / pinch on mobile | realtime |
| **Price change (varijanta)** | Crossfade stara → nova cena | 200ms |

### Toast / Notification sistem

- **Pozicija:** top-right (desktop), top-center (mobile)
- **Tipovi:** success (zelena), error (crvena), warning (žuta), info (plava)
- **Auto dismiss:** success/info: 3s, warning: 5s, error: manual dismiss
- **Stacking:** max 3 toast-a vidljiva, novi guraju stare gore
- **Sadržaj:** ikona + kratak tekst + opcioni action link + X dismiss
- **Ne prekidaju flow:** ne blokiraju interakciju sa stranicom

### Form UX

#### Input elementi
- **Default state:** border gray-300, bg white
- **Focus state:** border primary, ring (shadow) primary/20, label float ili bold
- **Error state:** border red-500, ring red/20, error poruka ispod u crvenoj
- **Disabled state:** bg gray-100, text gray-400, cursor not-allowed
- **Filled state:** label ostaje gore (floating label) ili bold label iznad

#### Specifični input-i
- **Password:** show/hide toggle (eye ikona)
- **Phone:** country code prefix opciono
- **Quantity (+/−):** dugmad sa strane, min 1, validacija max stock-a
- **Search:** clear X dugme kad ima tekst, submit on Enter
- **Select/Dropdown:** custom styled (ne native), search unutar dropdown-a za duge liste
- **Checkbox/Radio:** custom styled, veći click area
- **File upload:** drag & drop zona + browse dugme, preview thumbnails
- **Rich text editor:** toolbar sa basic opcijama (bold, italic, lists, links, slike)
- **Color picker:** za admin podešavanja
- **Date picker:** za kupone, blog scheduling, izveštaje

#### Validacija
- **Inline real-time:** validacija na blur (kad korisnik napusti polje)
- **Required indicator:** crvena zvezdica (*) pored labele
- **Error poruke:** ispod polja, konkretne ("Email format nije validan"), ne generičke ("Polje je obavezno")
- **Scroll to first error** na form submit
- **Disable submit** dugme dok forma nije validna (opciono, zavisi od konteksta)

### Scroll Behavior

- **Smooth scroll** za anchor linkove i "scroll to" akcije
- **Scroll to top** na navigaciju na novu stranicu (Nuxt default)
- **Preserve scroll** na back/forward browser navigaciju
- **Infinite scroll / Load More:** loading indikator na dnu, "Load More" dugme (ne automatski — korisnik kontroliše)
- **Parallax:** ne koristiti — performansno skup i ne dodaje vrednost e-commerce-u
- **Sticky header:** pojavi se na scroll up, sakrije na scroll down (save screen real estate)
- **Scroll progress** na blog postovima (tanki bar na vrhu) — opciono

### Accessibility (a11y)

#### Minimalni standardi (WCAG 2.1 AA)
- **Contrast ratio:** min 4.5:1 za normalan tekst, 3:1 za veliki tekst (18px+)
- **Focus visible:** svaki interaktivni element mora imati vidljiv focus ring (outline primary)
- **Keyboard navigacija:** Tab za navigaciju, Enter/Space za aktivaciju, Escape za zatvaranje modala/drawera
- **Skip to content:** link na vrhu stranice za screen reader korisnike
- **ARIA labels:** na svim ikonama bez teksta (search, cart, wishlist, close, menu)
- **Alt tekst:** obavezan za sve product slike, dekorative slike imaju alt=""
- **Semantic HTML:** ispravna heading hijerarhija (h1→h2→h3), `<nav>`, `<main>`, `<footer>`, `<button>` vs `<a>`, `<ul>/<li>` za liste
- **Form labels:** svaki input mora imati povezan `<label>` (ne samo placeholder)
- **Error identifikacija:** ne oslanjati se samo na boju — dodati ikonu i tekst
- **Touch target:** min 44x44px za sve interaktivne elemente na mobilnom
- **Reduced motion:** poštovati `prefers-reduced-motion` — isključiti animacije

#### Focus trap
- Modali i draweri: focus ostaje unutar dok su otvoreni
- Tab ciklira kroz elemente unutar modala
- Escape zatvara modal i vraća focus na trigger element

### Page Transitions

- **Default:** nema tranzicije — instant swap (brzo, ne ometa)
- **Opciono (konfigurabilan):** subtle fade (opacity 0→1, 150ms) između stranica
- **Loading indikator:** Nuxt progress bar na vrhu stranice pri navigaciji (tanak, primary boja)
- **SPA feeling:** prefetch linkova na hover, skeleton na destinaciji dok se učitava sadržaj

### Touch Interactions (Mobile)

- **Swipe left/right:** carousel navigacija (proizvodi, hero slideshow, galerija)
- **Swipe right:** zatvori drawer
- **Swipe down:** pull-to-refresh (nativni, ne custom)
- **Pinch to zoom:** product galerija na PDP-u
- **Long press:** ne koristiti — korisnici ga ne očekuju u e-commerce-u
- **Double tap:** ne koristiti — reservisano za native zoom

### Dark Mode

- **Faza 1: ne implementirati** — fokus na light mode
- **Priprema:** koristiti Tailwind CSS varijable za boje (ne hardcoded hex), da se dark mode može dodati kasnije sa `dark:` prefiksom
- **Kad se implementira (Faza 4+):** toggle u headeru/footer-u, poštovati `prefers-color-scheme`, persist izbor u localStorage

---

## 4. Faza 1 — MVP

Cilj: funkcionalan admin sa CRUD proizvodima, javni katalog sa korpom.

### 4.1 Katalog (Product, Category, ProductImage)

#### Modeli i migracije

**Product**
- id, name, slug (unique), short_description, description (rich text), price (decimal 10,2), sale_price (nullable), sku (unique), stock_quantity (integer, default 0), is_active (boolean, default false), featured (boolean, default false), sort_order (integer, default 0), meta_title, meta_description, timestamps, soft_deletes

**Category**
- id, parent_id (nullable, self-referencing FK), name, slug (unique), description, image_path, sort_order, is_active, meta_title, meta_description, timestamps

**ProductImage**
- id, product_id (FK), image_path, alt_text, sort_order, is_primary (boolean), timestamps

**category_product** (pivot)
- category_id, product_id

**User** (kupci)
- Laravel default + phone, timestamps

**Admin**
- id, name, email, password, role (enum: super_admin, manager, operator), is_active, timestamps

#### API rute

**Javne (storefront):**
```
GET    /api/v1/products              — listing sa paginacijom, filterima, sortiranjem
GET    /api/v1/products/{slug}       — detalj proizvoda
GET    /api/v1/categories            — tree kategorija
GET    /api/v1/categories/{slug}     — kategorija sa proizvodima
```

**Admin (zaštićene, Sanctum guard: admin):**
```
POST   /api/v1/admin/login
POST   /api/v1/admin/logout
GET    /api/v1/admin/me

GET    /api/v1/admin/products        — listing sa paginacijom, pretragom
POST   /api/v1/admin/products        — kreiranje
GET    /api/v1/admin/products/{id}   — detalj
PUT    /api/v1/admin/products/{id}   — update
DELETE /api/v1/admin/products/{id}   — soft delete
POST   /api/v1/admin/products/{id}/images  — upload slika
DELETE /api/v1/admin/products/images/{id}  — brisanje slike
PUT    /api/v1/admin/products/{id}/images/reorder  — redosled slika

GET    /api/v1/admin/categories
POST   /api/v1/admin/categories
PUT    /api/v1/admin/categories/{id}
DELETE /api/v1/admin/categories/{id}

GET    /api/v1/admin/dashboard       — statistika (placeholder za fazu 1)
```

#### Admin stranice
- `/products` — tabela proizvoda sa pretragom, filterom po statusu, paginacijom
- `/products/create` — forma za kreiranje (sva polja, upload slika sa drag&drop i reorder, kategorije multi-select, SEO polja, preview slug)
- `/products/:id/edit` — ista forma, edit mode
- `/categories` — tree prikaz sa inline edit, drag&drop reorder, CRUD

#### Storefront stranice i UX
- `/` — Homepage: hero slideshow (carousel sa više slajdova, konfigurabilan), featured proizvodi (carousel), kategorije sa slikama
- `/prodavnica` — Katalog: grid/list toggle, filteri (kategorija, cena range), sortiranje, paginacija + "Učitaj još" opcija
- `/proizvod/:slug` — Detalj: galerija sa zoom/lightbox, cena, opis, add to cart, povezani proizvodi. Ispod Add to Cart sekcije: trust/conversion blok (konfigurabilan u adminu)
- `/kategorija/:slug` — Proizvodi iz kategorije

#### Seeders
Kreirati demo podatke: 6 kategorija, 30 proizvoda sa slikama (placeholder slike), 1 super_admin nalog (admin@webshop.test / password).

#### Product carousel
- Reusable carousel komponenta za prikaz proizvoda (featured, related, cross-sell)
- Swipe podrška na mobilnim uređajima

---

### 4.2 Cene (logika i prikaz)

**Polja na Product modelu:**
- `price` (decimal 10,2) — osnovna cena
- `sale_price` (nullable) — snižena cena
- `sale_price_from` / `sale_price_to` (nullable) — period trajanja akcije
- `cost_price` (nullable) — nabavna cena (samo admin, za marginu u izveštajima)
- `unit_price` (nullable) — cena po jedinici (npr. 1.93/kg)
- `unit_label` (nullable) — naziv jedinice (kg, l, m, kom)

**Prikaz cena na storefrontu:**
- Regularna cena: prikazuje se normalno
- Sale: precrtana originalna + nova cena u crvenoj + badge "-X%"
- Varijante: price range ("$12.00 – $30.00") dok se ne izabere varijanta, pa tačna cena
- Unit price: ispod glavne cene, manji font ("$1.93 / 1kg")
- PDV: konfigurabilan prikaz — "sa PDV-om" ili "bez PDV-a" (globalno podešavanje)

**Sale price logika:**
- Ako su `sale_price_from` / `sale_price_to` definisani — sale važi samo u tom periodu
- Izvan perioda: sale_price se ignoriše, prikazuje se regularna cena
- Countdown timer (već planirano) veže se za `sale_price_to`

**Admin:**
- Sva polja na product formi
- Bulk price update: selektuj proizvode → % popust ili fiksni iznos
- Price history log (opciono): ko je promenio cenu, kad, sa koliko na koliko

**Faza 3+ proširenja:**
- Varijanta override price (već u modelu)
- Customer group pricing (VIP kupci vide nižu cenu)
- Quantity discounts (kupi 3+ → -10%)
- Porezi kalkulisani na osnovu cene (već planirano u Porezi sekciji)

---

### 4.3 Search (AJAX, autocomplete, analytics)

**Pretraga (standard):**
- Full-text pretraga po imenu, opisu, SKU, kategoriji
- AJAX autocomplete dok korisnik kuca (debounced 300ms) — dropdown sa: proizvodima (slika, naziv, cena), matchujućim kategorijama, i "View all results for 'X'" link
- Recent searches: poslednje pretrage čuvane u localStorage, prikazane kad korisnik klikne na search pre kucanja
- Trending searches: "Popularno: majice, patike, jakne" — prikazuju se u praznom search dropdown-u (admin konfiguriše ili automatski iz search analytics)
- Search scope: "Select Category" dropdown pored search bara za pretragu unutar kategorije
- "Did you mean...?" korekcija za typo-ove
- No results: "Nema rezultata za 'X'" + predlozi popularnih proizvoda
- Search engine: MySQL FULLTEXT za početak → migracija na Meilisearch za typo tolerance, faceted search, bolji relevancy (Faza 2+)

**API ruta:**
```
GET    /api/v1/search?q=             — pretraga proizvoda
```

**Storefront:**
- `/pretraga?q=` — Rezultati pretrage (isti layout kao PLP — filteri, sortiranje, paginacija)
- AJAX search sa live preview u headeru (dropdown sa proizvodima — slika, naziv, cena, kategorija dok se kuca)

**Search analytics:**
- Logovanje pretrage počinje u Fazi 1 (čuva se šta je traženo, da li je bilo rezultata)
- Admin UI izveštaj (top pretrage, pretrage bez rezultata, CTR) dostupan u Fazi 4 kad se implementira Izveštaji sekcija

---

### 4.4 Korpa (client-side, drawer, AJAX add to cart)

**Faza 1 — client-side samo:**
- Dodavanje u korpu, promena količine, brisanje
- Čuvanje u localStorage
- **AJAX Add to Cart** — dodavanje bez reload-a sa bilo koje stranice (PLP, PDP, quick view)
- **Cart sidebar drawer** — full-height panel sa desne strane, otvara se automatski kad se proizvod doda u korpu. Sadrži: listu stavki (slika, naziv, cena, quantity +/−, remove), subtotal, "Continue Shopping" i "Go to Checkout" dugmad. Zatvara se klikom van drawer-a ili X dugmetom
- Mini-cart ikonica u headeru sa badge-om (broj stavki) — klik otvara cart drawer
- Korpa stranica `/korpa` sa punom tabelom stavki i totalom
- "Checkout" dugme → placeholder stranica "Checkout dolazi uskoro"
- **Add to Cart feedback:** konfigurabilan u adminu — opcije: otvori cart drawer (default), prikaži toast notifikaciju, ili fly-to-cart animacija

---

### 4.5 Layout & Navigacija (header, footer, mobile nav, back to top)

#### Header
- Logo, mega menu navigacija (podkategorije sa slikama/banerima), AJAX search sa live preview, korpa ikonica sa badge-om

#### Footer (4 kolone + bottom bar)
- **Kolona 1 — O prodavnici:** logo, kratak opis, kontakt info (adresa, telefon, email)
- **Kolona 2 — Brzi linkovi:** Shop, Kategorije, O nama, Kontakt, Blog
- **Kolona 3 — Korisnički servis:** Moj nalog, Praćenje narudžbine, Povrat i zamena, FAQ, Uslovi korišćenja, Privacy policy (statičke stranice su hardcoded u Fazi 1, CRUD iz admina u Fazi 4)
- **Kolona 4 — Newsletter:** naslov, kratak tekst, email input + dugme
- **Social ikone:** Facebook, Instagram, X, TikTok, YouTube (konfigurabilan u adminu)
- **Bottom bar:** copyright tekst, payment ikone (Visa, MasterCard, PayPal, itd.)
- Sve linkove i tekstove admin konfiguriše u podešavanjima
- Responsive: na mobilnom kolone se slažu vertikalno, accordion za svaku sekciju

#### Mobile bottom navigation
- Fixirana traka na dnu (home, kategorije, korpa, account) — samo na mobilnim uređajima

#### Back to top
- Floating dugme, pojavi se na scroll

#### Responsive mobile-first

---

### 4.6 Product Card UX (badges, hover, swap)

- **Add to Cart dugme:** vidljivo direktno na card-u (uvek prisutno, ne samo na hover)
- **Hover akcije:** quick view — prikazuje se na hover overlay-u. Wishlist (Faza 2) i compare (Faza 4) se dodaju kad se feature implementira (feature flag)
- **Hover swap slika:** na hover prikazuje drugu sliku proizvoda (sekundarna slika iz galerije)
- **Badges:** automatski generisani iz podataka — "NEW" (kreiran u poslednjih X dana), "SALE -20%" (ima sale_price), "HOT" (featured), "OUT OF STOCK" (stock = 0)
- **Rating zvezdice** na card-u (prosečna ocena + broj recenzija)
- **Unit price** prikaz gde je relevantno (npr. "$1.93 / 1kg") — opciono polje na proizvodu
- **Category label** ispod naziva proizvoda

---

### 4.7 PDP detalji (tabs, trust blok, sticky bar, prev/next, share, quick view, recently viewed)

#### PDP Tabs

**Default tabovi (svi proizvodi):**
- **Opis** — rich text sadržaj iz product description polja (slike inline, video embed)
- **Specifikacije** — tabela key-value parova (Materijal: Pamuk, Težina: 250g, itd.). Admin unosi kao structured data (dinamičan broj redova)
- **Recenzije** — reviews tab (već razrađeno u Fazi 2 — prosečna ocena, distribution chart, lista, forma)
- **Shipping & Returns** — info o dostavi i politici povrata. Globalni tekst iz admin podešavanja, sa mogućnošću override-a po proizvodu

**Custom tabovi:**
- **Po proizvodu:** admin može dodati custom tabove na pojedinačnom proizvodu (naslov + rich text sadržaj, npr. "Ingredients" za hranu)
- **Po kategoriji:** tabovi nasleđeni iz kategorije — svi proizvodi u kategoriji automatski dobijaju tab (npr. svi u "Odeća" imaju "Size Guide" tab). Proizvod može override-ovati sadržaj nasleđenog taba

**FAQ tab:**
- Pitanja i odgovori po proizvodu (admin unosi Q&A parove)
- Accordion prikaz (klik na pitanje otvara odgovor)
- Opciono: korisnici mogu postaviti pitanje (admin odgovara, objavljuje se javno)

**Responsive ponašanje:**
- Desktop: klasični horizontalni tabovi
- Mobile: tabovi se pretvaraju u accordion (svaki tab postaje expandable sekcija)

**Sticky tabs:**
- Kad korisnik skroluje dole, tab navigacija ostaje fixirana na vrhu ekrana
- Klik na tab: smooth scroll do sadržaja

#### Trust & Conversion blok
Elementi ispod Add to Cart dugmeta, konfigurabilan u admin podešavanjima:
- **Stock status:** "✓ 44 in stock" (zeleno) / "Out of stock" (crveno)
- **Stock urgency bar:** "Less than X items left!" sa progress barom (prikazuje se kad stock padne ispod threshold-a)
- **Countdown timer:** "Hurry! This sale ends in 00:08:22:48" (veže se za sale_price expiry)
- **Shipping info:** "Free worldwide shipping on all orders over $100"
- **Return policy:** "30 days easy returns"
- **Dispatch info:** "Order yours before 2.30pm for same day dispatch"
- **Trust badges:** "Guaranteed Safe Checkout" sa payment ikonama (Visa, MasterCard, PayPal, itd.)
- Svi elementi se uključuju/isključuju i konfigurišu u adminu

#### Sticky Add to Cart bar
- Fiksiran na dnu ekrana, pojavi se kad originalni Add to Cart dugme izađe iz viewport-a (scroll dole)
- Sadrži: thumbnail slike, naziv proizvoda, star rating, stock status ("In stock"), cena/price range, ADD TO CART dugme
- Na mobilnom: kompaktnija verzija (samo cena + dugme)

#### Prev/Next product
- Navigacija između proizvoda na PDP-u bez povratka na PLP

#### Social share blok
- Share dugmad (Facebook, X/Twitter, Pinterest, Email, Copy link)

#### Quick View
- Modal sa osnovnim info o proizvodu (slika, naziv, cena, opis, add to cart) bez napuštanja listing stranice
- Dostupan na hover/klik na product card-u

#### Recently Viewed Products
- Sekcija "Nedavno ste gledali" na PDP-u i homepage-u
- Čuva se u localStorage (poslednji proizvodi koje je korisnik posetio)
- Carousel prikaz

---

### 4.8 Error & Empty states

Svaka stranica mora imati definisano ponašanje kad nema podataka ili kad nešto pođe po zlu:

| Stranica/Element | Empty state | Error state |
|------------------|-------------|-------------|
| **Cart** | "Korpa je prazna" + Continue Shopping + featured proizvodi | API error: toast + retry |
| **Search** | "Nema rezultata za 'X'" + predlozi popularnih proizvoda | — |
| **Wishlist** | "Wishlist je prazan" + link na shop | — |
| **Orders** | "Nemate narudžbina" + link na shop | — |
| **Category (0 proizvoda)** | "Nema proizvoda u ovoj kategoriji" + link na parent ili shop | — |
| **Filteri (0 rezultata)** | "Nema proizvoda za izabrane filtere" + "Resetuj filtere" dugme | — |
| **Product (ne postoji)** | 404 stranica sa suggested proizvodima | — |
| **Checkout (prazna korpa)** | Redirect na cart sa porukom | — |
| **Account/Downloads** | "Nemate digitalne kupovine" | — |
| **Generic 404** | Custom stranica: poruka + search + popular kategorije + featured proizvodi | — |
| **500 / Server error** | Friendly poruka + "Pokušajte ponovo" + kontakt info | — |
| **Network error** | Toast: "Nema internet konekcije" + offline indikator | — |
| **Form validacija** | Inline error ispod polja (crveno) + scroll to first error | — |
| **Image load fail** | Placeholder slika (product silhouette) | — |
| **Loading states** | Skeleton loaderi za sve liste i kartice (ne spinner) | — |

---

### 4.9 SEO & GDPR

#### SEO
- SSR renderovanje za sve javne stranice
- Meta tagovi dinamički iz API-ja
- Open Graph tagovi
- useHead() i useSeoMeta() composables

#### GDPR
- Cookie consent baner sa accept/decline/customize opcijama
- Kategorije kolačića: neophodni, analitički, marketing
- Privacy policy i terms of service stranice
- Korisnik može zatražiti export ili brisanje ličnih podataka iz account-a

---

### 4.10 Admin panel setup (layout, sidebar, UX zahtevi, media library, podešavanja)

#### Layout
- Sidebar navigacija (grupisana po sekcijama — vidi ispod)
- Header sa admin imenom, notifikacije bell ikona, logout
- Breadcrumbs
- Global search (pretraga proizvoda, narudžbina, kupaca iz headera)

#### Sidebar navigacija (raste po fazama)

**Faza 1:**
- Dashboard
- Proizvodi (listing, create/edit)
- Kategorije
- Media Library
- Podešavanja

**Faza 2 (dodaje se):**
- Narudžbine (listing, detail)
- Kupci (listing, detail)
- Reviews (moderacija)
- Newsletter (pretplatnici)
- Blog (postovi, kategorije, tagovi)
- Stranice (statičke + landing)

**Faza 3 (dodaje se):**
- Atributi / Varijante
- Kuponi
- Inventar
- Dostava (zone, metode)
- Plaćanje (metode, transakcije)
- Porezi

**Faza 4 (dodaje se):**
- Izveštaji
- Gift Cards
- Loyalty program
- Store Credits
- Store Locator
- Admin korisnici i permisije

#### Stranice (Faza 1)
- `/login` — admin login forma
- `/` — Dashboard (placeholder statistika)
- `/media` — media library:
  - Centralizovan upload i pregled svih medija (grid sa thumbnail-ima)
  - Drag & drop upload (single + bulk)
  - Automatski resize pri uploadu: generisanje više veličina (thumbnail, medium, large, original)
  - Automatska WebP konverzija
  - Alt text i title editing
  - Mogućnost reuse-a — media picker u svim formama (proizvodi, kategorije, baneri)
  - Max upload size konfigurabilan
  - Podržani formati: JPG, PNG, WebP, SVG, GIF, MP4, PDF
  - Proširenja u Fazi 4: folder organizacija, linked entities, bulk delete, napredno filtriranje
- `/settings` — podešavanja po tabovima

#### UX zahtevi
- Toast notifikacije za uspeh/grešku
- Confirm modal pre brisanja
- Loading stanja na svim akcijama
- Form validacija (client + server errors)
- Responsive (ali desktop-first, admin se koristi na desktop-u)

---

## 5. Faza 2 — Kupovina & Nalozi

### 5.1 Auth & User Account (registracija, login, social, 2FA, account stranice)

#### Registracija
- Email + password forma (ime, prezime, email, telefon, password, confirm password)
- **Password strength meter:** vizuelni indikator (weak/medium/strong) pri kucanju
- **Email verification:** nakon registracije, email sa linkom za potvrdu (expiry 24h). Nalog aktivan i pre potvrde, ali sa upozorenjem "Potvrdite email"
- **Social login:** Google, Facebook, Apple — jedan klik registracija (Laravel Socialite). Ako email već postoji — linkuje account
- Opcija registracije i na checkout-u ("Create account" checkbox)

#### Login
- Email + password
- "Zapamti me" checkbox (persistent session — duži token expiry)
- **Login/Register modal:** opciono umesto zasebne stranice — modal overlay koji ne prekida shopping flow (konfigurabilan u adminu: modal vs stranica)
- **Post-login redirect:** vrati korisnika tamo gde je bio pre logina, ne na homepage
- **Social login dugmad:** Google, Facebook, Apple (isti kao registracija)
- **Account lockout:** 5 neuspelih pokušaja → lock 15 min, email notifikacija korisniku

#### Forgot / Reset password
- Unos emaila → email sa reset linkom (expiry 1h)
- Reset forma: novi password + confirm + password strength meter
- Uspešan reset → automatski login

#### Session management
- Sanctum token-based auth
- Session timeout: auto logout posle konfigurabilan X minuta neaktivnosti
- Logout invalidira token

#### Admin 2FA
- **Two-factor auth (TOTP)** za admin panel — Google Authenticator / Authy
- Setup: QR kod + backup kodovi pri prvom aktiviranju
- Obavezno za super_admin rolu, opciono za ostale (konfigurabilan)

#### Account deletion
- Korisnik može zatražiti brisanje naloga iz `/nalog/profil` (GDPR)
- Confirm modal sa unosom lozinke
- Soft delete: anonimizacija podataka (ime → "Deleted User", email → hash), narudžbine ostaju za izveštaje

#### API rute

**Kupci (Sanctum guard: user):**
```
POST   /api/v1/register
POST   /api/v1/login
POST   /api/v1/logout
POST   /api/v1/login/social/{provider}    — social login (google, facebook, apple)
POST   /api/v1/forgot-password
POST   /api/v1/reset-password
POST   /api/v1/email/verify/{token}
POST   /api/v1/email/resend-verification
GET    /api/v1/me
PUT    /api/v1/me
PUT    /api/v1/me/password
DELETE /api/v1/me                          — account deletion

GET    /api/v1/me/addresses
POST   /api/v1/me/addresses
PUT    /api/v1/me/addresses/{id}
DELETE /api/v1/me/addresses/{id}
```

#### Model: Address (korisničke sačuvane adrese)
- id, user_id (FK), label (npr. "Kuća", "Posao"), first_name, last_name, address_line_1, address_line_2, city, postal_code, country, phone, is_default, timestamps

#### Storefront stranice
- `/nalog/prijava` — login
- `/nalog/registracija` — registracija
- `/nalog/zaboravljena-lozinka` — reset
- `/nalog` — dashboard (pregled poslednje narudžbine, brzi linkovi)
- `/nalog/profil` — ime, email, telefon, promena lozinke
- `/nalog/adrese` — CRUD adresa
- `/nalog/narudzbine` — istorija narudžbina
- `/nalog/narudzbine/:number` — detalj narudžbine
- `/nalog/lista-zelja` — wishlist (već planirano)
- `/nalog/newsletter` — newsletter subscription toggle
- `/nalog/sacuvane-kartice` — sačuvane kartice (Faza 3)
- `/nalog/poklon-kartice` — poklon kartice (Faza 4)
- `/nalog/poeni` — poeni i istorija (Faza 4)
- `/nalog/krediti` — krediti (Faza 4)
- `/nalog/preuzimanja` — downloadable products (Faza 4)

---

### 5.2 Checkout & Cart stranica (form polja, flow, conversion elementi, order bump, trust badges)

#### Cart stranica — funkcionalni zahtevi
- Tabela stavki: slika, naziv, cena, quantity +/−, line total, remove dugme
- Coupon field na cart stranici (ne samo checkout)
- Cart totals breakdown: subtotal, shipping estimate, tax, discount, total
- Empty cart state: poruka + "Continue Shopping" link + featured proizvodi
- Cross-sell sekcija ispod tabele

#### Checkout — funkcionalni zahtevi

**Princip: minimalan friction, maksimalna brzina.** Svaki nepotreban klik ili polje je izgubljena konverzija.

#### Brzina i frictionless UX
- **Što manje polja:** samo obavezna polja vidljiva, opciona skrivena ili uklonjena
- **Smart defaults:** država prefilled iz IP geolokacije, "Billing = Shipping" uključen po default-u
- **Address Autofill:** Google Places API — korisnik kuca adresu, autocomplete popunjava grad, poštanski broj, državu
- **Saved data za returning korisnike:** saved addresses dropdown, saved payment methods — jedan klik checkout
- **Express checkout:** za ulogovane korisnike sa sačuvanom adresom i karticom — "Buy Now" dugme na PDP-u skače direktno na review korak
- **Guest checkout bez prepreka:** ne forsirati registraciju, samo email + opcioni "Create account" checkbox na kraju
- **Inline validacija:** real-time validacija polja dok korisnik kuca (ne čekaj submit), jasne error poruke
- **Auto-advance:** kad korisnik popuni korak, automatski scroll/transition na sledeći (ne čekaj klik na "Next")
- **Persistent cart:** ako korisnik napusti checkout i vrati se — sve je sačuvano (localStorage + server za ulogovane)
- **Bez redirect-a:** sve na jednoj stranici, bez full page reload-a — AJAX za svaki korak

#### Checkout form polja
- **Obavezna:** ime, prezime, email, telefon, adresa, grad, poštanski broj, država
- **Opciona (skrivena po default-u):** address line 2, company name, napomena za narudžbinu
- **Billing vs Shipping:** checkbox "Billing same as shipping" (checked po default-u) — billing forma se prikazuje samo ako unchecked

#### Checkout order summary sidebar
- Lista stavki (slika, naziv, qty, cena), subtotal, shipping, tax, discount, total — sticky na scroll
- Edit quantity/remove direktno iz sidebar-a bez povratka na cart

#### Preferirana varijanta: Single-page checkout
- Sve vidljivo na jednoj stranici u dve kolone (forma levo, summary desno)
- Sekcije se otvaraju/zatvaraju accordion stilom: Shipping → Delivery Method → Payment → Review
- Multi-step ostaje kao alternativna varijanta za klijente koji preferiraju korake

#### Order Bump
- Blok iznad "Place Order" dugmeta: "Add our Best Selling X to your Order!"
- Prikazuje proizvod sa slikom, cenom i "Click to add" dugmetom — jedan klik dodaje u narudžbinu
- Admin: konfiguriše koji proizvod se prikazuje (ručno ili automatski po kategoriji)

#### Trust Badges na Checkout-u
- "Guaranteed Safe Checkout" sa payment ikonama ispod "Place Order" dugmeta
- Testimonial/review ispod: "What they are saying" — jedna rotirana recenzija za social proof

#### API ruta
```
POST   /api/v1/checkout           — kreira narudžbinu
```

#### Storefront stranice
- `/kasa` — single-page checkout: accordion sekcije (shipping → delivery → payment → review), dve kolone
- `/kasa/uspeh/:number` — potvrda narudžbine (broj narudžbine, rezime stavki, ukupna cena, očekivana dostava, sledeći koraci)

---

### 5.3 Orders (modeli, lifecycle, status flow, admin detail, kupac view, print/PDF)

#### Model: Order
- id, order_number (unique, auto-generated), user_id (nullable — guest checkout), status (enum: pending_payment, processing, on_hold, shipped, in_transit, partially_shipped, delivered, return_requested, returned, refunded, cancelled), subtotal, shipping_cost, discount_amount, tax_amount, total, notes, billing_address (JSON), shipping_address (JSON), payment_method, payment_status (enum: pending, paid, failed, refunded), shipped_at, delivered_at, timestamps, soft_deletes

#### Model: OrderItem
- id, order_id (FK), product_id (FK), product_name (snapshot), product_sku, quantity, unit_price, total_price, variant_info (JSON, nullable), timestamps

#### Status flow
```
pending_payment → processing → shipped → in_transit → delivered
                → cancelled
                → on_hold
processing → partially_shipped (ako ima više shipments)
delivered → return_requested → returned → refunded
```

#### Order detail (admin)
- Header: order number, datum, status badge, kupac (link na profil)
- Stavke: tabela sa slikom, nazivom, SKU, qty, unit price, line total, varijanta info
- Totali: subtotal, shipping, tax, discount (kupon/gift card/store credit), total, paid, refunded
- Adrese: shipping i billing (editabilne)
- Timeline: hronološki log svih promena (kreiran, plaćen, poslat, dostavljen + ko je promenio + timestamp)
- Notes: interne beleške (samo admin vidi) + kupcu vidljive beleške
- Akcije: promeni status, dodaj tracking, print invoice, initiate refund, resend email

#### Order actions
- **Promena statusa:** dropdown + obavezna beleška za cancelled/on_hold
- **Tracking:** unos broja i carrier-a, automatski email kupcu
- **Invoice:** auto-generisan PDF (download + email kupcu)
- **Refund:** full ili partial, izbor metode (original payment / store credit), razlog
- **Resend email:** ponovo pošalji bilo koji transakcioni email za tu narudžbinu
- **Edit order:** izmena stavki, adrese (samo dok je status pending/processing)

#### Order listing (admin)
- Filteri: status, datum (range), kupac, payment status, payment method, shipping method, min/max total
- Bulk actions: promena statusa, export, print invoices
- Quick search po order number, kupac email, kupac ime
- Sortiranje: datum, total, status

#### Kupac vidi (`/nalog/narudzbine/:number`)
- Status sa vizuelnim progress barom (ordered → shipped → delivered)
- Stavke, totali, adrese
- Tracking info sa linkom
- Download invoice (PDF)
- "Request Return" dugme (ako je delivered)
- Vidljive admin beleške (ne interne)

#### Print / PDF dokumenti

**Invoice (faktura):**
- Auto-generisan PDF pri kreiranoj narudžbini
- **Sadržaj:** logo prodavca, naziv i adresa prodavca, PIB, matični broj, podaci kupca (ime, adresa), order number, datum, tabela stavki (naziv, SKU, qty, cena, total), subtotal, shipping, tax, discount, grand total, napomena, tekst o PDV-u
- **Invoice numbering:** konfigurabilan format (npr. `INV-2026-00001`) sa auto-incrementom, resetuje se godišnje (opciono)
- Download iz admin order detail-a (dugme "Download Invoice")
- Download iz kupac account-a (`/nalog/narudzbine/:number` → "Download Invoice")
- Auto-email: invoice se šalje kupcu kao PDF attachment na order confirmation email

**Packing slip (otpremnica):**
- PDF za magacin/pakovanje — stavke i količine **bez cena**
- Sadržaj: order number, datum, shipping adresa, tabela stavki (naziv, SKU, qty), napomena
- Download iz admin order detail-a

**Credit note (knjižno odobrenje):**
- PDF za refund — referenca na originalni invoice, refunded stavke, iznos
- Auto-generisan pri refund-u

**Shipping label:**
- Generisanje nalepnice za pošiljku — zavisi od integracije sa kurirskom službom
- Za početak: admin printuje iz carrier sistema. Kasnije: API integracija za auto-generisanje

**Admin bulk & branding:**
- **Bulk print:** selektuj više narudžbina → download ZIP sa svim invoice-ima (ili packing slip-ovima)
- **Custom branding:** logo, boje, font, footer tekst — konfigurabilan u admin podešavanjima
- **Legal info:** PIB, matični broj, tekst o PDV-u, broj računa — konfigurabilan za srpsko tržište
- Print preview pre download-a

#### API rute
```
GET    /api/v1/me/orders
GET    /api/v1/me/orders/{order_number}

GET    /api/v1/admin/orders
GET    /api/v1/admin/orders/{id}
PUT    /api/v1/admin/orders/{id}/status
```

#### Admin stranice
- `/orders` — listing sa filterima (status, datum, kupac)
- `/orders/:id` — detalj narudžbine, promena statusa, napomene
- `/customers` — listing kupaca
- `/customers/:id` — profil kupca sa istorijom

#### Admin API rute (kupci)
```
GET    /api/v1/admin/customers
GET    /api/v1/admin/customers/{id}
```

---

### 5.4 Reviews / Recenzije (model, storefront, admin, API rute)

#### Model: Review
- id, product_id (FK), user_id (FK), rating (1-5), title, content, is_verified_purchase (boolean), status (enum: pending, approved, rejected), admin_reply (nullable), created_at, updated_at

#### Storefront
- **PDP Reviews tab:** prosečna ocena (veliki broj + zvezdice), distribution chart (5★ 66%, 4★ 33%...), lista recenzija (avatar, ime, datum, rating, naslov, tekst, "Verified Purchase" badge)
- **Review forma:** rating (klikabilne zvezdice), naslov, tekst, opcioni upload slika. Samo za kupce koji su kupili proizvod (verified purchase) ili za sve (konfigurabilan)
- **Sortiranje recenzija:** newest, highest rated, lowest rated, most helpful
- **"Was this helpful?" glasanje** na svakoj recenziji (thumbs up/down)
- Rating prikazuje se na product card-u, PLP-u, search results-u i quick view-u (definisano u sekciji 4.6)

#### Admin
- Moderacija recenzija: pending → approved / rejected
- Admin reply na recenziju (vidljiv na storefrontu)
- Bulk approve/reject
- Filteri: po statusu, ratingu, proizvodu, datumu
- Email notifikacija adminu za novu recenziju

#### API rute
```
GET    /api/v1/products/{slug}/reviews       — listing sa paginacijom, sortiranjem
POST   /api/v1/products/{slug}/reviews       — kreiranje (auth required)
POST   /api/v1/reviews/{id}/helpful          — glasanje
GET    /api/v1/admin/reviews                 — listing za moderaciju
PUT    /api/v1/admin/reviews/{id}            — approve/reject/reply
```

---

### 5.5 Wishlist & Cross-sell/Up-sell

#### Wishlist

**Model:** pivot tabela: user_id, product_id, timestamps

- Srce ikonica na svakom product card-u i detail stranici
- Za ulogovane korisnike: čuva se u bazi
- Za neulogovane: localStorage, sinhronizuje se pri loginu
- `/nalog/lista-zelja` — listing sa opcijom "premesti u korpu"
- Wishlist badge u headeru sa brojem stavki

**API rute:**
```
GET    /api/v1/me/wishlist
POST   /api/v1/me/wishlist/{product_id}
DELETE /api/v1/me/wishlist/{product_id}
```

#### Cross-sell / Up-sell
- **Related products** — proizvodi iz iste kategorije (već u Fazi 1 na product detail)
- **Cross-sell** — preporučeni proizvodi na cart stranici ("Često se kupuje uz...")
- **Up-sell** — skuplja alternativa na product detail stranici ("Možda vas zanima i...")
- Admin: mogućnost ručnog podešavanja related/cross-sell/up-sell proizvoda po proizvodu

---

### 5.6 Newsletter & Notify Me

#### Newsletter

**Model: NewsletterSubscriber**
- id, email (unique), is_active, subscribed_at, unsubscribed_at, timestamps

- Signup forma u footeru (email polje + dugme)
- **Newsletter popup** — modal koji se prikazuje posle X sekundi ili na exit intent (konfigurabilan u adminu, prikazuje se jednom po sesiji)
- Double opt-in: potvrda putem emaila
- Admin: pregled pretplatnika, export u CSV
- Unsubscribe link u svakom emailu

**API rute:**
```
POST   /api/v1/newsletter/subscribe
GET    /api/v1/newsletter/confirm/{token}
GET    /api/v1/newsletter/unsubscribe/{token}
GET    /api/v1/admin/newsletter-subscribers
```

#### Notify Me (Back in Stock)
- Na PDP-u kad je proizvod out of stock: "Notify me when available" dugme
- Otvara polje za email — korisnik se prijavljuje za notifikaciju
- Automatski email kad se stock vrati iznad 0
- Admin: pregled prijavljenih korisnika po proizvodu

---

### 5.7 Blog (model, storefront, admin, API rute)

#### Modeli

**Post** — id, admin_id, title, slug, content (rich text), excerpt, featured_image, status (enum: draft, published, scheduled), published_at, meta_title, meta_description, timestamps
**BlogCategory** — id, name, slug, description, sort_order
**Tag** — id, name, slug
**blog_category_post** (pivot) — blog_category_id, post_id
**post_tag** (pivot) — post_id, tag_id

#### Storefront
- `/blog` — listing sa varijantama (grid + sidebar / full grid / lista)
- `/blog/:slug` — post sa varijantama (sidebar / centered)
- `/blog/kategorija/:slug` — postovi iz kategorije
- `/blog/tag/:slug` — postovi sa tagom
- Sidebar widget-i: recent posts, kategorije sa brojem postova, tag cloud, search
- Social share dugmad na svakom postu
- Related posts na dnu (ista kategorija/tagovi)
- Komentari: opciono (konfigurabilan u adminu — uključeno/isključeno)
- Reading time estimate ("5 min čitanja")
- Author info blok (ime, avatar, bio)

#### Admin
- CRUD postova sa rich text editorom (bold, italic, headings, lists, links, slike, video embed, blockquote, code block)
- Featured image upload sa preview-om
- Kategorije i tagovi management (CRUD)
- Scheduled publishing: postavi datum objavljivanja, post se automatski publikuje
- Draft/Preview: editor sa preview pre objavljivanja
- SEO polja: meta title, meta description, OG image (override featured image)
- Inline image upload u sadržaju posta (media library integration)

#### API rute
```
GET    /api/v1/blog/posts                — listing sa paginacijom, filter po kategoriji/tagu
GET    /api/v1/blog/posts/{slug}         — detalj posta
GET    /api/v1/blog/categories           — listing kategorija
GET    /api/v1/blog/tags                 — listing tagova
GET    /api/v1/admin/blog/posts          — admin listing
POST   /api/v1/admin/blog/posts          — kreiranje
PUT    /api/v1/admin/blog/posts/{id}     — update
DELETE /api/v1/admin/blog/posts/{id}     — brisanje
```

---

### 5.8 Email notifikacije (svi transakcioni + admin emailovi)

Svi emailovi koriste konzistentan branding template (logo, boje, footer sa kontaktom i unsubscribe). Admin može customize-ovati sadržaj u podešavanjima.

#### Transakcioni emailovi (kupcu)
| Email | Trigger | Sadržaj |
|-------|---------|---------|
| **Dobrodošlica** | Registracija | Pozdrav, link na account, CTA na shop |
| **Reset lozinke** | Zahtev za reset | Link za reset (expiry 1h) |
| **Potvrda narudžbine** | Narudžbina kreirana | Order number, lista stavki sa slikama, cene, totali, shipping/billing adresa, očekivana dostava |
| **Narudžbina poslata** | Status → shipped | Order number, tracking broj + link, očekivani datum |
| **Narudžbina isporučena** | Status → delivered | Potvrda, CTA za ostavljanje recenzije |
| **Narudžbina otkazana** | Status → cancelled | Razlog, info o refund-u |
| **Refund** | Refund procesiran | Iznos, metoda povrata, očekivano vreme |
| **Back in stock** | Proizvod ponovo dostupan | Naziv + slika + CTA "Kupi sada" |
| **Gift card** | Gift card kupljena | Kod, iznos, poruka pošiljaoca, CTA za korišćenje |
| **Newsletter potvrda** | Double opt-in | Link za potvrdu pretplate |
| **Newsletter dobrodošlica** | Pretplata potvrđena | Pozdrav, šta da očekuju, CTA |

#### Emailovi adminu
| Email | Trigger | Sadržaj |
|-------|---------|---------|
| **Nova narudžbina** | Narudžbina kreirana | Rezime: kupac, stavke, total, link na admin |
| **Low stock alert** | Stock < threshold | Proizvod, trenutni stock, link na admin |
| **Nova recenzija** | Recenzija submitovana | Proizvod, rating, tekst, link za moderaciju |
| **Request a Call Back** | Korisnik zatražio poziv | Proizvod, telefon, kanal, link |
| **Contact form** | Kontakt forma popunjena | Ime, email, poruka |

---

### 5.9 Request a Call Back

- Dugme na PDP-u (pored ili ispod Add to Cart): "Request a Call Back"
- Otvara modal sa: slika proizvoda + naziv + cena, country dropdown, phone input, izbor kanala (Call / SMS / WhatsApp)
- Šalje notifikaciju adminu (email + admin dashboard)
- Posebno korisno za high-value proizvode

---

### 5.10 API dokumentacija (Swagger)

#### Swagger / OpenAPI
- OpenAPI 3.0 spec auto-generisana iz koda (Laravel annotations/attributes na controllerima)
- Swagger UI dostupan na `/api/docs` — interaktivna dokumentacija gde developer može testirati endpoint direktno
- Auth support: unos Sanctum tokena u Swagger UI za testiranje zaštićenih ruta

#### Sadržaj dokumentacije (po endpointu)
- URL, HTTP metoda, opis
- Auth requirements (public / user / admin)
- Request parametri (query, path, body) sa tipovima i validacijom
- Request body primer (JSON)
- Response format sa primerom (success + error)
- Status kodovi (200, 201, 401, 403, 404, 422)

#### Dodatno
- **Postman collection:** exportovana kolekcija sa svim endpointima, environment varijablama, primerima — za brzo testiranje
- **Verzioniranje:** dokumentacija prati API verziju (`/api/docs`)
- **Changelog:** šta se promenilo između verzija (novi endpointi, breaking changes, deprecations)
- Dokumentacija se automatski ažurira pri deploy-u (deo CI/CD pipeline-a)

---

## 6. Faza 3 — Napredna prodaja

### 6.1 Varijante & Atributi (modeli, admin UI, storefront PDP/PLP, size guide)

#### Modeli

**Attribute** — id, name (npr. "Boja", "Veličina"), type (enum: select, color, image), sort_order, is_filterable (boolean), is_visible_on_card (boolean)
**AttributeValue** — id, attribute_id, value (npr. "Crvena", "XL"), color_hex (nullable, za color tip), image_path (nullable, za image tip), sort_order
**ProductVariant** — id, product_id, sku (unique), price (override, nullable — ako null koristi base price), weight (nullable, za shipping), stock_quantity, is_active
**product_variant_attribute_value** (pivot) — variant_id, attribute_value_id
**product_variant_image** (pivot) — variant_id, product_image_id (mapira koje slike idu uz koju varijantu)

#### Admin — Upravljanje atributima
- **Atributi su potpuno korisnički definisani** — admin kreira, menja, briše atribute i njihove vrednosti. Nema hardcoded atributa
- Admin → Atributi: CRUD atributa (naziv, tip prikaza, sortable, filterable)
- Dodavanje vrednosti: inline editing (klik "+" za novu vrednost)
- Za color tip: color picker za hex vrednost
- Za image tip: upload swatch slike
- Atribut se može označiti kao `is_filterable` (pojavljuje se u PLP filterima) i `is_visible_on_card` (prikazuje se na product card-u)

#### Admin — Kreiranje varijanti na proizvodu
- **Izbor atributa:** admin bira koje atribute proizvod koristi (npr. "Boja" + "Veličina")
- **Matrica:** auto-generiše sve kombinacije (Crvena-S, Crvena-M, Crvena-L, Plava-S, Plava-M, Plava-L...)
- **Bulk edit:** set cena, stock, SKU za sve varijante odjednom ili individualno
- **Disable varijante:** pojedinačno isključivanje nedostupnih kombinacija (npr. Crvena-XXL ne postoji)
- **Varijanta image mapping:** admin mapira koja slika/slike idu uz koju varijantu (npr. sve slike crvenog proizvoda → Crvena varijante)
- **Quick duplicate:** kopiraj cene/stock iz jedne varijante na ostale

#### Storefront — PDP
- **Izbor varijante:** dropdown ili swatch (boja kao kružić, veličina kao dugmad) — tip prikaza zavisi od atribut type-a
- **Promena pri izboru:** slika se menja na varijantu (galerija se filtrira), cena se ažurira, stock status se ažurira
- **Nedostupne kombinacije:** disabled / crossed out sa "Out of stock" oznakom
- **Auto-select:** ako proizvod ima samo jednu dostupnu varijantu — auto-selectuj je
- **Deep link:** `/proizvod/majica?color=red&size=xl` — pre-select varijanta iz URL-a (za share, marketing)
- **Cena range:** ako varijante imaju različite cene, na card-u i pre selekcije prikazuje "$12.00 – $30.00"
- **Varijanta weight:** koristi se za shipping kalkulaciju po varijanti

#### Storefront — PLP
- **Color/Size swatches na product card-u** — mali kružići za boju ili veličinu direktno na listing-u. Klik na swatch menja sliku na card-u
- **Visual filters** — filteri sa slikama/swatch-evima umesto samo teksta (npr. boja kao kružić, brend kao logo)

#### Storefront — PDP dodatno
- **Size guide popup** — tabela sa merama na PDP-u (konfigurabilan po kategoriji u adminu)
- **Varijanta tabela:** opcioni prikaz svih varijanti u tabeli sa cenama i stock-om (korisno za B2B)

---

### 6.2 Kuponi (modeli, tipovi, pravila, admin, checkout UX)

#### Modeli

**Coupon** — id, code (unique), type (enum: percentage, fixed_cart, fixed_product, free_shipping), value (decimal), min_order_amount, max_discount_amount (cap za percentage), max_uses, used_count, per_user_limit, starts_at, expires_at, is_active, timestamps

**CouponCondition** — id, coupon_id, condition_type (enum: categories, products, exclude_categories, exclude_products, user_groups), condition_ids (JSON)

**CouponUsage** — id, coupon_id, user_id, order_id, discount_amount, used_at

#### Tipovi kupona
- **Percentage:** X% popusta na celu korpu ili izabrane proizvode/kategorije
- **Fixed cart:** fiksni iznos popusta na celu korpu (npr. -500 din)
- **Fixed product:** fiksni iznos popusta po proizvodu (npr. -200 din na svaki)
- **Free shipping:** besplatna dostava (bez obzira na threshold)

#### Pravila i ograničenja
- Min order amount (ne može se koristiti ispod X din)
- Max discount amount (cap za percentage — npr. max 2000 din popusta)
- Applies to: svi proizvodi, specifične kategorije, specifični proizvodi
- Exclude: izuzmi kategorije ili proizvode iz popusta
- Per user limit (npr. max 1 korišćenje po korisniku)
- Total uses limit (npr. max 100 korišćenja ukupno)
- Datum početka i isteka
- **Stacking rules:** da li se može kombinovati sa drugim kuponima (da/ne, konfigurabilan)
- **First order only:** opcija da kupon važi samo za prvu narudžbinu

#### Checkout / Cart UX
- Coupon polje na cart stranici i checkout-u
- Validacija u realnom vremenu: unos koda → AJAX provera → prikaz popusta ili error poruka
- Prikaz primenjenog kupona: naziv, popust, X dugme za uklanjanje
- Error poruke: "Kupon je istekao", "Min iznos je X din", "Kupon je već iskorišćen", itd.
- **Countdown timer** — odbrojavanje do kraja akcije/kupona na product card-u i PDP-u

#### Admin
- CRUD kupona sa svim pravilima
- Statistika po kuponu: koliko puta korišćen, ukupan popust, prihod sa kuponom
- Generate bulk kodova (npr. 100 unikatnih kodova za kampanju)
- Quick duplicate kupon
- Filter: aktivni, istekli, po tipu

---

### 6.3 Inventar (stock management, reservation, backorder, bulk, admin UI)

#### Model: StockMovement
- id, product_id, variant_id (nullable), quantity_change (+/-), type (enum: order, return, manual_adjustment, import, reservation_release), reference_id (nullable — order_id, import_id), reason (nullable — tekst), admin_id (nullable — ko je promenio), created_at

#### Stock management
- Stock quantity po proizvodu i po varijanti (varijanta override-uje product stock)
- **Automatsko smanjenje** stock-a pri kreiranoj narudžbini
- **Automatsko vraćanje** stock-a pri otkazanoj narudžbini ili return-u
- **Stock reservation:** kad korisnik uđe u checkout — rezerviši stock na 15 min (konfigurabilan). Sprečava overselling. Ako ne završi checkout — release reservation
- **Backorder:** opcija po proizvodu — dozvoli kupovinu kad je stock 0, sa prikazom "Ships in X weeks" (admin unosi estimated restock date)
- **Restock date:** opciono polje na proizvodu — "Očekivano ponovo u prodaji: 15. april". Prikazuje se na PDP-u kad je out of stock

#### Low stock notifikacije
- Low stock threshold (globalni default + override po proizvodu)
- Email notifikacija adminu kad proizvod padne ispod threshold-a
- Admin dashboard: low stock widget sa listom proizvoda

#### Stock history log
- Svaka promena stock-a logovana u StockMovement
- Ko je promenio (admin ili sistem), kad, razlog, referenca na narudžbinu
- Admin: pregled istorije po proizvodu (timeline prikaz)

#### Bulk operacije
- **Bulk stock import:** CSV upload (SKU, quantity, operation: set/add/subtract)
- **Bulk stock export:** CSV download trenutnog stanja
- **Bulk manual adjustment:** selektuj proizvode → unesi novu količinu ili +/- adjustment sa razlogom
- **Inventory izveštaj:** stock vrednost (quantity × cost_price), low stock, out of stock, dead stock (nema prodaje X dana)

#### Admin UI
- Inventar listing: proizvod, SKU, trenutni stock, status (in stock / low / out), poslednja promena
- Filteri: status, kategorija, supplier
- Inline edit stock-a iz listinga (klik na broj → edit → save)
- Stock history modal po proizvodu

---

### 6.4 Dostava (zone, metode, kalkulacija, tracking, admin)

#### Modeli

**ShippingZone** — id, name, countries (JSON), is_active, sort_order
**ShippingMethod** — id, zone_id, name, description, calculation_type (enum: flat, weight_based, price_based, free), cost (decimal), free_above (nullable), min_order (nullable), estimated_days_min, estimated_days_max, is_active, sort_order

#### Kalkulacija
- **Flat rate:** fiksna cena po narudžbini
- **Weight-based:** cena po kilogramu (zahteva weight polje na proizvodu)
- **Price-based:** cena dostave zavisi od ukupne vrednosti korpe (rangovi: 0-3000 → 500din, 3000-6000 → 300din, itd.)
- **Free shipping:** besplatno iznad threshold-a (konfigurabilan po zoni)
- Više metoda po zoni — kupac bira na checkout-u (npr. "Standardna 3-5 dana — 300din" vs "Express 1-2 dana — 600din")

#### Checkout UX
- Prikaz dostupnih metoda sa cenom i estimated delivery ("Očekivana dostava: 15-17. april")
- Shipping estimate na cart stranici (pre checkout-a) baziran na državi
- Free shipping progress bar u korpi

#### Order tracking

**Model: Shipment** — id, order_id, tracking_number, carrier (enum: post_srbije, dexpress, bex, aks, custom), status, shipped_at, delivered_at

- Status tracking: processing → shipped → in_transit → delivered
- Email notifikacija kupcu sa tracking brojem i linkom
- Tracking stranica: `/pracenje/:tracking_number` (ili redirect na carrier sajt)
- Admin: unos tracking broja pri slanju, promena statusa

#### Admin
- CRUD zona i metoda
- Weight polje na proizvodu (opciono, za weight-based shipping)
- Konfiguracija carrier-a (naziv, tracking URL pattern)
- Bulk print shipping labels (integracija sa carrier API-jem — buduća faza)

---

### 6.5 Plaćanje (metode, flow, refund, security, admin)

#### Modeli

**PaymentMethod** — id, name, code (enum), description, instructions (rich text, za offline metode), is_active, sort_order, config (JSON — API keys, merchant ID, itd.)

**Payment** — id, order_id, method_code, transaction_id, amount, status (pending/completed/failed/refunded), gateway_response (JSON), created_at

#### Podržane metode

| Metoda | Tip | Faza | Napomene |
|--------|-----|:----:|----------|
| **Pouzeće (COD)** | Offline | 3 | Plaćanje kuriru pri preuzimanju. Opcioni dodatni trošak (npr. +100din) |
| **Virman / Uplata na račun** | Offline | 3 | Prikaz instrukcija nakon narudžbine (banka, broj računa, poziv na broj). Order status: pending dok admin ne potvrdi uplatu |
| **Stripe** | Online (kartice) | 3 | Credit/debit kartice, Stripe Elements za PCI compliance, save card za buduće kupovine, 3D Secure podrška |
| **PayPal** | Online | 4 | PayPal Checkout SDK, express checkout dugme |
| **Lokalni gateway-i** | Online | 4 | Integracija po potrebi klijenta (NestPay, Allsecure, itd.) |

#### Checkout payment flow
1. Kupac bira metodu plaćanja
2. **Online:** redirect na payment provider ili inline forma (Stripe Elements) → capture → order confirmed
3. **Offline:** order kreiran sa status "pending payment" → admin ručno potvrđuje → order confirmed
4. Sve payment interakcije logovane u Payment model

#### Refund flow
- Admin inicira refund iz order detail-a (full ili partial amount)
- **Online metode:** automatski refund preko gateway API-ja
- **Offline metode:** admin markira kao refunded, ručni transfer
- Email kupcu sa potvrdom refund-a
- Opciono: refund kao store credit umesto povrata novca

#### Security
- PCI DSS compliance: kartični podaci nikad ne dodiruju naš server (Stripe Elements / PayPal SDK)
- Payment config (API keys) enkriptovani u bazi
- Transaction log za audit trail

#### Admin
- Aktiviranje/deaktiviranje metoda
- Test/Live mode toggle po metodi
- API credentials unos (enkriptovano)
- Instrukcije za offline metode (WYSIWYG editor)
- COD: konfiguracija dodatnog troška
- Pregled transakcija i statusa po narudžbini

---

### 6.6 Porezi

#### Model: TaxRate
- id, name, rate (decimal), country, is_active, is_included_in_price

- Admin: konfiguracija stopa
- API: kalkulacija poreza pri checkoutu

---

### 6.7 Abandoned Cart Recovery (model, email serija, exit intent, admin)

#### Model: AbandonedCart
- id, user_id (nullable), email (nullable — guest), cart_data (JSON — stavke, količine, cene), recovery_token (unique), status (enum: abandoned, reminded, recovered, expired), reminded_count (integer), last_reminded_at, created_at, updated_at

#### Kako radi
- Kad korisnik doda u korpu i ne završi checkout u roku od 1h — korpa se markira kao abandoned
- Za ulogovane korisnike: automatski iz server-side korpe
- Za goste: ako su uneli email u prvom koraku checkout-a pa napustili

#### Email serija (automatski, queue job)
1. **Posle 1h:** "Zaboravili ste nešto u korpi" — lista stavki sa slikama, cenama, CTA "Završite kupovinu"
2. **Posle 24h:** "Još uvek vas čeka" — isti sadržaj + automatski generisan kupon (-10%, važi 48h)
3. **Posle 72h:** "Poslednja šansa" — urgency, stavke možda neće biti dostupne, kupon ističe uskoro

- Svaki email ima unikatni recovery link (`/korpa/obnovi/:token`) koji vraća korpu sa svim stavkama
- Ako korisnik kupi pre sledećeg emaila — serija se zaustavlja
- Unsubscribe link u svakom emailu

#### Exit intent popup
- Kad korisnik kreće da napusti stranicu (kursor ide ka tab bar-u) — popup modal
- "Sačekajte! Ostavite email i dobijte 10% popusta"
- Prikazuje se jednom po sesiji, samo ako je korpa neprazna
- Konfigurabilan u adminu: tekst, popust, uključen/isključen

#### Admin
- **Dashboard widget:** broj napuštenih korpi danas/nedelja/mesec, ukupna vrednost
- **Lista:** kupac/email, stavke, vrednost korpe, datum, status (abandoned/reminded/recovered)
- **Recovery rate izveštaj:** procenat korisnika koji se vrate i kupe posle emaila, prihod od recovery-ja
- **Konfiguracija:** timing emailova, tekst, kupon procenat, exit intent on/off
- Email template customization

---

### 6.8 Import/Export podataka

#### Import
- **CSV import proizvoda:** naziv, opis, cena, sale_price, SKU, kategorija, stock, slike URL, meta title, meta description
- **Import sa validacijom:** upload CSV → preview tabela sa statusom po redu (OK / greška / upozorenje) → potvrdi → import. Greške ne blokiraju ostale redove
- **Import mapping:** admin mapira kolone iz svog fajla na naša polja (drag & drop ili dropdown). Sačuvaj mapping za buduće importe
- **Image import:** URL kolona u CSV-u — sistem downloaduje slike u pozadini (queue job) i dodaje na proizvod
- **Incremental update:** ako SKU već postoji → update, ako ne → kreiraj novi
- **Template download:** dugme "Download template" — prazan CSV sa ispravnim header-ima i jednim primer redom
- **Import history:** log svih importa (ko, kad, fajl, ukupno redova, uspešno, greška)

#### Export
- **CSV export proizvoda:** sva polja, filter po kategoriji/statusu pre exporta
- **CSV export narudžbina:** order number, datum, kupac, stavke, totali, status, payment status
- **CSV export kupaca:** ime, email, telefon, broj narudžbina, ukupna potrošnja
- **Scheduled export:** konfigurabilan automatski export (nedeljni/mesečni) na admin email — za računovodstvo

---

## 7. Faza 4 — Proširenja

### 7.1 Loyalty, Gift Cards & Store Credits

#### Gift Cards

**Model: GiftCard** — id, code (unique, auto-generated), initial_amount, balance, sender_user_id, recipient_email, recipient_name, personal_message, status (active/used/expired/disabled), expires_at, created_at

**Model: GiftCardTransaction** — id, gift_card_id, order_id, amount, type (debit/credit), created_at

**Kupovina:**
- Poseban tip proizvoda u shopu (kategorija "Gift Cards")
- Kupac bira iznos: predefinisani (1000, 2000, 5000 din) ili custom amount
- Forma: recipient email, recipient name, lična poruka, datum slanja (odmah ili zakazano)
- Email primaocu sa kodom, porukom, CTA za korišćenje

**Korišćenje:**
- Polje na checkout-u: "Imate poklon karticu?" → unos koda → prikazuje se balance i oduzima od totala
- Delimično korišćenje: ostatak ostaje na kartici
- Kombinacija sa kuponom dozvoljena
- Korisnik može proveriti balance na `/poklon-kartica/provera`

**Admin:**
- Pregled svih kartica: kod, balance, status, sender, recipient
- Ručno kreiranje kartice (admin dodela)
- Disable/enable kartice
- Istorija transakcija po kartici

#### Loyalty / Reward Points

**Model: LoyaltyAccount** — id, user_id, points_balance, tier (bronze/silver/gold/platinum), lifetime_points

**Model: LoyaltyTransaction** — id, user_id, points, type (earned/redeemed/expired/adjusted), source (purchase/registration/review/referral/birthday/admin), reference_id, description, created_at

**Pravila za zarađivanje poena (konfigurabilan u adminu):**
- Kupovina: X poena po dinaru (npr. 1 RSD = 1 poen)
- Registracija: fiksni bonus (npr. 500 poena)
- Prva kupovina: bonus poeni
- Recenzija: fiksni bonus po recenziji
- Birthday: godišnji bonus
- Referral: bonus kad prijatelj napravi prvu kupovinu

**Korišćenje poena:**
- Checkout: "Koristite X poena za Y din popusta" sa sliderom ili input-om
- Konfigurabilan conversion rate (npr. 100 poena = 100 din)
- Minimum poena za korišćenje
- Maksimum popusta po narudžbini (% ili fiksno)

**Tier sistem:**
- Nivoi bazirani na lifetime_points (pragovi konfigurabilan)
- Viši nivoi: bolji conversion rate, exclusive kuponi, early access
- Prikaz trenutnog nivoa i progress-a do sledećeg u account-u

**Account stranica (`/nalog/poeni`):**
- Trenutni balance i tier
- Progress bar do sledećeg tiera
- Istorija transakcija (zarađeno, potrošeno, isteklo)
- Dostupne nagrade

**Admin:**
- Konfiguracija svih pravila (poeni po dinaru, bonusi, tier pragovi)
- Pregled po korisniku: balance, tier, istorija
- Ručno dodavanje/oduzimanje poena sa razlogom
- Expiry pravila (npr. poeni ističu posle 12 meseci neaktivnosti)

#### Store Credits

**Model: StoreCreditAccount** — id, user_id, balance

**Model: StoreCreditTransaction** — id, user_id, amount, type (credit/debit), source (refund/loyalty_conversion/admin/gift), reference_id, description, created_at

- Kredit na korisničkom nalogu iz: refund-a, loyalty konverzije, admin dodele, poklon od prodavnice
- Automatski se primenjuje na checkout-u pre payment-a (korisnik može isključiti)
- Prikazuje se u account-u: balance + istorija transakcija
- Admin: dodela/oduzimanje kredita sa razlogom, pregled po korisniku
- Ne ističe (za razliku od poena)

---

### 7.2 Multi-language & Multi-valuta

#### Multi-language (i18n)

**Storefront prevodi:**
- Language picker u headeru / top baru (zastava + naziv jezika)
- URL strategija: URL-ovi su uvek na srpskom, bez jezičkih prefiksa. Jezik menja samo sadržaj stranice, ne URL
- Nuxt i18n modul (`@nuxtjs/i18n`) sa lazy-loaded locale fajlovima
- UI stringovi (dugmad, labele, poruke): JSON translation fajlovi po jeziku
- Sadržaj proizvoda/kategorija/blog postova: prevodljiva polja u bazi

**Prevodljiva polja u bazi:**
Svaki model sa tekstualnim sadržajem dobija translations JSON kolonu ili odvojenu `_translations` tabelu:

- **Product translations:** name, slug, short_description, description, meta_title, meta_description
- **Category translations:** name, slug, description, meta_title, meta_description
- **Blog Post translations:** title, slug, content, excerpt, meta_title, meta_description
- **Static Page translations:** title, slug, content, meta_title, meta_description

**Admin panel:**
- Jezički tab-ovi na svakoj formi (npr. tab "SR" / "EN" / "DE" iznad polja za naziv i opis)
- Podešavanje dostupnih jezika u admin settings-u (dodaj/ukloni jezik, postavi default)
- Indikator kompletnosti prevoda (npr. "EN: 80% prevedeno")
- Bulk export/import prevoda (CSV/JSON) za eksternog prevodioca

**SEO za multi-language:**
- `hreflang` tagovi na svakoj stranici
- Odvojeni sitemap-ovi po jeziku
- Canonical URL-ovi sa language alternates
- OG locale tag

#### Multi-valuta
- Prekidač valuta u headeru (RSD/EUR/USD)
- Kursna lista iz eksternog API-ja ili ručno u adminu
- Cene se konvertuju na frontendu, baza čuva u primarnoj valuti

---

### 7.3 Product Compare, Social Proof, Promo Bar, Free Shipping Indikator

#### Product Compare
- Ikonica na product card-u za dodavanje u compare listu
- Floating bar na dnu ekrana sa odabranim proizvodima (max 3-4)
- `/uporedi` stranica — side-by-side tabela sa specifikacijama, cenama, slikama
- Čuvanje u localStorage

#### Social Proof
- "Kupljeno X puta" brojač na PDP-u (iz broja narudžbina)
- "X ljudi gleda ovaj proizvod" (simulirano ili realno sa WebSocket)
- Popup notifikacije: "Marko iz Beograda je upravo kupio..." (konfigurabilan u adminu, može se isključiti)

#### Promo Bar
- Konfigurabilan top bar iznad headera sa promotivnom porukom (npr. "Besplatna dostava za narudžbine iznad 5000 din")
- Admin: podešavanje teksta, boje, linka, mogućnost disable-a
- Dismiss dugme (zapamti u localStorage)

#### Free Shipping Indikator
- Progress bar u korpi/mini-cart: "Još X din do besplatne dostave"
- Konfigurabilan threshold u admin podešavanjima
- Poruka se menja kad se dostigne threshold: "Ostvarili ste besplatnu dostavu!"

---

### 7.4 Blog (proširenja)

Sva osnovna blog funkcionalnost je u sekciji 5.7. Eventualna proširenja u Fazi 4 (napredni komentari, newsletter integracija, itd.) dokumentuju se ovde po potrebi.

---

### 7.5 Shop the Look, Store Locator, Downloadable Products

#### Shop the Look
- Kolekcija/outfit slika — klikabilne tačke na slici linkuju na proizvode
- Korisnik može dodati sve stavke u korpu odjednom ("Add all to cart")
- Admin: upload slike, postavljanje hotspot-ova na pozicije, linkovanje proizvoda
- Homepage sekcija ili zasebna stranica `/izgled`

#### Store Locator
- Mapa sa lokacijama prodavnica (Google Maps / Leaflet)
- Lista prodavnica sa adresom, telefonom, radnim vremenom
- Pretraga po gradu ili radius od lokacije korisnika
- Admin: CRUD lokacija

#### Downloadable Products
- Digitalni proizvodi (PDF, fajlovi, softver)
- Download link dostupan posle kupovine (u account-u i email-u)
- Konfigurabilan broj dozvoljenih download-a i rok trajanja linka
- Admin: upload fajlova, podešavanje ograničenja

---

### 7.6 Izveštaji (dashboard, svi izveštaji, export)

#### Dashboard overview kartice
- Ukupan prihod (danas, ova nedelja, mesec, custom period)
- Broj narudžbina (isti periodi)
- Prosečna vrednost narudžbine (AOV)
- Conversion rate (posetilaca → kupaca)
- Novi vs returning kupci

#### Izveštaj: Prodaja
- Prihod po periodu (line chart, uporedi sa prethodnim periodom)
- Prodaja po danu/nedelji/mesecu
- Prodaja po payment metodi
- Prodaja po shipping zoni/državi
- Refund-ovi po periodu

#### Izveštaj: Proizvodi
- Top proizvodi po prihodu i po količini
- Proizvodi koji se najčešće gledaju ali ne kupuju (potencijalni problem)
- Low stock izveštaj
- Out of stock izveštaj
- Proizvodi bez prodaje u poslednjih X dana

#### Izveštaj: Kategorije
- Prihod po kategoriji (bar chart)
- Broj proizvoda po kategoriji

#### Izveštaj: Kupci
- Top kupci po potrošnji
- Novi kupci po periodu
- Customer lifetime value (CLV) distribucija
- Kupci sa napuštenom korpom (abandoned cart — ako se implementira)

#### Izveštaj: Kuponi
- Korišćenost po kuponu
- Prihod sa kuponom vs bez
- Najčešće korišćeni kuponi

#### Export
- Svi izveštaji exportabilni u CSV i PDF
- Scheduled export (npr. nedeljni report na admin email)

---

### 7.7 Media Library (proširenja)

Faza 1 pokriva basic Media Library (upload, grid, alt text, resize, media picker). Faza 4 dodaje:

- Folder organizacija ili tag sistem
- Prikaz gde je slika korišćena (linked entities)
- Bulk delete sa upozorenjem ako je slika u upotrebi
- Search i filter po tipu (image, video, document), datumu, veličini

---

### 7.8 Admin korisnici & Permisije

- Role: super_admin, manager, operator
- Permisije po modulu (proizvodi, narudžbine, kupci, podešavanja)
- Activity log — ko, šta, kada

---

### 7.9 Admin podešavanja (svi tabovi)

Centralizovana konfiguracija svih aspekata shopa. Organizovano po tabovima:

#### General
- Naziv prodavnice, logo, favicon
- Adresa, telefon, kontakt email
- Socijalne mreže linkovi (FB, Instagram, X, TikTok, YouTube)
- Timezone, date format

#### Valuta i cene
- Primarna valuta (kod, simbol, pozicija — pre/posle cene, decimale)
- Dodatne valute + kursevi (ručno ili API)
- Prikaz cena: sa/bez PDV-a

#### Storefront layout
- Header varijanta (A / B) — mega menu ili vertikalni sidebar
- PLP varijanta (A / B / C)
- PDP varijanta (A / B / C)
- Cart varijanta (A / B)
- Checkout varijanta (single-page / multi-step)
- Blog varijanta (A / B / C)
- Products per page default (9 / 12 / 18 / 24)

#### Top bar / Promo bar
- Uključen/isključen
- Tekst, boja pozadine, boja teksta, link
- Social ikone da/ne
- Language picker da/ne

#### Trust & Conversion (PDP)
- Stock status prikaz da/ne
- Stock urgency bar da/ne + threshold
- Countdown timer da/ne
- Shipping info tekst
- Return policy tekst
- Dispatch info tekst
- Trust badges: upload ikona payment metoda

#### Cart & Checkout
- Add to Cart feedback: drawer / toast / fly animacija
- Free shipping threshold (iznos, poruka pre/posle)
- Guest checkout da/ne
- Minimum order amount
- Order bump: proizvod, tekst

#### Product badges
- "NEW" threshold (broj dana od kreianja)
- Badge boje (konfigurabilan po tipu)

#### Email
- Sender name, sender email, reply-to
- Email template: logo, boje (primary, background, text), footer tekst
- Uključivanje/isključivanje pojedinih emailova

#### SEO defaults
- Default meta title pattern (npr. "{product_name} | {store_name}")
- Default meta description pattern
- Google Analytics ID
- Facebook Pixel ID

#### GDPR
- Cookie consent tekst
- Privacy policy stranica link
- Terms of service stranica link
- Data retention period

#### Languages
- Dostupni jezici, default jezik
- Language picker pozicija (top bar / header)

#### Notifications
- Admin email za notifikacije
- Low stock threshold
- Notify on new order da/ne
- Notify on new review da/ne

---

### 7.10 Statičke stranice

- `/o-nama` — O nama
- `/kontakt` — Kontakt forma
- `/uslovi-koriscenja` — Uslovi korišćenja
- `/politika-privatnosti` — Politika privatnosti
- `/cesta-pitanja` — Najčešća pitanja
- Admin: CRUD statičkih stranica

---

### 7.11 Webhooks, API Rate Limiting

#### Webhooks

Admin konfiguriše webhook URL-ove za automatsko obaveštavanje eksternih sistema (ERP, kurirska služba, Slack, marketing alati).

**Kako radi:**
- Admin registruje URL + bira koje evente želi da prima
- Kad se event desi → POST request na registrovani URL sa JSON payload-om
- Retry logika: ako webhook fails — 3 pokušaja sa exponential backoff (1min, 5min, 30min)
- Posle 3 fail-a: webhook se markira kao failed, admin dobije notifikaciju

**Podržani eventi:**
| Event | Trigger |
|-------|---------|
| `order.created` | Nova narudžbina |
| `order.status_changed` | Promena statusa narudžbine |
| `order.paid` | Plaćanje primljeno |
| `order.refunded` | Refund procesiran |
| `product.created` | Novi proizvod |
| `product.updated` | Izmena proizvoda |
| `product.deleted` | Brisanje proizvoda |
| `customer.registered` | Novi korisnik |
| `stock.low` | Stock pao ispod threshold-a |

**Sigurnost:**
- **Webhook secret:** svaki webhook ima HMAC secret — potpis u header-u (`X-Webhook-Signature`) koji primatelj koristi da verifikuje da request stvarno dolazi od nas, a ne od nekog trećeg

**Admin UI:**
- Webhook listing: URL, aktivni eventi, status (active/failing), secret (prikaži/kopiraj)
- Kreiranje: URL input + checkbox za svaki event (selektivan subscribe)
- **Webhook log:** istorija poslatih webhook-a — timestamp, event, URL, HTTP status, response body, retry count
- **Test webhook:** dugme "Send test" — šalje dummy payload na URL da proveri da radi
- Enable/disable webhook bez brisanja

#### API Rate Limiting
- Laravel throttle middleware na svim rutama
- **Javne rute:** 60 req/min po IP
- **Autentifikovane rute:** 120 req/min po user ID-u
- **Auth rute (login, register, forgot-password):** 5 req/min po IP — zaštita od brute force
- **Search/autocomplete:** 120 req/min (trpe više zbog debounce-a)
- **Checkout/payment:** 10 req/min (strožije, sprečava abuse)
- **Response headers:** `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `Retry-After`
- **429 response:** jasna poruka "Too many requests" + retry-after vreme
- **Whitelist:** admin IP adrese izuzete od limitiranja (konfigurabilan u .env)

---

### 7.12 Performanse & Tehničko

#### Frontend (Nuxt/Storefront)
- **Lazy loading slika** sa native `loading="lazy"` + Nuxt Image modul
- **Image optimization:** automatska WebP konverzija, responsive srcset, `contain-intrinsic-size` za CLS
- **Skeleton loaderi** za sve liste i kartice (ne spinneri)
- **Code splitting:** automatski po stranici (Nuxt default), lazy load teških komponenti (carousel, rich text editor)
- **Font optimization:** preload kritičnih fontova, `font-display: swap`
- **CSS:** Tailwind purge nekorišćenih klasa, critical CSS inline
- **Prefetch:** linkovi na hover (Nuxt default), prefetch sledećih stranica
- **Core Web Vitals targeti:** LCP < 2.5s, FID < 100ms, CLS < 0.1

#### Backend (Laravel API)
- **Eloquent optimization:** eager loading relacija (N+1 prevencija), select samo potrebnih kolona
- **API response caching:** cache popularnih query-a (homepage, categories tree) sa invalidacijom pri promeni
- **Database indexi:** slug, sku, email, status, sort_order, created_at — na svim modelima gde se filtrira/sortira
- **Pagination:** cursor-based za velike liste, limit max per page
- **Image processing:** queue job za resize/optimize pri uploadu (ne blokiraj request)
- **Query optimization:** raw query za search, composite indexi za česte filter kombinacije

#### CDN & Caching
- **Cloudflare:** cache statičkih assets-a, page rules za javne stranice
- **Browser caching:** long cache za assets sa content hash u imenu
- **API caching strategy:** stale-while-revalidate za javne endpointe

#### Tehničko
- sitemap.xml (auto-generisan, po jeziku)
- robots.txt
- Schema.org markup (Product, BreadcrumbList, Organization, FAQPage)
- 404 / 500 custom error stranice
- Canonical URL-ovi na svim stranicama

---

## 8. Infrastruktura & DevOps

### 8.1 Produkcioni setup (Hetzner, Vercel, Cloudflare)

#### Arhitektura deploymenta
- **VPS (Hetzner CX22, ~4.35€/mesec):** Laravel API instance + MySQL baze
- **Vercel (besplatno):** Storefront (Nuxt SSR) + Admin (Nuxt SPA) — po projekat za svakog klijenta
- **Cloudflare (besplatno):** DNS + CDN + DDoS zaštita
- **SSL:** Let's Encrypt (besplatno)

#### Multi-klijent model
- Svaki klijent ima svoju bazu, svoju Laravel instancu i svoje Vercel projekte
- Na VPS-u samo API + MySQL (bez Node.js) — PHP-FPM pool po klijentu (~50-100 MB RAM)
- Nuxt frontend ide na Vercel — ne troši VPS resurse

#### Skaliranje po broju klijenata

| Broj klijenata | Hetzner plan | RAM | Cena/mesec |
|---------------|-------------|-----|------------|
| do 15 | CX22 | 4 GB | 4.35€ |
| 15-30 | CX32 | 8 GB | 7.45€ |
| 30-60 | CX42 | 16 GB | 14.45€ |

#### Kad klijent preraste VPS
Razdvajanje servisa:
- Laravel API → VPS sa load balancerom (Laravel Forge)
- MySQL → Managed DB (PlanetScale, AWS RDS)
- Cache/Queue → Managed Redis (Upstash)
- Slike → S3 + CloudFront / Cloudflare R2
- Email → SES / Postmark
- Monitoring → Telescope + Sentry

---

### 8.2 Template kloniranje & Versioning & Feature flags

#### Pristup: svi klijenti na istoj verziji
- Jedan main template repo — source of truth
- Svaki klijent je fork/klon sa config razlikama (boje, logo, kategorije, dostupni feature-i)
- Kad dodaš feature u template → pull-uješ update svim klijentima
- Bug fix-evi idu svima automatski — to je popravka, ne novi feature

#### Feature flags
- **Svaki novi feature mora imati feature flag.** Jedini izuzetak su bug fix-evi
- Config fajl po klijentu (`config/features.php` ili `.env`) definiše šta je uključeno:
  ```
  FEATURE_WISHLIST=true
  FEATURE_BLOG=false
  FEATURE_REVIEWS=true
  FEATURE_LOYALTY=false
  FEATURE_GIFT_CARDS=false
  FEATURE_MULTI_LANGUAGE=false
  ```
- Backend: middleware ili helper `feature('wishlist')` — ako je false, rute vraćaju 404
- Frontend: `useFeature('wishlist')` composable — sakrije UI elemente koji nisu aktivni
- Admin panel: prikazuje samo sekcije za aktivne feature-e u sidebaru
- Nije "premium/free" model — feature flag služi da se klijentu uključi samo ono što mu treba

#### Setup novog klijenta (checklist)
1. Fork/klon template repo-a
2. Konfiguracija: naziv, logo, favicon, boje (Tailwind config), kontakt podaci
3. Feature flags: uključi/isključi po dogovoru
4. Baza: kreiranje MySQL baze + `migrate --seed`
5. DNS: podesiti domenu (Cloudflare)
6. Deploy: API na VPS (nginx vhost + PHP-FPM pool), Frontend na Vercel
7. SSL: Let's Encrypt (automatski)
8. Admin nalog: kreirati super_admin za klijenta
9. Seed data: obrisati demo podatke, klijent unosi svoje
10. Testiranje: provera svih aktivnih feature-a

#### Update klijenata
- `git pull` iz main template repo-a → resolve konflikti (ako ih ima u config-u) → deploy
- Pre update-a: backup baze
- Posle update-a: `php artisan migrate` za nove migracije
- Testiranje na staging-u pre produkcije

---

### 8.3 CI/CD & Deployment (pipeline, environments, deploy, rollback, health check)

#### Git workflow
- `main` — produkcija, stabilan, deploy-uje se automatski
- `develop` — aktivan razvoj, deploy na staging
- `feature/*` — feature grane, merge u develop kroz PR
- Svaki PR prolazi kroz pipeline pre merge-a

#### Pipeline (GitHub Actions)
Automatski se pokreće na svaki push/PR:

```
1. Lint        → PHP CS Fixer + ESLint (code style provera)
2. Test        → PHPUnit (API testovi) + TypeCheck (Nuxt)
3. Build       → Nuxt build (admin + storefront) — provera da se uopšte builda
4. Deploy      → Samo na push to main (produkcija) ili develop (staging)
```

Ako bilo koji korak padne — deploy se ne dešava, PR se ne može merge-ovati.

#### Environments

| Environment | Namena | API | Frontend | Baza |
|-------------|--------|-----|----------|------|
| **Local** | Development | `php artisan serve :8000` | `npm run dev :3000/:3001` | Lokalni MySQL |
| **Staging** | Testiranje pre produkcije | Hetzner dev VPS | Vercel preview | Staging MySQL (kopija šeme, demo podaci) |
| **Production** | Klijent koristi | Hetzner prod VPS | Vercel production | Produkciona MySQL |

- Staging ima realističan seed data (ne produkcione podatke!) — 50+ proizvoda, kategorije, narudžbine, kupci
- Env varijable potpuno odvojene po environment-u (`.env.staging`, `.env.production`)

#### Deploy proces (API — Hetzner VPS)
1. `git pull` nova verzija koda
2. `composer install --no-dev` — produkcione zavisnosti
3. `php artisan migrate --force` — migracije
4. `php artisan config:cache` — cache konfiguracije
5. `php artisan route:cache` — cache ruta
6. `php artisan view:cache` — cache view-ova (ako ih ima)
7. `php artisan queue:restart` — restart queue worker-a
8. **Zero-downtime:** koristiti Laravel Envoyer ili custom symlink deploy (novi release u novi folder, symlink prebaci na novi tek kad je sve gotovo)
9. PHP-FPM reload (ne restart) — postojeći requesti se završe, novi idu na novi kod

#### Deploy proces (Frontend — Vercel)
- Automatski na push — Vercel builda i deploy-uje
- **Preview deploys:** svaki PR dobije svoj URL za testiranje (Vercel to radi automatski)
- Produkcioni deploy samo sa `main` grane
- Rollback: jedan klik u Vercel dashboard-u na prethodni deployment

#### Rollback strategija
- **API:** svaki deploy je u zasebnom folderu sa symlinkom. Rollback = prebaci symlink na prethodni folder (< 5 sekundi)
- **Frontend:** Vercel instant rollback na prethodni build
- **Baza:** migracije moraju biti backward-compatible. Nikad ne briši kolonu u istom deploy-u kad ukloniš kod koji je koristi — uradi u dva deploy-a (1: ukloni kod, 2: ukloni kolonu)

#### Health check
- `GET /api/health` — endpoint koji proverava:
  - API je aktivan (status 200)
  - Baza je dostupna (test query)
  - Storage je writable (test write/delete)
  - Queue je aktivan (test dispatch)
  - Redis dostupan (ako se koristi)
- UptimeRobot ili Better Stack pinga ovaj endpoint svakih 1-5 min
- Alert na email/Slack ako padne

---

### 8.4 Database Backup

#### Automatski backup-i
- **Dnevni full backup:** mysqldump cele baze — svaki dan u 3:00 AM (cron job na VPS-u)
- **Čuvanje:** poslednjih 7 dnevnih + 4 nedeljnih + 3 mesečnih backup-a (rotacija)
- **Storage:** backup fajlovi se uploaduju na eksterni storage (Cloudflare R2 / S3 / Hetzner Storage Box) — nikad samo na istom VPS-u
- **Kompresija:** gzip (smanjuje veličinu 5-10x)

#### Restore procedura
- Dokumentovana step-by-step procedura za restore
- Testirati restore bar jednom mesečno (na staging-u) — backup koji nisi testirao nije backup

#### Pre-migration backup
- Automatski backup pre svakog `php artisan migrate` na produkciji
- Ako migracija padne — restore iz tog backup-a

#### Media fajlovi
- Slike i uploadovani fajlovi: sync na S3/R2 (ne samo lokalno)
- Ako su na lokalnom storage-u: uključiti u backup (rsync na eksterni storage)

---

### 8.5 Monitoring & Logging (Sentry, Telescope, uptime, alerting)

#### Error tracking — Sentry
- Integrisan u Laravel (API) i oba Nuxt projekta (admin, storefront)
- Automatski hvata sve unhandled exception-e
- Source maps za frontend (čitljivi stack trace-ovi)
- Alert na email pri novom error-u
- Grupisanje istih errora, praćenje regression-a

#### Application logging — Laravel
- **Laravel Telescope** (samo na staging/local, ne na produkciji — performance)
- Log levels: emergency, alert, critical, error, warning, notice, info, debug
- Produkcija: loguje error+ nivo, ne debug
- Log rotacija: dnevni fajlovi, čuvaj 14 dana
- Structured logging za lakši parsing

#### Uptime monitoring
- **UptimeRobot ili Better Stack** (besplatan tier)
- Ping `/api/health` svakih 5 min
- Alert na email + Slack pri downtime-u
- Status page URL za klijente (opciono)

#### Performance monitoring
- Slow query log: MySQL loguje query-e duže od 1s
- API response time: loguje se za svaki request (middleware)
- Monthly review: top 10 najsporijih endpoint-a → optimizacija

#### Alerting pravila
- **Error spike:** više od X novih errora u 5 min → alert
- **Response time:** prosečan API response > 2s → alert
- **Disk usage:** > 80% → warning, > 90% → critical
- **Memory usage:** > 85% → alert
- **Downtime:** health check fails 2x zaredom → alert
- **Failed jobs:** queue job failed → alert sa detaljima
- **Security:** neuspeli login pokušaji > 20/min sa iste IP → alert (potencijalni brute force)
- Kanali: email (uvek) + Slack webhook (opciono)

---

## 9. Konvencije & Razvoj

### 9.1 Backend konvencije (Laravel)
- API-only, nema Blade view-ova
- Svi odgovori u JSON formatu sa konzistentnom strukturom:
  ```json
  { "data": {}, "message": "Success" }
  ```
- Greške:
  ```json
  { "message": "Error text", "errors": { "field": ["..."] } }
  ```
- Form Request klase za validaciju
- Resource klase za API transformaciju
- Policy klase za autorizaciju
- Sve rute pod verzijom: `/api/v1/...`
- Testovi: Feature testovi za sve API rute

### 9.2 Frontend konvencije (Nuxt)
- Composition API (script setup)
- TypeScript strict mode
- Pinia za state management
- Composables za reusable logiku (useCart, useAuth, useApi...)
- Tailwind utility-first, komponente sa @apply gde ima smisla

#### Folder struktura
```
components/    — UI komponente (po strukturi ispod)
composables/   — reusable logika
layouts/       — layout wrapper-i
middleware/    — auth guards
pages/         — rute
stores/        — Pinia stores
types/         — TypeScript tipovi
utils/         — helper funkcije
```

#### Komponentna arhitektura

Tri nivoa: **atomi** → **molekuli** → **domenski folderi**. Nema "common" foldera — svaka komponenta ima tačno jedno mesto.

```
components/
├── ui_atoms/        ← jedan element, jedna stvar, nula biznis logike
│   ├── Button.vue
│   ├── Input.vue
│   ├── Badge.vue
│   ├── Icon.vue
│   ├── Spinner.vue
│   ├── Avatar.vue
│   ├── Checkbox.vue
│   ├── Radio.vue
│   ├── Switch.vue
│   ├── Skeleton.vue
│   └── Tooltip.vue
│
├── ui_molecules/    ← 2-3 atoma spojeni, reusable, nula biznis logike
│   ├── SearchBar.vue
│   ├── QuantitySelector.vue
│   ├── RatingStars.vue
│   ├── Modal.vue
│   ├── Toast.vue
│   ├── DataTable.vue
│   ├── Dropdown.vue
│   ├── Tabs.vue
│   ├── Accordion.vue
│   ├── FileUpload.vue
│   ├── PriceDisplay.vue
│   ├── SocialShare.vue
│   ├── SocialIcons.vue
│   ├── TrustBadges.vue
│   └── Newsletter.vue
│
├── layout/          ← školjka stranice, uvek vidljivi
│   ├── Header.vue
│   ├── Footer.vue
│   ├── MegaMenu.vue
│   ├── MobileNav.vue
│   ├── Sidebar.vue
│   ├── Breadcrumbs.vue
│   └── CookieConsent.vue
│
├── product/         ← zna za Product, ima biznis logiku
│   ├── ProductCard.vue
│   ├── ProductGrid.vue
│   ├── ProductPrice.vue
│   ├── ProductGallery.vue
│   └── QuickView.vue
│
├── cart/
│   ├── CartDrawer.vue
│   ├── CartItem.vue
│   └── CartTotals.vue
│
└── checkout/
    ├── CheckoutForm.vue
    └── OrderSummary.vue
```

**Pravilo za klasifikaciju:**
- Prima samo generičke props (label, color, size, disabled) → **ui_atoms/**
- Kombinuje atome, i dalje ne zna za biznis domene → **ui_molecules/**
- Deo školjke stranice, uvek vidljiv → **layout/**
- Zna šta je Product, Cart, Order, User → **domenski folder** (product/, cart/, checkout/, account/, blog/, admin/...)

Domenski folderi rastu po fazama — u Fazi 2 dolaze `account/`, `order/`, `review/`, u Fazi 3 dolazi `coupon/` itd.

#### State management

**Pinia store** — globalni state koji više komponenti/stranica deli:
- `useCartStore` — stavke u korpi, totali
- `useAuthStore` — ulogovan korisnik, token
- `useWishlistStore` — wishlist stavke
- `useCompareStore` — compare lista

**Composable** — reusable logika izdvojena u fajl jer je koriste 2+ komponente:
- `useApi()` — wrapper za fetch pozive
- `useCart()` — facade za cartStore (add, remove, updateQty)
- `useAuth()` — facade za authStore (login, logout, isLoggedIn)
- `useFeature('wishlist')` — provera feature flag-a
- `useBreakpoint()` — trenutni breakpoint
- `useDebounce()` — debounce helper

**Lokalni state (ref/reactive)** — state koji nikad ne napušta komponentu:
- Form input vrednosti, modal open/close, accordion state, loading state dugmeta, hover state

Composable i lokalni state koriste isti Vue reactivity sistem (ref, reactive, computed, watch). Razlika je samo gde živi kod — composable u zasebnom fajlu, lokalni state inline u komponenti.

**Pravilo:** počni sa lokalnim state-om. Tek kad drugi put zatreba ista logika — izvuci u composable. Ne praviti composable unapred "za svaki slučaj". Ako se pitaš "da li ovo treba da bude store?" — verovatno ne. Store samo kad dva nepovezana dela stranice moraju da dele isti state.

#### API layer

**`useApi()` composable** — centralni wrapper za sve API pozive:
- Base URL iz env varijable (`NUXT_PUBLIC_API_BASE`)
- Automatski dodaje Sanctum token u header (iz authStore)
- Standardizovan error handling: 401 → redirect na login, 422 → vrati validation errors, 500 → toast error
- Return format: `{ data, error, loading }`
- Nuxt `useFetch` / `useAsyncData` pod haubom (SSR-safe)

**Pravila:**
- Nikad direktan `fetch()` ili `$fetch()` u komponentama — uvek kroz `useApi()` ili domenski composable
- Domenski composables koriste `useApi()` interno:
  ```
  useProducts().getAll()     → useApi().get('/products')
  useProducts().getBySlug()  → useApi().get('/products/:slug')
  useCart().addItem()        → cartStore + toast
  ```
- Loading state: uvek prikaži skeleton ili spinner dok čekaš API
- Error state: uvek prikaži error poruku, nikad prazan ekran
- Retry: automatski retry za network error (max 2 pokušaja), ne za 4xx greške
- Cache: koristi Nuxt keying za cache (`useFetch` sa unique key), invalidacija pri mutaciji

#### TypeScript pravila

**Gde se definišu tipovi:**
```
types/
├── product.ts      ← Product, Category, ProductImage, ProductVariant
├── cart.ts         ← CartItem, Cart
├── order.ts        ← Order, OrderItem, Address
├── user.ts         ← User, AuthResponse
├── api.ts          ← ApiResponse<T>, PaginatedResponse<T>, ApiError
└── common.ts       ← SortOption, FilterOption, Breadcrumb
```

**Pravila:**
- Interfejsi za API response objekte (`interface Product { ... }`)
- Type za union tipove i aliase (`type OrderStatus = 'pending' | 'processing' | 'shipped'`)
- Enumi NE koristiti — Vue reactivity ne radi dobro sa njima. Koristiti `as const` objekte:
  ```typescript
  export const ORDER_STATUS = { PENDING: 'pending', PROCESSING: 'processing' } as const
  type OrderStatus = typeof ORDER_STATUS[keyof typeof ORDER_STATUS]
  ```
- Svaki composable i store mora imati jasne tipove za parametre i return
- Bez `any` — koristiti `unknown` ako tip nije poznat, pa narrowing
- API response uvek tipiziran: `useApi().get<Product[]>('/products')`

#### CSS / Tailwind pravila

**Grid vs Flex:**
- **Flexbox** za jednodimencionalne layoute: navigacija, button grupe, card sadržaj vertikalno, form redovi, centriranje
- **CSS Grid** za dvodimenzionalne layoute: product grid, category grid, dashboard kartice, checkout dve kolone, footer kolone, blog grid
- Praktično pravilo: razmišljaš o **kolonama** → Grid. Razmišljaš o **poravnavanju u redu** → Flex. **Responsivan broj kolona** → Grid

**@apply pravila:**
- Koristiti SAMO za komponente koje se ponavljaju sa istim skupom klasa (npr. `.btn-primary`, `.card`, `.input`)
- Nikad @apply za layout — to uvek inline Tailwind klase
- Max 5-7 utility-ja u jednom @apply. Ako treba više — razmisli da li je to uopšte jedna komponenta
- Preferirati Tailwind utility klase inline — @apply je izuzetak, ne pravilo

**Custom klase:**
- Ne praviti custom CSS klase osim za @apply komponente i third-party library override-ove
- Imenovanje: kebab-case, BEM nije potreban uz Tailwind (`btn-primary`, `card-featured`, ne `btn__icon--large`)
- Animacije: definisati u `tailwind.config` kao extend, ne u custom CSS

#### File/folder naming konvencije

**Komponente:** PascalCase (`ProductCard.vue`, `CartDrawer.vue`)
**Composables:** camelCase sa `use` prefiksom (`useCart.ts`, `useApi.ts`)
**Stores:** camelCase sa `use` prefiksom i `Store` sufiksom (`useCartStore.ts`)
**Tipovi:** camelCase fajl, PascalCase interfejsi (`product.ts` → `interface Product`)
**Stranice:** kebab-case po Nuxt konvenciji (`proizvod/[slug].vue`, `nalog/narudzbine/[number].vue`)

#### URL konvencije (storefront)

URL-ovi su **uvek na srpskom**, bez obzira na izabrani jezik. Jezik menja samo sadržaj stranice, ne URL. Prefiks po jeziku za sadržaj (`/en/` itd.) se NE koristi.

| Ruta | URL |
|------|-----|
| Prodavnica | `/prodavnica` |
| Proizvod | `/proizvod/:slug` |
| Kategorija | `/kategorija/:slug` |
| Korpa | `/korpa` |
| Checkout | `/kasa` |
| Checkout uspeh | `/kasa/uspeh/:number` |
| Pretraga | `/pretraga` |
| Nalog | `/nalog` |
| Profil | `/nalog/profil` |
| Adrese | `/nalog/adrese` |
| Narudžbine | `/nalog/narudzbine` |
| Detalj narudžbine | `/nalog/narudzbine/:number` |
| Wishlist | `/nalog/lista-zelja` |
| Prijava | `/nalog/prijava` |
| Registracija | `/nalog/registracija` |
| Blog | `/blog` |
| Blog post | `/blog/:slug` |
| Kontakt | `/kontakt` |
| O nama | `/o-nama` |
| FAQ | `/cesta-pitanja` |
| Uslovi | `/uslovi-koriscenja` |
| Privatnost | `/politika-privatnosti` |
| Compare | `/uporedi` |
| Landing stranice | `/stranica/:slug` |
| Blog kategorija | `/blog/kategorija/:slug` |
| Blog tag | `/blog/tag/:slug` |
| Zaboravljena lozinka | `/nalog/zaboravljena-lozinka` |
| Poklon kartica provera | `/poklon-kartica/provera` |
| Praćenje pošiljke | `/pracenje/:tracking_number` |
| Obnovi korpu | `/korpa/obnovi/:token` |
| Shop the Look | `/izgled` |

Slug-ovi proizvoda, kategorija i blog postova ostaju isti na svim jezicima (generišu se iz srpskog naziva)
**Utils:** camelCase (`formatPrice.ts`, `slugify.ts`)

**Pravilo:** nikad `index.vue` u komponentama — uvek eksplicitno ime. Jedini izuzetak: `pages/index.vue` (homepage).

### 9.3 Git workflow
- main branch — stabilan, production-ready
- develop branch — aktivan razvoj
- feature/* grane za svaku celinu
- Konvencionalne commit poruke (feat:, fix:, chore:, docs:)

### 9.4 Pokretanje projekta (dev setup, env varijable)

```bash
# API
cd api
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8000

# Admin
cd admin
npm install
npm run dev -- --port=3001

# Storefront
cd storefront
npm install
npm run dev -- --port=3000
```

#### Env varijable
- API: `APP_URL=http://localhost:8000`, `FRONTEND_URL=http://localhost:3000`, `ADMIN_URL=http://localhost:3001`
- Storefront: `NUXT_PUBLIC_API_BASE=http://localhost:8000/api/v1`
- Admin: `NUXT_PUBLIC_API_BASE=http://localhost:8000/api/v1`

---

## 10. Napomene za Claude Code

### Filozofija projekta
- **Svaka stvar mora da radi besprekorno, koliko god vremena bilo potrebno.** Nema "dovoljno dobro" — ili radi savršeno ili se ne isporučuje
- **Gledamo široko i planiramo.** Bolje predvideti 10 edge case-ova unapred nego gasiti požare posle
- Nema prečica, nema tehničkog duga na kredit. Svaki komad koda je finalni proizvod

### Planiranje pre kodiranja
- **x3 vreme na planiranje nego na kucanje.** Razmisli tri puta, kodiraj jednom
- Pre svakog modula: napiši plan (koji fajlovi, koje klase, koje relacije, koji edge cases) → čekaj potvrdu → onda kodiraj
- Kad sediš da pišeš kod — treba da znaš tačno šta pišeš. Ako razmišljaš dok kucaš, stani i vrati se na planiranje
- Bolje potrošiti 30 minuta na analizu nego 2 sata na refaktor posle

### Clean Code — bez kompromisa
- **Uvek po standardima.** Nema "brzo da radi pa ćemo refaktorisati" — to se nikad ne desi
- Laravel: striktno poštuj konvencije — naming, folder struktura, service/repository pattern gde ima smisla
- SOLID principi: svaka klasa ima jednu odgovornost, dependency injection, interfejsi za eksterne servise
- DRY ali ne na silu — duplikacija je bolja od pogrešne apstrakcije
- Čitljivost > kratkost — kod se čita 10x više nego što se piše
- Imenovanje: jasno, deskriptivno, konzistentno. Ako ime zahteva komentar — ime je loše
- Metode kratke (max 20-30 linija). Ako je duža — razbij na manje
- Nesting max 2-3 nivoa. Koristi early return, guard clauses
- Commit samo kod koji bi pokazao na code review-u bez sramote

### Redosled rada
- Kreni od **Faze 1** — kompletno je završi pre prelaska na sledeću
- Unutar svake faze, redosled: **API → Admin → Storefront**
- Nemoj da praviš placeholder fajlove za buduće faze — samo trenutnu fazu
- Ako nešto nije dovoljno specificirano, napravi razumnu pretpostavku i nastavi

### Backend (API) pravila
- Svaki model treba da ima: migraciju, model, factory, seeder, controller, resource, form request, rute
- Feature testovi za svaki API endpoint — happy path + validacija + auth + edge cases
- Pokreni testove posle svakog završenog modula (`php artisan test`)
- Seederi moraju biti idempotentni (safe za re-run)
- Koristi Policy klase za autorizaciju, ne inline provere u controllerima
- Svi API odgovori kroz Resource klase — nikad direktan model return

### Frontend pravila
- Admin panel treba da bude funkcionalan i upotrebljiv — loading stanja, validacija, toast poruke, skeleton loaderi
- Storefront treba da bude vizuelno čist i modern — Tailwind, responsive, SSR
- TypeScript strict mode — bez `any` tipova
- Svaki composable i store sa jasnim TypeScript tipovima u `types/`
- Komponente: mali, single-responsibility, reusable gde ima smisla
- Korpa u Fazi 1 je čisto client-side (localStorage + Pinia store)

### Testiranje
- **API:** Feature testovi za svaki endpoint (PHPUnit) — minimum: CRUD operacije, auth guard, validacija, paginacija
- **Admin:** Ručno testiranje (nema automatizovanih UI testova u Fazi 1)
- **Storefront:** Ručno testiranje + provera SSR renderovanja (`curl` ili browser)
- Svaki bug fix mora imati test koji pokriva taj scenario
- Pokreni ceo test suite pre commitovanja

### Kvalitet koda
- Ne ostavljaj TODO/FIXME komentare — ili reši odmah ili ne diraj
- Ne dodaj zakomentarisan kod
- Ne pravi helper/util fajl dok nemaš bar 2 mesta koja ga koriste
- Error handling: centralizovano (Laravel exception handler, Nuxt error.vue), ne try/catch svuda
- Console.log/dd() — nikad u ukomitovanom kodu

### Komunikacija
- Posle svakog završenog modula (npr. "Product CRUD API") — kratko reportuj šta je urađeno i pokreni testove
- Ako naiđeš na arhitekturnu odluku koja nije pokrivena specifikacijom — pitaj pre nego što izabereš
