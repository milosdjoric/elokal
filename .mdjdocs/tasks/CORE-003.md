# [CORE-003] Onboarding klijentskog sajta (ponovljiv proces, po klijentu)

**Tip:** infra / runbook
**Status:** 📋 todo
**Referenca:** `.mdjdocs/notes/arhitektura-core-sloj.md`
**Preduslov:** CORE-002 (elokal baza spremna za per-client deploy)

## Cilj

Jedan **ponovljiv proces** za podizanje jednog klijentskog sajta na Headless Flavor A.
Mentalni model: **nema posebnog "sloja" — postoji baza + N klijentskih instanci, a
"sloj" je samo klijent #1.** Isti runbook se reciklira za svaki sledeci klijent.

Svaka instanca = (deljen API kod, pinovan + svoja baza) + (deljen admin, pinovan) +
(storefront po klijentu) + (`.env` + `settings` za brend/feature).

---

## Sta cini jednu klijentsku instancu

```
KLIJENT (npr. "acme"):
  api      -> deljen elokal-api kod, pinovan na tag vX.Y.Z
              + svoja Postgres baza (acme_db)
              + svoj .env (APP_NAME, APP_URL, DB_*, MAIL_FROM_*, kljucevi placanja)
  admin    -> deljen elokal-admin kod, pinovan; apiBase -> acme API
  storefront -> per-klijent (brend); apiBase -> acme API
  settings (u acme bazi) -> feature flagovi + brend config (boja, naziv, valuta...)
  domen + SSL
```

---

## Runbook: podizanje novog klijenta od nule

### 1. Provisioning
- [ ] Kreirati Postgres bazu za klijenta (`<klijent>_db`)
- [ ] Iskopirati `.env.example` -> `.env`, popuniti klijent-specificno
      (APP_NAME, APP_URL, DB_*, MAIL_FROM_*, payment keys)
- [ ] Odabrati verziju baze: pin na `vX.Y.Z` (tag elokal-api/admin)

### 2. API deploy
- [ ] Deploy `elokal-api` pinovan na tag, vezan na klijentsku bazu + `.env`
- [ ] `php artisan migrate --force`
- [ ] Seed **osnovnih** podataka (NE demo seeder) — pocetne kategorije/valuta/settings
- [ ] Smoke: `/api/health` ili osnovni endpoint vraca 200

### 3. Admin deploy
- [ ] Deploy `elokal-admin` pinovan na isti tag
- [ ] `apiBase` -> klijentski API URL
- [ ] Login super_admin radi protiv klijentske baze

### 4. Storefront (po izabranoj opciji iz CORE-002 Faza 3)
- [ ] Kreirati/deploy storefront za klijenta (config-driven / layer / fork)
- [ ] `apiBase` -> klijentski API
- [ ] Brending: logo, `primary` boja (`tailwind.config.ts`), `locales`
- [ ] SSR build prolazi, pocetna se renderuje

### 5. Konfiguracija u `settings` (klijentska baza)
- [ ] Feature flagovi za tog klijenta (`feature_*` on/off)
- [ ] Brend/biznis config (naziv prodavnice, valuta, kontakt, loyalty stope ako su externalizovane)

### 6. Domen + SSL
- [ ] Povezati domen(e): storefront, admin, api
- [ ] SSL (Let's Encrypt preko Coolify/Dokploy ili reverse proxy)

### 7. Smoke test (E2E na klijentu)
- [ ] Kupac flow: pregled -> korpa -> checkout -> narudzbina
- [ ] Admin flow: login -> vidi narudzbinu
- [ ] Feature flag koji je ugasen zaista skriven na frontu i blokiran na API-ju

---

## Runbook: update postojeceg klijenta

- [ ] Odabrati novi tag baze (`vX.Y+1.0`)
- [ ] Bump pin za tog klijenta (api + admin), redeploy
- [ ] `php artisan migrate --force` (preko migrate skripte iz CORE-002 Faza 2)
- [ ] Storefront: bump verziju ako per-klijent vuce iz core-a (layer/fork)
- [ ] Smoke test
- [ ] **Drugi klijenti ostaju netaknuti** (svaki na svom pinu) — to je poenta

---

## Sloj = klijent #1 (pilot)

- [ ] Prvi prolaz kroz runbook gore radi se za **sloj (klijent #1)** — ujedno je to
      pilot iz CORE-002 Faza 4
- [ ] Cilj pilota: dokazati da (a) instanca radi end-to-end, (b) update jednog klijenta
      ne dira druge (pinning), (c) isti fix stize svima bez merge konflikta (deljen kod)
- [ ] Posle pilota: ovaj runbook je proveren i reciklira se za klijente #2-#5

---

## Acceptance criteria

- [ ] Postoji proveren, ponovljiv runbook "novi klijent od nule" (koraci 1-7)
- [ ] Postoji runbook "update klijenta" koji ne dira ostale klijente
- [ ] Sloj (klijent #1) podignut end-to-end kroz taj runbook
- [ ] Demonstrirano: klijent #1 na `vX.Y+1`, ostali na `vX.Y`, oba rade (pinning)
- [ ] Vreme za podizanje klijenta #2 je znacajno krace (runbook se isplatio)

---

## Plan (redosled)

1. Cekati CORE-002 (baza spremna za per-client deploy)
2. Prvi prolaz runbook-a = sloj/klijent #1 (pilot)
3. Doterati runbook na osnovu iskustva iz pilota
4. Reciklirati za klijente #2-#5

---

## Progress

_Ovde dodavati stavke sa datumom i vremenom, ne brisati stare._

- 2026-06-03 — task kreiran. Reframe: nema posebnog "sloja" — postoji baza + N klijenata,
  sloj = klijent #1. Generican ponovljiv runbook za svih 5. Zavisi od CORE-002.
