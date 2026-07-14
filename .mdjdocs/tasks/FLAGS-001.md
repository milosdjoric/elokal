# [FLAGS-001] Sredjivanje feature flag sistema

**Tip:** refactor
**Branch:** `main` (odluka 2026-07-14: sve se radi na main, bez feature grane)
**Status:** 🔨 in progress — Prioritet 1 gotov (2026-07-14)

## Kontekst

Audit feature flag sistema otkrio je 4 kategorije problema. Od 14 definisanih flagova, nekolicina je potpuno mrtva (definisani ali se ne proveravaju), dva imaju ozbiljan security propust (backend ruta radi cak i kad je flag ugasen), postoje nedoslednosti u kljucevima i rasporedu provera, a infrastruktura nema registry — string literali su razbacani po kodu bez jednog izvora istine.

**Mehanizam:** backend helper `feature($key)` cita iz `Setting` tabele → fallback na `api/config/features.php`. Frontend: `storefront/composables/useFeature.ts` (`isEnabled`) i `admin/composables/useFeatureFlags.ts`. Ukupno 14 flagova.

---

## Prioritet 1 — Security teatar (backend rute nisu zasticene)

> Ovo su jedine dve stavke koje se mogu smatrati security propustom — gasenje flaga u Settings UI nema efekta na API.

- [x] `feature_store_credits`: dodat `feature('feature_store_credits')` gate u `CheckoutController` — rani 422 (`ValidationException` na `store_credits` polju) pre validacije + gate u obradi (belt-and-braces)
- [x] `feature_webhooks`: napravljen `app/Http/Middleware/EnsureFeatureEnabled.php` (parametrizovan: `feature:webhooks` → proverava `feature_webhooks`, vraca 403), alias registrovan u `bootstrap/app.php`, webhook rute u `api.php` umotane u `Route::middleware('feature:webhooks')->group(...)`
- [x] Testovi: `tests/Feature/Storefront/CheckoutFeatureFlagTest.php` (flag off → 422 + nista se ne desava; flag on → krediti primenjeni + transakcija ispravna) i `tests/Feature/Admin/WebhookFeatureFlagTest.php` (flag off → 403 GET+POST; flag on → 200). TDD: oba prvo videna crvena.
- [x] **(otkriveno u auditu 2026-07-14)** Admin Settings UI snimao flagove pod pogresnim kljucevima: grupni prefiks pravio `features_wishlist` (mnozina) umesto kanonskog `feature_wishlist` — Feature Flags tab NIKAD nije radio (pisao kljuceve koje niko ne cita, prikazivao defaultove umesto stanja baze). Fix: grupa `features` u `admin/pages/settings/index.vue` koristi kanonske pune kljuceve bez prefiksa (save + fetch); migracija `2026_07_14_000001` brise orphan `features_*` redove; kontraktni test `FeatureFlagSettingsChainTest` (UI payload → settings endpoint → gate reaguje).

---

## Prioritet 2 — Mrtvi flagovi (definisani, ali se nigde ne proveravaju)

Za svaki od ova tri flaga — odluciti: (a) dodati stvarnu proveru ili (b) obrisati flag i UI toggle.

- [x] `feature_wishlist` — ODLUKA: implementirana provera (template fleksibilnost po klijentu). API: `feature:wishlist` middleware na svih 5 ruta (403). UI base+sloj: WishlistButton sakriven, SiteHeader ikona+mobile link, sloj MobileNav stavka, `nalog/wishlist` stranica → 404 guard. Test: `WishlistFeatureFlagTest` (TDD, viden crven).
- [x] `feature_compare` — ODLUKA: implementirana provera. NALAZ: compare NEMA API povrsinu (cisto klijentski, localStorage) → UI gate je kompletan gate. Base+sloj: CompareButton sakriven, FloatingCompareBar sakriven, `/uporedi` → 404 guard.
- [x] `feature_multi_currency` — ODLUKA: implementirana provera. API: `feature:multi_currency` na admin currencies CRUD (403; test `CurrencyFeatureFlagTest`, TDD). Storefront base+sloj: `useCurrency.fetchCurrencies()` preskace fetch kad je flag off → switcher se sam sakriva (`v-if currencies.length > 1`), konverzija ostaje default valuta. Javna `GET /v1/currencies` svesno ostavljena otvorena (read-only lista).
- [x] Po zavrsetku: svih 11 toggle-ova na Feature Flags tabu sada ima bar jednu proveru (wishlist/compare/multi_currency gore; newsletter, shop_the_look, social_proof, loyalty, gift_cards, abandoned_cart, store_credits, webhooks vec imaju). ALI vidi novu stavku u Prioritetu 3 za loyalty checkout rupu.

---

## Prioritet 3 — Nedoslednosti

