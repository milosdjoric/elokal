# [CORE-001] Razdvajanje elokal <-> sloj: dva odvojena repoa (fork)

**Tip:** infra
**Status:** 🅿️ parkiran — zamenjen modelom Flavor A (vidi CORE-002)
**Referenca:** `.mdjdocs/notes/arhitektura-core-sloj.md`

> NAPOMENA (2026-06-03): Posle analize industrijskih standarda i skaliranja na ~5-10
> klijenata, fork model je napusten u korist **Headless Flavor A** (deljen API/admin
> kod + deploy/baza po klijentu). Razlog: fork trazi 5x rucni prenos svakog API fix-a
> + merge konflikte. Aktivni plan je **CORE-002**. Ovaj fajl ostaje kao zapis razmatranja.

## Cilj

Fizicki odvojiti **elokal** (baza/proizvod) i **sloj** (prvi klijent) u **dva
potpuno nezavisna repoa, svaki sa svojim kompletnim kodom**. Zatim testirati nacin
rada: razvoj na elokal -> rucan, biran prenos izmena u sloj.

Model = fork sa upstream remote-om (NE dependency/layer). Vidi notes za obrazlozenje
i cenu (divergencija + rucni prenos).

---

## Faza 0 — Priprema

- [ ] Privatna GitHub org/namespace (npr. `tvoj-org`)
- [ ] Potvrditi da je trenutni repo (ovaj) = **elokal** (baza). Ostaje kako jeste.
- [ ] Odluciti naziv prvog sloja (npr. `sloj-acme` ili stvarni klijent)

---

## Faza 1 — Napraviti sloj kao odvojen repo (klon)

> Klon, ne novi prazan repo — da dele git istoriju, pa cherry-pick/merge rade kasnije.

- [ ] Klonirati elokal u novi folder pod novim imenom (`sloj-acme`)
- [ ] Na GitHub-u napraviti prazan privatan repo `sloj-acme`
- [ ] U klonu prepokaciti `origin` na novi repo:
  - [ ] `git remote rename origin elokal` (stari elokal ostaje kao upstream)
  - [ ] `git remote add origin git@github.com:tvoj-org/sloj-acme.git`
  - [ ] `git push -u origin main`
- [ ] Provera: `git remote -v` pokazuje `origin` (sloj) + `elokal` (baza)

Rezultat: dva nezavisna repoa, sloj ima svoj kompletan kod i svoj origin, a elokal
mu je dostupan kao remote za buduci prenos.

---

## Faza 2 — Klijent-specificne izmene u sloju (da se vidi razlika)

- [ ] Promeniti brend u sloju: `storefront/tailwind.config.ts` -> druga `primary` boja
- [ ] (opciono) zameniti logo / tekst u footeru
- [ ] (opciono) ugasiti neki feature preko `settings` (seed) u sloju
- [ ] Commit + push na `origin` (sloj) — elokal se NE dira
- [ ] Pokrenuti sloj lokalno, potvrditi da radi nezavisno od elokal-a

---

## Faza 3 — Test prenosa izmene elokal -> sloj (srz testa)

- [ ] U **elokal** repou napraviti vidljivu izmenu (npr. nova komponenta ili fix),
      commit na `main`, push
- [ ] U **sloj** repou povuci tu izmenu biranjem:
  - [ ] `git fetch elokal`
  - [ ] `git cherry-pick <commit>` (biran prenos jedne izmene)  **ili**
  - [ ] `git merge elokal/main` (sve nakupljeno odjednom)
- [ ] Potvrditi: izmena iz elokal-a je u sloju, a klijentski brend override ostao
- [ ] Namerno izazvati konflikt: izmeniti `tailwind.config.ts` i u elokal-u, pa
      probati prenos -> videti kako izgleda merge konflikt i kako se resava
      (ovo je poenta — da znas trosak modela pre nego sto bude vise klijenata)

---

## Acceptance criteria

- [ ] `sloj-acme` je zaseban repo sa svojim `origin`, svojim kompletnim kodom
- [ ] elokal i sloj se grade i pokrecu nezavisno jedan od drugog
- [ ] Klijentski override (brend) postoji samo u sloju, elokal ga ne zna
- [ ] Prenos izmene elokal -> sloj radi i preko `cherry-pick` i preko `merge`
- [ ] Dokumentovano: kako izgleda i kako se resava merge konflikt na deljenom fajlu
- [ ] Donesena odluka: ostajemo na fork modelu ili (kasnije) prelazimo na layer/paket

---

## Plan (redosled)

1. **Faza 0** — org + potvrda da je ovaj repo elokal
2. **Faza 1** — klon -> sloj-acme, remotes (origin=sloj, elokal=upstream)
3. **Faza 2** — brend izmena u sloju, dokaz nezavisnosti
4. **Faza 3** — prenos izmene + namerni konflikt (poenta: testiranje nacina rada)

---

## Progress

_Ovde dodavati stavke sa datumom i vremenom, ne brisati stare._

- 2026-06-03 — task kreiran. Model ispravljen na **fork (dva odvojena repoa, svaki
  svoj kod)** umesto dependency/layer. Prenos preko upstream remote-a
  (cherry-pick/merge). Arhitektura u `.mdjdocs/notes/arhitektura-core-sloj.md`.
