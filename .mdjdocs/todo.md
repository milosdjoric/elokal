# Webshop Template — TODO & Verzije

Verzija format: `v0.{faza}.{sekcija}` — svaka sekcija = jedan release.

---

## v0.2.x — Faza 2: Kupovina & Nalozi

### v0.2.1 — Auth: API

- [ ] Social login endpoints (Google, Facebook, Apple — Laravel Socialite)

### v0.2.2 — Auth: Storefront

- [ ] Social login dugmad (Google, Facebook, Apple)

### v0.2.3 — Auth: Admin 2FA

- [ ] TOTP setup (QR kod + backup kodovi)
- [ ] 2FA verification pri loginu
- [ ] Obavezno za super_admin, opciono za ostale

### v0.2.8 — Checkout: Storefront

- [ ] Smart defaults (država iz IP geolokacije)
- [ ] Address Autofill (Google Places API)

### v0.2.10 — Orders: Admin

- [ ] Resend email

### v0.2.11 — Print / PDF

- [ ] Download iz kupac account-a
- [ ] Auto-email invoice kao attachment
- [ ] Bulk print (ZIP sa više invoice-a)
- [ ] Admin podešavanja: branding (logo, boje), legal info (PIB, matični broj, PDV tekst)

### v0.2.14 — Reviews: Admin

- [ ] Email notifikacija za novu recenziju

### v0.2.21 — Blog: Admin

- [ ] Rich text editor
- [ ] Featured image + inline slike (media library)

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

- [ ] Email notifikacija adminu

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
- [ ] Rich text editor — TipTap ili Quill za blog content editor — v0.2.21
- [ ] API dokumentacija — OpenAPI 3.0 spec + Swagger UI (l5-swagger ili scramble) — v0.2.24

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

---

## v0.3.x — Faza 3: Napredna prodaja

### v0.3.9 — Dostava: Admin + Storefront

- [ ] Email sa tracking brojem

### v0.3.10 — Plaćanje: API

- [ ] Stripe integracija (Elements, 3D Secure, save card)
- [ ] Testovi

### v0.3.11 — Plaćanje: Admin + Storefront

- [ ] Admin: test/live mode toggle
- [ ] Admin: API credentials (enkriptovano)
- [ ] Checkout: Stripe Elements inline forma
- [ ] Checkout: save card za buduće kupovine
- [ ] Saved payment methods u account-u (`/nalog/sacuvane-kartice`)

### Odloženo — Faza 3

> Stavke koje zahtevaju eksternu zavisnost, velik UI rad ili SMTP.

**Eksterno:**

- [ ] Stripe integracija (API ključ) — v0.3.10
- [ ] Email sa tracking brojem — v0.3.9

**UI-heavy (iterativno sa dizajnom):**

**Backend (manja prioritetnost):**

---

## v0.4.x — Faza 4: Proširenja

### v0.4.1 — Gift Cards

- [ ] Email primaocu sa kodom

### v0.4.20 — Performanse & Tehničko

- [ ] Image optimization (WebP, responsive srcset)
- [ ] Critical CSS inline

---

## DevOps (paralelno sa razvojem)

### D.1 — CI/CD

- [ ] Staging environment setup (Hetzner dev VPS + Vercel preview)
- [ ] Staging seed data (50+ proizvoda, realistični podaci)
- [ ] Zero-downtime deploy (symlink strategy)

### D.2 — Monitoring

- [ ] Sentry setup (API + admin + storefront)
- [ ] UptimeRobot / Better Stack setup
- [ ] Slow query log (MySQL)
- [ ] Alerting pravila (error spike, response time, disk, memory, security)

### D.3 — Backup

- [ ] Upload na eksterni storage (R2/S3)
- [ ] Media sync na eksterni storage
- [ ] Mesečni test restore na staging-u

## Sloj storefront (klijent) — Pangaia redizajn

> Vizuelni uzori: Vitra (layout) + Pangaia (brand). Vidi `.mdjdocs/notes/vizuelni-uzori-storefront.md`.
> Sve niže je urađeno **lokalno** u `clients/sloj/storefront/` — NIJE commit-ovano ni deploy-ovano na Vercel.

- [x] Header redizajn (Pangaia stil): crni announcement bar, logo centriran, nav levo (CAPS), full-bleed red, niža visina (h-14)
- [x] Font: Afacad Flux primenjen svuda (link + tailwind `sans` + CSS `html`); JetBrains Mono ostaje za mono
- [ ] Commit + push + Vercel redeploy (header + font da se vide na živom `elokal-sloj-web.vercel.app`)
- [ ] Provera: kategorije se ne učitavaju na živom sajtu (samo Magazin/Inspiracija) — zaseban od redizajna, istražiti API
- [ ] (opc.) Ukloniti nekorišćen Quicksand font-link nasleđen iz base elokal layera

## Feature flags cleanup — FLAGS-001

> Detalji i acceptance criteria: `.mdjdocs/tasks/FLAGS-001.md`

- [ ] [SECURITY] Zastititi CheckoutController store credit logiku sa `feature('store_credits')` gate-om (linije ~46, ~149-153)
- [ ] [SECURITY] Premestiti webhook zastitu sa Sidebar.vue na same API rute (`api/routes/api.php` ~270-275)
- [ ] Auditi mrtve flagove (`feature_wishlist`, `feature_compare`, `feature_multi_currency`) — za svaki: implementiraj proveru ili obrisi toggle
- [ ] Konsolidovati abandoned cart na jedan kljuc (`feature_abandoned_cart`); ukloniti `cart_feature_abandoned_cart` iz ExitIntentPopup.vue
- [ ] Izloziti `feature_store_locator`, `feature_downloads`, `feature_multi_language` u Settings → Feature Flags UI
- [ ] Kreirati centralni registry/enum za sve kljuceve flagova i zameniti string literale
