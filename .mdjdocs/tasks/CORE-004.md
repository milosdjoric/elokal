# [CORE-004] Sloj redizajn: fork -> Nuxt Layer (napustanje fork modela)

**Tip:** refactor / arhitektura
**Status:** 📋 todo
**Referenca:** `.mdjdocs/notes/arhitektura-core-sloj.md`, CORE-002

## Kontekst / odluka

Otkriveno: `/MdjWeb/sloj` je **pun fork** elokal-a (`elokal-source` remote -> lokalni
`/MdjWeb/elokal`) sa **5 storefront redizajn commit-a** (87 fajlova, +3027/-1536):
visual identity overhaul, Lucide ikone, mega menu, editorial homepage, EmptyState v2,
404 redesign, brand assets/tokens.

**Odluka korisnika:** napustiti fork model, ALI zadrzati mogucnost dodavanja
**redizajn koda po klijentu**. Resenje = **Nuxt Layers**:
- elokal storefront = **base layer**
- svaki klijent (sloj) = **tanak layer** koji `extends` elokal i sadrzi SAMO svoje
  override-e (redizajn). Nema fork, nema divergencije, elokal update-i stizu automatski.

Granica: api + admin ostaju Flavor A (deljen elokal kod + per-klijent baza/env).
Storefront prelazi na layer model.

## Sta je redizajn dirao (scope)

Storefront (~80 fajlova): `components/product` (12), `pages/nalog` (12),
`components/ui_molecules` (10), `components/layout` (8), `pages/blog` (4),
`components/ui_atoms` (4), `components/cart` (4), `assets/css`, `tailwind.config.ts`,
`utils/price.ts`, `types`, `stores/cart.ts`, public assets (og/favicon/manifest).
Nove deps: `@nuxt/icon`, `@iconify-json/lucide`.

api/ (van layera — zaseban tretman): `OpendeskScrape`, `OpendeskRemoveBg`,
`SetCategoryThumbnails` komande + izmenjeni `CategorySeeder`/`ProductSeeder`.

## Plan

### Faza 1 — Storefront layer
- [ ] Kreirati tanak sloj storefront layer (`nuxt.config.ts` sa `extends` elokal storefront)
- [ ] Preneti SAMO izmenjene/nove fajlove iz redizajna (override po istoj putanji)
- [ ] Dodati nove deps (`@nuxt/icon`, `@iconify-json/lucide`, `leaflet`)
- [ ] `extends` referenca na elokal storefront (git ref / lokalni path / npm)
- [ ] Lokalni build + typecheck zeleni

### Faza 2 — api deo redizajna
- [ ] Opendesk komande (`OpendeskScrape`, `OpendeskRemoveBg`, `SetCategoryThumbnails`):
      genericko tooling -> **promovisati u elokal bazu** (cherry-pick u elokal main)
- [ ] Seeder izmene (`CategorySeeder`/`ProductSeeder`) = sloj demo podaci -> sloj seed
      (ne u elokal bazu; pokrece se nad sloj Neon bazom)

### Faza 3 — Deploy
- [ ] Re-point `sloj-storefront` Vercel projekat na layer izvor
- [ ] Redeploy, verifikuj redizajn renderuje uzivo
- [ ] (api/admin vec deployovani iz elokal-a — OK, nema sloj-specific koda)

### Faza 4 — Gasenje forka
- [ ] Posto je redizajn u layeru, `/MdjWeb/sloj` fork repo vise nije izvor istine za deploy
- [ ] Zadrzati ga kao referencu/backup dok layer ne bude potvrdjen, pa arhivirati

## Acceptance criteria
- [ ] sloj storefront layer sadrzi SAMO redizajn override-e (ne celu kopiju)
- [ ] `extends` elokal -> ne-redizajnirane stranice/komponente se naslede automatski
- [ ] Promena u elokal storefront-u stize u sloj bez merge konflikta (test: izmeni nesto
      u elokal-u sto sloj NE override-uje, redeploy, vidi se u sloj-u)
- [ ] sloj redizajn (mega menu, editorial homepage, EmptyState...) renderuje na sloj-storefront
- [ ] Opendesk komande u elokal bazi; sloj seed nad sloj bazom

## Progress

_Ovde dodavati stavke sa datumom i vremenom, ne brisati stare._

- 2026-06-03 — task kreiran. Odluka: storefront prelazi sa fork na Nuxt Layer model.
  Redizajn izmeren (87 fajlova). Trenutni `sloj-storefront` deploy je iz elokal-a (bez
  redizajna) — ceka Fazu 3.