- [x] **Abandoned cart — dva razlicita kljuca**: konsolidovano na kanonski `feature_abandoned_cart`. ExitIntentPopup (base+sloj) prebacen na kanonski kljuc, duplikat toggle uklonjen iz admin Cart taba (ostaje samo u Feature Flags), seeder ociscen, migracija `2026_07_14_000002` brise stari `cart_feature_abandoned_cart` red.
- [x] **Gift cards — nepotpuno gejting**: `feature:gift_cards` middleware na sve 3 rute (inline `abort(404)` iz `purchase()` uklonjen — middleware je jedini izvor, sada 403). PLUS otkrivena i zatvorena 4. rupa: checkout je unovcavao `gift_card_code` bez flag provere — dodat rani 422 gate + provera u obradi. Test: `GiftCardFeatureFlagTest` (TDD, flag-off videni crveni sa validnom karticom u bazi).
- [x] **Flagovi bez UI togglea**: `feature_store_locator`, `feature_downloads`, `feature_multi_language` dodati u Feature Flags tab (labele: Lokator prodavnica, Digitalna preuzimanja, Više jezika).
- [x] **(otkriveno 2026-07-14) Loyalty — ista rupa kao store_credits**: dodat gate u `CheckoutController` (rani 422 za `loyalty_points` + provera u obradi), `GET /v1/loyalty/balance` → `feature:loyalty` (403; checkout UI sekcija se sama sakriva jer balance fetch tiho pada). Bonus konzistentnost: `GET /v1/store-credits/balance` → `feature:store_credits`, stranice `nalog/poeni` i `nalog/krediti` → 404 guard (base+sloj). Testovi: `LoyaltyFeatureFlagTest` (3, TDD) + balance test u `CheckoutFeatureFlagTest`.

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

- **2026-07-14 ~13:10** — Prioritet 1 zavrsen (TDD, na `main`):
  - `CheckoutController::store()`: rani 422 gate za `store_credits` kad je `feature_store_credits` off + `feature()` provera u obradi.
  - **Bonus bug fix (otkrio ga pozitivni test):** dedukcija store kredita pisala `description` (kolona ne postoji, zove se `reason`) i nije slala NOT NULL `balance_after` → svaki pravi checkout sa kreditima pucao bi na 500. Zamenjeno postojecom `StoreCreditAccount::debit()` metodom (ispravne kolone + `order_id`).
  - Novi `EnsureFeatureEnabled` middleware (alias `feature`, prefiksuje `feature_`), webhook rute grupisane pod `feature:webhooks` → 403 kad je flag off.
  - Testovi: `CheckoutFeatureFlagTest` (2) + `WebhookFeatureFlagTest` (2). Ceo suite: **250 passed (585 assertions)**.
  - Napomena: radjeno na `main` (dogovor — sve na main), jos nije commitovano.
- **2026-07-14 ~13:40** — Korak 1 nastavka: **admin Settings kljucevi flagova**.
  - Nalaz: `saveSettings()` lepio grupni prefiks → `features_*` (mnozina) umesto kanonskih `feature_*`; fetch skidao isti prefiks → tab nikad nije ni prikazivao ni menjao stvarne flagove. Dva paralelna sveta kljuceva.
  - Fix: `features` grupa izuzeta iz prefiks seme (kanonski kljucevi as-is u save i fetch), reactive mapa + labele prebacene na pune kljuceve. Cleanup migracija za orphan `features_*` redove.
  - Verifikacija: API suite **252 passed (591 assertions)** ukljucujuci novi `FeatureFlagSettingsChainTest`; admin `nuxi typecheck` exit 0.
  - Acceptance kriterijumi #1 i #2 (gasenje u Settings UI zaista blokira API) sada vaze end-to-end.
  - Napomena: admin nema test harness (nema vitest/playwright) — Vue deo pokriven typecheck-om + kontraktnim testom na API strani. Uvodjenje vitest-a u admin = kandidat za zasebnu stavku.
- **2026-07-14 ~14:15** — **Prioritet 2 zavrsen** (mrtvi flagovi → implementirane provere, sve na `main`):
  - Wishlist: API `feature:wishlist` (5 ruta) + UI sakrivanje base+sloj (dugme, header, mobile nav, stranica 404).
  - Compare: nema API povrsine (localStorage) → UI-only gate base+sloj (dugme, floating bar, `/uporedi` 404).
  - Multi-currency: API `feature:multi_currency` na admin CRUD + `useCurrency` preskace fetch (base+sloj).
  - Verifikacija: API suite **256 passed (599 assertions)**; `nuxi typecheck` exit 0 za storefront i sloj.
  - NOVO OTKRICE: `feature_loyalty` rupa na checkout-u (ista klasa kao store_credits) — dodato kao stavka u Prioritet 3.
- **2026-07-14 ~14:45** — **Loyalty rupa zatvorena** (TDD, na `main`):
  - Checkout: rani 422 gate za `loyalty_points` + `feature()` u obradi; `loyalty/balance` i `store-credits/balance` rute pod `feature:` middleware.
  - Storefront base+sloj: `nalog/poeni` i `nalog/krediti` → 404 guard; checkout sekcije (poeni/krediti) se same sakrivaju jer balance fetch tiho pada na 403.
  - Verifikacija: API suite **260 passed (610 assertions)**; typecheck exit 0 (storefront + sloj).
- **2026-07-14 ~15:15** — **Prioritet 3 zavrsen** (na `main`):
  - Abandoned cart: jedan kanonski kljuc (`feature_abandoned_cart`) — popup base+sloj, admin Cart tab bez duplikata, seeder, cleanup migracija.
  - Gift cards: `feature:gift_cards` middleware na 3 rute + NOVA RUPA zatvorena (checkout unovcavao kartice sa ugasenim flagom — 422 gate). TDD.
  - 3 flaga izlozena u Feature Flags tabu (store_locator, downloads, multi_language) — sada svih 14 ima UI toggle.
  - Verifikacija: API suite **263 passed (621 assertions)**; typecheck exit 0 (admin + storefront + sloj).
  - Ostaje samo Prioritet 4 (registry/enum + defaulti + dokumentovanje pravila).
