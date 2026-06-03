# [CORE-002] Headless Flavor A: deljen API/admin kod + deploy/baza po klijentu

**Tip:** infra
**Status:** 📋 todo
**Referenca:** `.mdjdocs/notes/arhitektura-core-sloj.md`
**Zamenjuje:** CORE-001 (fork model, parkiran)

## Cilj

Pripremiti repo da jedan deljen API i admin kod opsluzuje vise klijenata, svaki kao
**zaseban deploy sa svojom Postgres bazom, svojim `.env` i pinovan na svoj git tag**.
Storefront ostaje per-klijent (tamo zive vizuelne razlike). Bez `tenant_id`, bez
multi-tenancy refaktora — svaka baza je "jedna prodavnica", sto kod vec pretpostavlja.

Procena: **~2-3 dana** za core spremnost + pilot sa 1 klijentom.

---

## Faza 0 — Odluke i priprema

- [ ] Privatna GitHub org/namespace (npr. `tvoj-org`)
- [ ] Potvrditi: ovaj repo ostaje `elokal` (izvor proizvoda); klijentski deployi vuku iz njega
- [ ] Odluka pin: **tag (semver, preporuka)** vs commit
- [ ] **Odluka storefront strategije** (vidi Faza 3) — pre nego se krene

---

## Faza 1 — API spreman za per-client deploy (jezgro posla)

### 1a. SQLite -> Postgres
- [x] `DB_CONNECTION=pgsql` + Postgres servis lokalno — **postgresql@17 na portu 5433**,
      baza `elokal`, user `milosdjoric` (trust). `.env` prebacen (backup: `api/.env.sqlite-backup`).
      PHP 8.2 preko `/opt/homebrew/opt/php@8.2/bin/php` (sistemski php je 8.1).
- [x] **`DATE(created_at)` NIJE mina** — empirijski radi na Postgres 17 (`select DATE(...)` OK).
      Prvobitna pretpostavka iz audita pogresna; ReportController ostaje kako jeste.
- [x] **Svih 72 migracije prolaze na pgsql** — popravljeno 7 ordering problema (forward-FK na
      parovima sa istim timestamp-om; SQLite tolerantan, pg nije). `git mv` 1s ranije za roditelje:
      orders, reviews, posts, tags, attributes, shipping_zones, gift_cards.
- [x] **Seederi prolaze na pgsql** (`migrate:fresh --seed`). Popravljeno:
      (a) `AttributeVariantSeeder` `substr('Bež',0,3)` cepao multibyte slovo ž -> nevalidan
      UTF-8 0xc5 -> `mb_substr`; (b) `FullDemoSeeder` ubacivao `download_logs.order_id=null`
      u NOT NULL kolonu -> guard "samo ako narudzbina postoji".
- [x] **phpunit protiv pgsql** — phpunit.xml prebacen na `pgsql` + zasebna baza `elokal_test`
      (HOST/PORT/USER iz .env). CI (`.github/workflows/ci.yml`) dobio Postgres 17 servis +
      `pdo_pgsql` ekstenzije. Prelazak otkrio **veliku minu**: 29 `'like'` pretraga case-sensitive
      na pg (SQLite case-insensitive) — svaki search box (proizvodi/narudzbine/kupci/blog/...)
      ne bi nasao "Laptop" za upit "laptop". Fix: `'like'` -> `'ilike'` u 13 kontrolera.
      phpunit na pg: 244/246 (ista 2 pre-postojeca PublicCategoryTest pada).
  - [ ] TODO: Playwright E2E (124) protiv pgsql backenda — zaseban korak (treba podignut stack).
  - [ ] TODO (van CORE-002): 2 pre-postojeca pada PublicCategoryTest (vidljivost proizvoda) —
        prave CI crvenim, vredi popraviti zasebno.

### 1b. Externalizacija klijent-specificnih vrednosti  ✅ ZAVRSENA
- [x] `RSD` literal -> **izvor istine je `Currency` model** (NE nov setting; valuta vec
      postoji: `code/symbol/is_default`, `Currency::getDefault()`, default seedovan RSD).
      Bolje od settinga jer je jedan izvor, po-bazi -> po-klijentu. Dodato:
      `app/Helpers/currency.php` (`currency_symbol()` kesirano zbog N+1 u listama +
      `forget_currency_cache()`), registrovan u composer autoload `files`. Zamenjeno 5 literala
      (Product accessor, Coupon/Order/Loyalty controlleri). Cache invalidacija u CurrencyController
      (store/update/destroy — `where()->update()` ne okida evente). Dokazano: EUR default -> `€`.
- [~] Loyalty rate **brojevi** (100/50) — ostavljeni hardkodovani, oznaceni `TODO(CORE-002 1b)`
      u LoyaltyController. Externalizovati samo ako loyalty stope treba per-klijent (dira i
      CheckoutController redemption logiku). Zaseban, manji follow-up.
- [x] Grep za ostale brend/kontakt/domen hardkode u `app/` -> **prazan** (mail je vec env-driven).
- [x] `.env.example` DB blok -> pgsql + napomena "per klijent svoja baza" + 5433 dev quirk.
      (APP_NAME/APP_URL/MAIL_FROM su ocigledno per-deploy, vec u templejtu.)