- 2026-06-03 — **Faza 1 (layer) ZAVRŠENA lokalno.** Odluke korisnika: iskoristiti postojeći
  `/MdjWeb/sloj` (očišćen), veza lokalni relativni path `extends ['../../elokal/storefront']`,
  prvo lokalno. Vizija: monorepo (elokal + clients) za deploy (Faza 3).
  - Mera: redizajn dira **78/113** storefront fajlova (69%) — layer NIJE tanak, ali deli
    35 ne-UI fajlova (auth, api klijent, i18n, core composables). Korist fokusirana na
    funkcije/bug-fixeve, ne UI (potvrđeno sa korisnikom).
  - Backup: grana `fork-archive` @ 73b731b u sloj repou.
  - Obrisano 33 byte-identična fajla (nasleđuju se iz elokala kroz extends).
  - Rešene 4 planirane zamke: tailwind `content[]` + elokal putanje; `leaflet` dep;
    obrisan mrtav `elokal/.../ui_atoms/Icon.vue` (kolizija sa @nuxt/icon); `tailwindcss.cssPath`.
  - **2 NOVE zamke (van plana):** (a) `@pinia/nuxt` ne nasleđuje `stores/` iz base layera →
    `pinia.storesDirs` + elokal putanja; (b) dupla `pinia`/`vue` instanca (odvojeni node_modules)
    → `vite.resolve.dedupe`. Bez ovoga: "getActivePinia() ... no active Pinia" 500.
  - Verifikacija: sve stranice 200, redizajn renderuje (Hanken, brand tokeni, 86 lucide ikona,
    mega menu, editorial homepage), nasleđene stranice rade (leaflet /prodavnice OK).

- 2026-06-03 — **Sloj podaci migrirani u Neon** (zaseban od layera, na zahtev korisnika).
  - Problem: sloj proizvodi (25 Opendesk) bili samo u lokalnoj `sloj/api/database.sqlite`;
    Neon (Railway) imao elokal demo (62 dečija). Storefront gađao Railway → video pogrešne.
  - Šeme identične (74 tabele, 0 razlika u kolonama) — sloj e77c3b6 = Neon main.
  - Napisana komanda `sloj/api/app/Console/Commands/CopyDatabaseToNeon.php` (`db:copy-to-neon`):
    topološki FK-sort (Neon ne dozvoljava session_replication_role), bool cast (sqlite 0/1→pg),
    TRUNCATE + insert + sequence reset. **969 redova kopirano**, Neon sad ima 25 sloj proizvoda.
  - Storefront preusmeren na Railway (`NUXT_PUBLIC_API_BASE`), lokalni sloj api ugašen.
    Railway api servira sloj proizvode (Studio Desk, Johann Stool...) + kategorije (Stolovi...).
  - **Backend (api+baza) je sad potpuno web-based.** Storefront-redizajn još NIJE (radi lokalno,
    Vercel deploy je staro elokal stanje — Faza 3).

- 2026-06-03 — **Slike: privremeni placehold.co fallback.** Opendesk media fajlovi izgubljeni
  (0 lokalno, 0 na Railway, nisu u git-u — storage u .gitignore). Dodato:
  `sloj/storefront/utils/placeholder.ts` (`placeholderImage`/`onImageError`, brand boje ink-800)
  + `@error` na ProductCard i ProductGallery (glavni + thumb + lightbox). Kad pravi media bude
  na trajnom storage-u (S3/Cloudinary), fallback prestaje sam. TODO: proširiti na index.vue/cart/quickview.

## ⚠️ REŠENO (uzrok identifikovan) — skeleton = SPOR API, nije frontend bug
Storefront je ISPRAVAN. Puppeteer-core headless (debug4.mjs) dokazao: `vueMounted=true`,
`skeletons=0`, slike se učitavaju za prave proizvode — app montiran i radi. Ranije debug
skripte (kraće čekanje) nisu sačekale spor fetch pa su lažno pokazale "0 proizvoda".

**Pravi uzrok: Railway api je 6–14s po pozivu.** Merenja:
- `/up` (bez DB): 0.24s — Railway sam brz.
- `/v1/settings` (1 prost query): **6.3s** — baseline DB konekcija je ~6s!
- `/v1/products?per_page=12` (relacije): **14s**.
- Direktan psql ka Neon: 1.78s (uklj. konekciju).
Dakle: **region mismatch + konekcija-po-zahtevu.** Neon u `us-west-2`, svaki Railway zahtev
otvara novu SSL konekciju preko kontinenta (~6s handshake+RTT). Products dodatno N+1 (→14s).
`onMounted` zove 3 fetch-a → korisnik gleda skeleton 14s+ i misli da je prazno.

