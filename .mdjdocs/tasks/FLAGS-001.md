# [FLAGS-001] Sredjivanje feature flag sistema

**Tip:** refactor
**Branch:** `refactor/flags-001-feature-flag-cleanup`
**Status:** 📋 todo

## Kontekst

Audit feature flag sistema otkrio je 4 kategorije problema. Od 14 definisanih flagova, nekolicina je potpuno mrtva (definisani ali se ne proveravaju), dva imaju ozbiljan security propust (backend ruta radi cak i kad je flag ugasen), postoje nedoslednosti u kljucevima i rasporedu provera, a infrastruktura nema registry — string literali su razbacani po kodu bez jednog izvora istine.

**Mehanizam:** backend helper `feature($key)` cita iz `Setting` tabele → fallback na `api/config/features.php`. Frontend: `storefront/composables/useFeature.ts` (`isEnabled`) i `admin/composables/useFeatureFlags.ts`. Ukupno 14 flagova.

---

## Prioritet 1 — Security teatar (backend rute nisu zasticene)

> Ovo su jedine dve stavke koje se mogu smatrati security propustom — gasenje flaga u Settings UI nema efekta na API.

- [ ] `feature_store_credits`: dodati `feature('store_credits')` gate u `CheckoutController` — i u validaciju (~linija 46) i u obradu (~linije 149-153). Dok flag ne prodje, ruta treba da vrati 422 ili preskoci store credit odbitak
- [ ] `feature_webhooks`: premestiti zastitu sa sidebar linka (`admin/components/layout/Sidebar.vue` ~linija 90) na same API rute (`api/routes/api.php` ~linije 270-275) — middleware provera ili inline `feature()` gate na grupama ruta; samo skrivanje linka u UI nije dovoljno
- [ ] Napisati test za oba slucaja: poziv rute dok je flag iskljucen treba da vrati greshu, ne da tihо uspe

---

## Prioritet 2 — Mrtvi flagovi (definisani, ali se nigde ne proveravaju)

Za svaki od ova tri flaga — odluciti: (a) dodati stvarnu proveru ili (b) obrisati flag i UI toggle.

- [ ] `feature_wishlist`: utvrditi da li postoji provera u storefront komponentama i WishlistController-u; ako ne — dodati ili obrisati
- [ ] `feature_compare`: proveriti storefront `/uporedi` stranicu i product card ikonu; ako nema provere — dodati ili obrisati
- [ ] `feature_multi_currency`: proveriti da li currency switcher i konverzija cena proveravaju flag; ako ne — dodati ili obrisati
- [ ] Po zavrsetku: u Settings → Feature Flags UI ne sme da ostane ni jedan toggle koji nema efekta kad se ugasi

---

## Prioritet 3 — Nedoslednosti

- [ ] **Abandoned cart — dva razlicita kljuca**: API koristi `feature_abandoned_cart`, a `storefront/ExitIntentPopup.vue` (~linija 16) gleda `cart_feature_abandoned_cart` iz cart grupe settings. Konsolidovati na jedan kljuc; odluciti koji je kanonski (preporuka: `feature_abandoned_cart`)
- [ ] **Gift cards — nepotpuno gejting**: `feature_gift_cards` se proverava samo u `GiftCardController::purchase()`, dok `check()` i `checkByCode()` rade i kad je flag iskljucen. Dodati proveru u sva tri metoda
- [ ] **Flagovi bez UI togglea**: `feature_store_locator`, `feature_downloads`, `feature_multi_language` se mogu menjati samo direktno u config/bazi. Izloziti ih u Settings → Feature Flags tab u admin panelu

---

## Prioritet 4 — Infrastruktura

- [ ] **Registry / enum flagova**: kreirati jedan fajl (npr. `api/app/Enums/FeatureFlag.php` ili `api/config/features.php` prosiriti) koji sadrzi sve kljuceve kao konstante ili enum case-ove; zameniti sve string literale referencama na taj enum/const
- [ ] **Pravilo "UI + API"**: dokumentovati u `.mdjdocs/` (ili inline komentar u features.php) da svaki flag mora da gejtuje i UI i odgovarajucu API rutu — flag bez backend gate-a tretirati kao ne-implementiran
- [ ] **Default vrednosti**: pronaci sva mesta gde se poziva `isEnabled(key, false)` i `isEnabled(key, true)` (ili Laravel `feature($key, true/false)`) i ujednaciti defaultove — isti flag ne sme da ima razlicit fallback zavisno od mesta poziva; defaultove preseliti u registry/config

---

## Touch points

- **Fajlovi:**
  - `api/app/Http/Controllers/Storefront/CheckoutController.php` (~linije 46, 149-153)
  - `api/routes/api.php` (~linije 270-275)
  - `api/config/features.php`
  - `admin/components/layout/Sidebar.vue` (~linija 90)
  - `admin/composables/useFeatureFlags.ts`
  - `storefront/composables/useFeature.ts`
  - `storefront/components/ExitIntentPopup.vue` (~linija 16)
  - `api/app/Http/Controllers/GiftCardController.php`
  - `api/app/Http/Controllers/WishlistController.php`
- **Baza:** `settings` tabela (kljucevi feature flagova)
- **API:** webhook rute, checkout ruta, gift card rute, wishlist rute
- **Admin UI:** Settings → Feature Flags tab

---

## Acceptance criteria

- [ ] Gasenje `feature_store_credits` u Settings UI zaista blokira primenu store kredita na checkout API ruti (test prolazi)
- [ ] Gasenje `feature_webhooks` u Settings UI zaista vraca gresku na webhook API rutama (test prolazi)
- [ ] Svaki od 14 flagova ima barem jednu vidljivu posledicu i na frontendu i na API-ju kad se ugasi
- [ ] Nema "dekorativnih" togglea u Settings UI koji ne menjaju ponasanje sistema
- [ ] Abandoned cart koristi jedan kanonski kljuc na oba mesta
- [ ] Gift card flagovanje je konzistentno u sva tri metoda kontrolera
- [ ] `feature_store_locator`, `feature_downloads`, `feature_multi_language` su vidljivi i editabilni u Settings UI
- [ ] Svi string literali kljuceva zamenjeni referencama na centralni registry/enum
- [ ] `isEnabled` / `feature()` pozivi za isti flag koriste isti default value

---

## Plan

1. **Security fix** (Prioritet 1): CheckoutController + webhook rute + testovi — ne mergirati ostalo dok ovo nije zeleno
2. **Audit mrtvih flagova** (Prioritet 2): za svaki potvrditi status pre pisanja koda; tek onda odluka implementiraj/obrisi
3. **Nedoslednosti** (Prioritet 3): abandoned cart kljuc, gift card metodi, UI toggle za 3 flaga
4. **Registry + defaulti** (Prioritet 4): posle ciscenja, kad se zna konacna lista flagova

---

## Progress

_Ovde dodavati stavke sa datumom i vremenom, ne brisati stare._