### 1c. Provera izolacije
- [ ] Dve lokalne baze + isti kod + dva razlicita `.env` -> dva "razlicita" shopa
      (razliciti naziv/valuta/feature flagovi) bez ijedne izmene koda

---

## Faza 2 — Deploy: Vercel + Railway + Neon (managed)

Topologija u `.mdjdocs/notes/arhitektura-core-sloj.md`. Repo-strana je pripremljena
(vidi Progress 2026-06-03); ostaje provizioniranje naloga/projekata (tvoji nalozi).

### Repo-priprema  ✅ ZAVRSENA
- [x] Laravel `pgsql` cita `DB_URL` + `DB_SSLMODE` (Neon-ready, bilo od ranije).
- [x] `api/railway.json` — `preDeployCommand: php artisan migrate --force` (migrate na svaki deploy).
- [x] `api/.env.example` — Neon sekcija (DB_URL direct endpoint + sslmode).
- [x] **admin externalizovan**: `API_BASE` bio hardkodovan -> sad `runtimeConfig.public.apiBase`
      (`NUXT_PUBLIC_API_BASE`). storefront je to vec imao.

### Provizioniranje po klijentu (tvoji nalozi — runbook)
- [ ] **Neon**: projekat/baza po klijentu. Uzmi DIRECT connection string (bez `-pooler`).
- [ ] **Railway**: novi servis iz `elokal-api` repoa, Root Directory = `api`, builder Nixpacks.
      Env: `APP_KEY` (`php artisan key:generate --show`), `APP_ENV=production`, `APP_URL`,
      `DB_URL` (Neon), `DB_SSLMODE=require`, `MAIL_*`, payment keys. Deploy -> `preDeployCommand`
      migrira automatski. (Prvi put: i `db:seed` osnovnih podataka, rucno preko Railway shell.)
- [ ] **Vercel x2**: storefront (SSR) i admin (SPA), svaki iz svog Nuxt foldera kao Root.
      Env: `NUXT_PUBLIC_API_BASE` = Railway API URL klijenta. Build-time boja/logo override.

### Version pinning = grana po klijentu
- [ ] Semver tagovi na repou (`v1.0.0`; breaking = major bump).
- [ ] Po klijentu deploy grana `deploy/<klijent>` koja pokazuje na izabrani tag.
      Vercel+Railway servisi tog klijenta deployuju iz te grane.
- [ ] **Update klijenta** = `git checkout deploy/acme && git merge v1.1.0 && git push`
      -> auto-deploy SAMO acme (Railway migrira, Vercel rebuild). Ostali netaknuti.
- [ ] Migrate je automatski (Railway `preDeployCommand`) — nema rucnog 5x.

---

## Faza 3 — Storefront po klijentu (odluka + izvedba)

> Odluka koja zavisi koliko "poneka feature razlika" ide daleko:

- [x] **Opcija 1 — config-driven (IZABRANO, zapoceto):** jedan deljen storefront,
      brending iz **API `settings`** (po-bazi -> po-klijentu), NE hardkodovano. Otkriveno:
      sav brend je vec u settings (`site_name/logo/email/phone/address/social_*`), storefront
      ga samo nije koristio. Uradjeno:
      - `layouts/default.vue`: `await loadSettings()` SSR-side (brend u prvom renderu) +
        ispravljeni POGRESNI kljucevi (`general_*` -> `site_*`, bili su mrtvi -> schema.org prazan).
      - `SiteHeader` (2x) + `SiteFooter` (naziv 2x + kontakt telefon/email/adresa) -> `getValue('site_*')`.
      - Rezultat: jedan build sluzi bilo kog klijenta preko `apiBase`; samo boja/logo ostaju
        build-time (tailwind `primary` + asset). typecheck zelen.
      - [ ] FOLLOW-UP: page `useHead` title-ovi (~10 stranica) jos hardkoduju "eLokal" —
        traze app-level titleTemplate hranjen iz settings (zaseban, mehanicki).
- [ ] **Opcija 2 — Nuxt layer (ako treba custom strane/komponente):** klijent repo
      `extends` core storefront, override-uje samo svoje. Bez merge konflikta.
- [ ] **Opcija 3 — fork (samo ako klijent radikalno odstupa):** pun kopiran kod.
- [ ] Admin: jedan deljen `elokal-admin` repo, deploy po klijentu, `apiBase` -> klijentski API

Preporuka: krenuti **Opcija 1**, preci na 2 tek kad prvi klijent zatrazi custom kod.

---

## Faza 4 — Pilot: 1 klijent end-to-end

> Pilot = **prvi prolaz CORE-003 runbook-a** za sloj/klijent #1. Ovde se baza dokazuje
> kroz stvarnu klijentsku instancu; detaljan per-klijent runbook je u CORE-003.

