# Arhitektura: elokal (baza) + klijentski sajtovi

> Odluka o modelu distribucije proizvoda na vise klijentskih sajtova.
> Datum odluke: 2026-06-03. Profil: ~5 klijenata, "tema + poneka feature razlika",
> version pinning po klijentu pozeljan.

## Razmatrane opcije (i zasto odbacene)

| Model | Zasto NE za nas profil |
|---|---|
| **Fork** (svaki klijent svoj pun kod) | 5x rucni prenos svakog API fix-a + merge konflikti. Skalira lose. |
| **Multi-tenant B1** (single baza, `tenant_id` svuda) | ~2-3 nedelje refaktora 61 tabele, rizik "tenant leak". Overkill na 5. |
| **Multi-tenant B2** (stancl, jedna instanca, baza-po-tenantu) | Trajni "tenancy porez" na svaki feature + gubi version pinning + blast radius. Isplati se tek na 10-15+. |
| **Headless A** ⭐ | Izabran — vidi dole. |

## Izabrani model: Headless Flavor A — deljen kod, deploy po klijentu

API i admin su **deljeni repoi** koji se **deployuju po klijentu**, svaki sa svojom
bazom + svojim `.env` + pinovan na svoj git tag. Storefront je per-klijent (tamo
zive vizuelne razlike).

```
elokal-api    (1 repo, deljen)  -> deploy po klijentu: svoja Postgres baza + .env + pin
elokal-admin  (1 repo, deljen)  -> deploy po klijentu: cilja na klijentski API + pin
klijent-X-storefront            -> per klijent (brend, teme, custom strane)
```

### Zasto A pobedjuje na 5 klijenata
- **Deljen kod** za API/admin -> API fix se NE prenosi 5x rucno (za razliku od forka).
  Update = `git pull` + redeploy izabranih klijenata, bez merge konflikta.
- **Zadrzan version pinning** -> svaki klijent deploy pinovan na svoj tag; biram sta
  i kad updateujem, po klijentu.
- **Izolacija** -> svoja baza po klijentu = nema "tenant leak" rizika, `settings`
  tabela radi kao-jeste (svaka baza svoja).
- **Nula multi-tenancy refaktora** -> svaka baza = jedna prodavnica = tacno ono sto
  kod vec pretpostavlja. Ne dodaje se `tenant_id` nigde.

### Cena (prihvacena)
- Operativa raste linearno (5 deploya, 5 baza, 5 migracija pri update-u) -> resava se
  **deploy/migrate automatizacijom** (CI/skripta). Podnosljivo na 5; na 10-15+
  ponovo razmotriti B2.
- Storefront customizacija po klijentu (fork ili Nuxt layer) -> vidi odluku u CORE-002.

## Granica deljeno <-> klijent-specificno

| Sloj | Status | Klijent-specificno kroz |
|---|---|---|
| **API logika** | deljen repo | `.env` + `settings` tabela (po bazi) |
| **Admin** | deljen repo | `.env` (apiBase -> klijentski API) |
| **Storefront izgled** | per klijent | tema, logo, custom strane/komponente |
| **Feature on/off** | deljen kod | `settings` tabela (`feature_*`) po bazi |
| **Podaci** | izolovani | zasebna Postgres baza po klijentu |

## Sta tacno treba srediti u API-ju (nalazi audita 2026-06-03)

1. **SQLite -> Postgres** (preduslov za zasebne baze):
   - `ReportController` koristi `DB::raw('DATE(created_at)')` — radi na SQLite, **puca
     na Postgres** (Postgres nema `DATE(x)` funkciju; treba `created_at::date` ili
     `DATE_TRUNC`). Audit svih `DB::raw` na pg-kompatibilnost.
   - Pokrenuti svih 72 migracije + seedere na pgsql, srediti sqlite-izme.
2. **Hardkodovane klijent-specificne vrednosti** (kandidati za `settings`/`.env`):
   - `RSD` literal razbacan (`Product.php`, `CouponController`, `OrderController`,
     `LoyaltyController`).
   - Loyalty stope hardkodovane (`LoyaltyController`: `1 poen = 100 RSD`).
   - Za 5 RS klijenata sa istom valutom moze i da ostane; izloziti ako se klijenti
     razlikuju po valuti/stopama.
3. **Vec OK:** `MAIL_FROM_*` je `.env`-driven; `Setting`/`Currency` upiti su per-baza
   (nema single-store mine za Flavor A).

## Infrastruktura (hosting) — IZABRANO: Vercel + Railway + Neon (managed, nula-ops)

Bez servera. Po klijentu zaseban projekat na svakoj platformi, pinovan na git granu.

| Sloj | Platforma | Po klijentu |
|------|-----------|-------------|
| **storefront** (Nuxt SSR) | **Vercel** (Nitro vercel preset, auto) | projekat + `NUXT_PUBLIC_API_BASE` -> klijentski API + build-time boja/logo |
| **admin** (Nuxt SPA) | **Vercel** (static) | projekat + `NUXT_PUBLIC_API_BASE` -> klijentski API |
| **api** (Laravel) | **Railway** (Nixpacks, root=`api`) | servis + `DB_URL` (Neon) + `APP_KEY` + env; `preDeployCommand` migrira |
| **baza** | **Neon** (serverless Postgres) | projekat/baza po klijentu; DIRECT endpoint (Railway je persistentan) |

**Version pinning na managed PaaS = grana po klijentu.** Vercel/Railway deployuju iz
grane. Po klijentu deploy grana (npr. `deploy/acme`) koja pokazuje na izabrani tag.
Update klijenta = fast-forward te grane na novi tag -> Vercel+Railway auto-deploy SAMO
tog klijenta; ostali ostaju na svom pinu. (Nema divergencije — grana je samo pokazivac.)

Repo je vec spreman: Laravel `pgsql` ima `DB_URL`+`DB_SSLMODE` (Neon), Nuxt (storefront i
admin) citaju `NUXT_PUBLIC_API_BASE`. Dodato: `api/railway.json` (migrate na deploy).

Runtime: api = PHP 8.2 + Postgres; storefront = Node SSR (`ssr: true`);
admin = static SPA (`ssr: false`).

> Hetzner+Coolify (self-hosted) je bila ranija opcija — odbacena u korist managed stacka.

## Aktivni plan

Vidi `.mdjdocs/tasks/CORE-002.md` (Flavor A). Stari fork plan CORE-001 je parkiran.
