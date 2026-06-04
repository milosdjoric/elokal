# Dev workflow — izmene i deploy (multi-client)

> Kako se radi svakodnevni development na elokal monorepo-u: gde se dira kod,
> kako se komituje, kako ide deploy. Verifikovano uživo 2026-06-04.

## Arhitektura ukratko

**Jedan git repo** (`milosdjoric/elokal`) drži sve:

```
elokal/                      ← jedan repo, jedan git, jedan push
├── storefront/              ← BASE (deljeni kod — Nuxt layer koji se nasleđuje)
├── clients/
│   ├── sloj/storefront/     ← klijent 1: pun redizajn (override 74 fajla)
│   └── demo/storefront/     ← klijent 2: prazan layer (0 override, ogledalo base-a)
├── api/                     ← deljeni Laravel backend (Railway)
└── admin/                   ← deljeni admin panel
```

Svaki klijent je Nuxt **layer** koji radi `extends: ['../../../storefront']`.
Pravilo nasleđivanja:

- Klijent **ima** fajl na nekoj putanji → **njegova** verzija se koristi (override).
- Klijent **nema** taj fajl → vuče se iz **base-a** automatski.

## 🔑 Zlatno pravilo

**Gde staviš fajl = gde se vidi = šta se deploy-uje.**

| Fajl živi u… | Vidi se na… |
|--------------|-------------|
| `storefront/` (base) | svim klijentima (koji ga ne override-uju) |
| `clients/sloj/` | samo sloj |
| `clients/demo/` | samo demo |

Ne biraš ručno koga deploy-uješ — biraš **gde diraš fajl**, ostalo je automatski.

---

## 3 scenarija

### 1. Override + deploy SAMO na sloj
Sloj hoće drugačiju komponentu od base-a (npr. svoje dugme).

```bash
# kopiraj base fajl u sloj na ISTU putanju, pa izmeni kopiju
cp storefront/components/ui_atoms/Button.vue \
   clients/sloj/storefront/components/ui_atoms/Button.vue
#   …izmeni clients/sloj/storefront/components/ui_atoms/Button.vue…

git add clients/sloj/
git commit -m "feat(sloj): custom button"
git push
```
→ Deploy ide na **sloj**. Demo i dalje vuče base Button — nedirnut.

### 2. Override + deploy SAMO na demo
Isto kao gore, samo putanja `clients/demo/storefront/…`:

```bash
#   …izmeni/dodaj fajl u clients/demo/storefront/…
git add clients/demo/
git commit -m "feat(demo): …"
git push
```
→ Deploy ide na **demo**. Sloj nedirnut.

### 3. Fix/izmena na OBA (i sve buduće klijente)
Popravka ili izmena u **base-u**.

```bash
#   …izmeni storefront/… (base)…
git add storefront/
git commit -m "fix(storefront): …"
git push
```
→ Ide na **sve klijente** (koji taj fajl ne override-uju).

---

## Deploy model

- **Git integracija je uključena.** Oba Vercel projekta (sloj, demo) prate
  `milosdjoric/elokal`, production branch `main`.
- **`git push` → auto-deploy.** Ne pokrećeš ništa ručno.
- **Trenutno: svaki push rebuilduje SVE klijente** (sigurno, ali demo se
  rebuilduje i kad menjaš samo sloj). To je samo višak CI vremena — rezultat
  je uvek ispravan.
  - *Buduća optimizacija:* "rebuild samo pogođenog klijenta" (ignore-build-step
    koji gleda promene u `clients/<ime>/` + `storefront/`). Prvi pokušaj je
    pao jer Vercel klonira shallow i `.vercelignore` je brisao `.git`; treba
    robustnija varijanta. Dok se ne uradi — svaki push gradi sve.

## ⚠️ Granica koju treba znati

Fix u base-u **ne stiže** na klijenta koji je baš taj fajl override-ovao
(ima svoju kopiju, ona pobeđuje). Primer: popraviš bug u base `ProductCard.vue`
→ demo ga dobije (nasleđuje), **sloj ne** (ima svoj `ProductCard`).

**Zato:** logiku drži u `composables/` / `stores/` (klijenti to retko
override-uju → fix ide svuda), a komponente neka budu tanak prikaz. Bug je
skoro uvek u logici → fix propagira čak i klijentima sa punim redizajnom.

Auto se dele i: **admin** (zaseban, jedan za sve) i **api** (deljeni backend).

---

## Infrastruktura (referenca)

| Deo | Vrednost |
|-----|----------|
| Git repo | `github.com/milosdjoric/elokal` (branch `main`) |
| sloj sajt | https://sloj-storefront.vercel.app |
| demo sajt | https://demo-storefront-hazel.vercel.app |
| Vercel projekti | `sloj-storefront`, `demo-storefront` (team `milos-projects-dadf5bb3`) |
| Vercel rootDirectory | `clients/sloj/storefront` / `clients/demo/storefront` |
| installCommand (oba) | `npm install && (cd ../../../storefront && npm install && npx nuxt prepare)` |
| api | Railway: `https://elokal-api-production.up.railway.app` |
| baza | Neon `elokal-sloj-eu` (Frankfurt, eu-central-1) — deljena sloj+demo |
| CORS | Railway env `CORS_ALLOWED_ORIGINS` mora sadržati svaki Vercel domen |

**Novi klijent — checklist:** napravi `clients/<ime>/storefront/` (kopiraj demo
kao šablon: `nuxt.config.ts` sa `extends` + `~/types`/`~/locales` aliasi,
`tailwind.config.ts` sa content-putanjama ka base, `package.json`), Vercel
projekat (rootDirectory + installCommand + git link), dodaj domen u Railway CORS.

## Verifikovano (2026-06-04)

Promenjen je jedan **base** fajl (`CookieConsent.vue` tekst), `git push` →
oba sajta su se sama rebuildovala → isti tekst se pojavio na **demo i sloj**,
a svaki je zadržao svoj font (demo Quicksand, sloj Hanken). Propagacija base→svi
+ izolacija dizajna po klijentu, iz jednog push-a.