- [ ] Jedan test/stvarni klijent (= sloj, klijent #1): API deploy + Postgres baza +
      storefront + admin, sve povezano (po CORE-003)
- [ ] **Test pinninga:** tag `v1.1.0` na API (neka izmena), redeploy SAMO tog klijenta;
      potvrditi da bi drugi klijent ostao na `v1.0.0` (kontrola "biram" dokazana)
- [ ] **Test update bez konflikta:** isti fix stize svim klijentima preko `git pull` +
      redeploy, bez ijednog merge konflikta (suprotnost forku)
- [ ] Dokumentovati runbook u `.mdjdocs/notes/`

---

## Acceptance criteria

- [ ] API radi na Postgres-u: migracije + seederi + E2E zeleni, nijedan `DATE()`/sqlite-izam ne puca
- [ ] Isti kod + dva `.env`/baze daju dva nezavisna shopa bez izmene koda
- [ ] Klijent-specificne vrednosti su u `settings`/`.env`, ne hardkodovane (osim svesno ostavljenih)
- [ ] Deploy po klijentu pinovan na tag; update jednog klijenta ne dira druge
- [ ] Migrate na svim klijentima ide jednom komandom/skriptom, ne 5x rucno
- [ ] Storefront strategija odabrana i sprovedena za pilot klijenta
- [ ] Runbook za "update klijenta" i "novi klijent" napisan

---

## Plan (redosled)

1. **Faza 0** — odluke (org, pin, storefront strategija)
2. **Faza 1** — API na Postgres + externalizacija (jezgro, ~1-1.5 dan)
3. **Faza 2** — deploy/pin/migrate automatizacija (~0.5-1 dan)
4. **Faza 3** — storefront per klijent (zavisi od opcije)
5. **Faza 4** — pilot 1 klijent + dokaz pinninga (~0.5 dan)

---

## Progress

_Ovde dodavati stavke sa datumom i vremenom, ne brisati stare._

- 2026-06-03 — task kreiran. Model: Headless Flavor A (deljen API/admin + deploy/baza
  po klijentu). Audit pokazao glavni posao = SQLite->Postgres (`DATE()` fix u
  ReportController) + externalizacija RSD/loyalty literala. CORE-001 (fork) parkiran.
- 2026-06-03 — **Faza 1a ZAVRSENA**. Postgres@17 (port 5433) + baza `elokal`. Migracije
  (72) i seederi (6) zeleni na pgsql. Otkrivene i ispravljene 3 klase SQLite->pg mina:
  (1) migration ordering — 7 forward-FK preimenovanja; (2) `substr`->`mb_substr` na srpskom
  tekstu (UTF-8 0xc5); (3) NOT NULL `download_logs.order_id` guard u seederu. `DATE()`
  verifikovan kao NE-problem na pg. Regresija nula (244/246 phpunit, 2 pada pre-postojeca).
- 2026-06-03 — **Faza 1b ZAVRSENA**. Currency izvor istine = `Currency` model (ne setting).
  Helper `currency_symbol()` (kesiran) + invalidacija u CurrencyController. 5 RSD literala
  zamenjeno; loyalty rate brojevi flagovani kao follow-up. `.env.example` -> pgsql. Per-klijent
  valuta dokazana (EUR->€). Regresija nula (244/246, ista 2 pre-postojeca pada).
  Preostalo u Fazi 1: 1c (dve baze + dva .env = dva shopa) — opciono za sad.
- 2026-06-03 — **phpunit prebacen na Postgres** (baza `elokal_test`) + CI dobio pg servis.
  Prelazak otkrio i ispravio **29 case-sensitive `like` pretraga** (-> `ilike`) — latentni bug u
  SVIM search box-ovima koji bi se video tek na produkciji. 4. klasa SQLite->pg mine. 244/246.
  CI ce biti crven dok se ne srede 2 pre-postojeca PublicCategoryTest pada (zaseban bug).
- 2026-06-03 — **Faza 3 (storefront) zapoceta — Opcija 1 (config-driven).** Brending sada iz
  API `settings` (po-klijentu), ne hardkodovan. Sredjen `default.vue` (SSR settings load +
  ispravljeni mrtvi kljucevi `general_*`->`site_*`), `SiteHeader`/`SiteFooter` (naziv + kontakt).
  Jedan storefront build sluzi bilo kog klijenta preko `apiBase`; samo boja/logo build-time.
  typecheck zelen. Follow-up: page title-ovi (~10) jos hardkoduju "eLokal".
- 2026-06-03 — **Hosting izabran: Vercel + Railway + Neon** (managed, ne Hetzner). Faza 2
  repo-priprema ZAVRSENA: `api/railway.json` (migrate na deploy), `.env.example` Neon sekcija,
  **admin API_BASE externalizovan** (bio hardkodovan -> `runtimeConfig`/`NUXT_PUBLIC_API_BASE`;
  4. deploy-blocker uhvacen). Laravel je vec Neon-ready (`DB_URL`+`DB_SSLMODE`). admin+storefront
  typecheck zelen. Pinning model = grana po klijentu (`deploy/<klijent>` -> tag). Ostaje
  provizioniranje naloga (Neon/Railway/Vercel projekti) — tvoji nalozi, runbook u Fazi 2.
