# eLokal — Setup Checklist za novog klijenta

## 1. Server priprema

- [ ] PHP 8.2+ instaliran
- [ ] Composer instaliran
- [ ] Node.js 20+ instaliran
- [ ] MySQL/PostgreSQL baza kreirana
- [ ] Nginx/Apache konfigurisan

## 2. API (Laravel)

```bash
cd api
cp .env.example .env
```

- [ ] Podesiti `.env`:
  - `APP_NAME` — ime prodavnice
  - `APP_URL` — URL API-ja
  - `DB_*` — konekcija ka bazi
  - `MAIL_*` — SMTP konfiguracija
  - `FRONTEND_URL` — URL storefront-a
- [ ] `composer install --no-dev`
- [ ] `php artisan key:generate`
- [ ] `php artisan migrate`
- [ ] `php artisan db:seed`
- [ ] `php artisan storage:link`
- [ ] Konfigurisati queue worker: `php artisan queue:work`
- [ ] Konfigurisati scheduler: `* * * * * cd /path && php artisan schedule:run`

## 3. Admin Panel (Nuxt)

```bash
cd admin
npm ci
```

- [ ] Podesiti `.env`:
  - `NUXT_PUBLIC_API_BASE` — URL ka API-ju
- [ ] `npx nuxi build`
- [ ] Servirati iz `.output/public/` (SPA mode)

## 4. Storefront (Nuxt)

```bash
cd storefront
npm ci
```

- [ ] Podesiti `.env`:
  - `NUXT_PUBLIC_API_BASE` — URL ka API-ju
- [ ] `npx nuxi build`
- [ ] Pokrenuti: `node .output/server/index.mjs`

## 5. Inicijalna konfiguracija (Admin panel)

### Opšta podešavanja
- [ ] Naziv prodavnice
- [ ] Logo upload
- [ ] Kontakt email i telefon
- [ ] Adresa prodavnice

### Prodavnica
- [ ] Valuta (RSD default)
- [ ] Poreske stope (PDV 20% podrazumevano)
- [ ] Shipping zone i metode dostave
- [ ] Payment metode (pouzeće, virman)
- [ ] Carrier konfiguracija (D Express, Pošta Srbije, itd.)

### Feature flagovi
- [ ] Pregledati i uključiti/isključiti po potrebi:
  - `feature_wishlist`
  - `feature_newsletter`
  - `feature_compare`
  - `feature_social_proof`
  - `feature_store_credits`
  - `feature_gift_cards`
  - `feature_loyalty`
  - `feature_abandoned_cart`
  - `feature_shop_the_look`
  - `feature_store_locator`
  - `feature_downloads`
  - `feature_webhooks`
  - `feature_multi_currency`

### Sadržaj
- [ ] Kreirati kategorije proizvoda
- [ ] Dodati proizvode (ili CSV import)
- [ ] Kreirati statičke stranice (O nama, Kontakt, Uslovi, Politika privatnosti, FAQ)
- [ ] Postaviti blog postove (opciono)
- [ ] Dodati banere na homepage

### Email
- [ ] Konfigurisati SMTP u `.env`
- [ ] Podesiti email branding (logo, boje, footer tekst)
- [ ] Testirati transakcione emailove

### Notifikacije
- [ ] Admin email za notifikacije
- [ ] Low stock threshold
- [ ] Uključiti/isključiti notifikacije po tipu

## 6. SSL i domen

- [ ] SSL sertifikat instaliran
- [ ] Domen konfigurisan (A record ili CNAME)
- [ ] HTTPS redirect aktivan
- [ ] CORS podesiti u Laravel config-u

## 7. Monitoring i backup

- [ ] Health check endpoint proveriti: `GET /api/health`
- [ ] Backup cron aktivan (dnevni, nedeljni, mesečni)
- [ ] Sentry ili sličan error tracking (opciono)
- [ ] UptimeRobot ili sličan uptime monitoring (opciono)

## 8. Pre-launch provera

- [ ] Testirati registraciju i login
- [ ] Testirati checkout flow (korpa → checkout → narudžbina)
- [ ] Testirati payment metode
- [ ] Proveriti email slanje
- [ ] Proveriti SEO meta tagove
- [ ] Proveriti sitemap.xml
- [ ] Proveriti robots.txt
- [ ] Proveriti responsive dizajn (mobile/tablet/desktop)
- [ ] Google Analytics / Tag Manager setup
- [ ] GDPR cookie consent konfigurisan