**Fix za sporost — URAĐENO (2026-06-04):**
- Railway je u `europe-west4` (Amsterdam), Neon bio u `us-west-2` (Oregon) → prekoatlantski RTT.
- Kreiran nov Neon `elokal-sloj-eu` u **Frankfurt** (`aws-eu-central-1`, pg17), podaci prebačeni
  `pg_dump (us-west-2) | psql (Frankfurt)` — 25 proizvoda, čisto.
- Railway `DB_URL` → Frankfurt direktni endpoint (preko Railway GraphQL variableUpsert +
  serviceInstanceRedeploy; token: Account token, ne Project; Cloudflare traži browser User-Agent).
- **Rezultat ~10×:** settings 6.3→0.6s, categories 7.4→0.68s, products 14→1.1s, filters 8→0.83s.
- Stare baze obrisane (us-west-2 holy-scene + us-east-2 test). Ostaje samo Frankfurt.
- PREOSTALO pre launcha: Neon `autosuspend` off (prvi poziv posle neaktivnosti ~11s cold-start);
  products blagi N+1 (skalira ~20ms/proizvod, prihvatljivo na Frankfurt latenciji).
- Manji bug i dalje: `resolveImageUrl` hardkodira `localhost:8000/storage/` (placehold @error pokriva).
Debug skripte: `/tmp/sloj-debug/debug*.mjs`.

## ✅ Faza 2 (api čišćenje) — URAĐENO (2026-06-04)
- `db:copy-to-neon` (CopyDatabaseToNeon.php) + `categories:set-thumbnails` (SetCategoryThumbnails.php)
  promovisani iz sloj fork-a u **elokal** bazu (`api/app/Console/Commands/`). Generički onboarding alati.
- Opendesk `scrape`/`removebg` OSTAJU u sloj (sloj-specifičan pipeline).
- Verifikovano: `php artisan list` u elokal-u registruje obe nove komande.

## Commit-ovi (2026-06-04, NISU push-ovani)
- **elokal** (4): feat(api) komande; refactor(storefront) Icon drop; chore(deploy) leaflet+ignores; docs CORE-004.
- **sloj** (3): feat(storefront) placehold; refactor(storefront) layer konverzija; chore(api) migracioni alat.

## ⚠️ KLJUČAN NALAZ za Fazu 3 — sloj i elokal DELE isti GitHub repo
`git remote` oba foldera = `git@github.com:milosdjoric/elokal.git`. Sloj NEMA svoj repo —
to je lokalni klon elokal-a sa redizajn+layer commitima koji NISU push-ovani (origin=elokal/main).
→ Monorepo je prirodan: elokal repo postaje monorepo, sloj layer ide u `clients/sloj/storefront`.
Vercel `sloj-storefront` trenutno: vezan za elokal repo, deploy-uje `elokal/storefront` folder
(.vercel u `elokal/storefront`, projectName "sloj-storefront") — BEZ redizajna. `sloj/storefront` nema .vercel.

## Preostalo (sledeće sesije)
- **Faza 3 — monorepo + deploy (VELIK ZAHVAT, sveža sesija):**
  1. Prebaci sloj layer `/MdjWeb/sloj/storefront` → `elokal/clients/sloj/storefront`.
  2. Prilagodi `extends` putanju (`../../../storefront`) + tailwind content paths + pinia storesDirs.
  3. Vercel sloj-storefront: root → `clients/sloj/storefront`; env `NUXT_PUBLIC_API_BASE`=Railway.
  4. Reši deljeni-repo zbrku (sloj lokalni commiti). Commit clients/sloj u elokal repo.
  5. Redeploy, verifikuj: redizajn renderuje + brz api (Frankfurt) → proizvodi instant.
  6. resolveImageUrl localhost:8000 bug — popraviti da koristi apiBase host.
- **Slike:** re-scrape Opendesk (opendesk:scrape + removebg) + trajni storage, ili zadržati placehold.
- **Pre launch:** Neon `autosuspend` off (cold-start ~11s); products N+1 eager-load (opc.).
- **Faza 4:** arhivirati fork (grana `fork-archive` @ 73b731b ostaje backup).
