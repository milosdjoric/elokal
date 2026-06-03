# eLokal — Popis feature-a

Detaljan pregled svih implementiranih funkcionalnosti, kategorisanih po oblastima.
Svaka stavka opisuje konkretne mogucnosti koje korisnik ili admin moze da koristi.

---

## Katalog proizvoda (PLP)

- [x] **Filteri u sidebar-u**
  - Hijerarhijski prikaz kategorija (parent → child) sa brojem proizvoda
  - Cena range slider (min/max) sa prikazom aktivnog opsega
  - Dinamicki atributi iz baze: boja (color swatch), slika (image swatch), obicni (checkbox lista)
  - Svaki filter odmah sužava rezultate bez reload-a

- [x] **3 layout-a prikaza**
  - Grid: kartice u 3-4 kolone, slika + ime + cena + sale badge
  - List: horizontalni redovi sa vecim opisom i detaljima
  - Compact: manji kartice, vise proizvoda na ekranu

- [x] **Sortiranje**
  - Najnovije (po datumu kreiranja)
  - Cena rastuce / opadajuce
  - Naziv A-Z / Z-A
  - Najveci popust (procenat sale-a)

- [x] **Per-page selector**
  - Korisnik bira 12, 24 ili 48 proizvoda po stranici
  - Izbor se cuva u URL-u

- [x] **Aktivni filter cipovi**
  - Svaki primenjeni filter se prikazuje kao chip iznad rezultata
  - Klik na X uklanja taj filter
  - "Obrisi sve" link resetuje sve filtere

- [x] **URL sync**
  - Svi filteri, sort, layout i stranica se syncuju u URL query params
  - Link se moze podeliti i otvara isti prikaz
  - Back/forward u browseru radi korektno

- [x] **Paginacija**
  - Numbered pagination sa prev/next
  - Prikaz ukupnog broja rezultata

- [x] **Skeleton loading**
  - Shimmer placeholderi dok se podaci ucitavaju
  - Prati izabrani layout (grid/list/compact)

- [x] **Kategorija slug resolver**
  - URL `/kategorije/tepisi` automatski pronalazi kategoriju po slug-u
  - Redirect na `/proizvodi?category=ID` sa spinnerom

---

## Proizvod detalj (PDP)

- [x] **Galerija slika**
  - Glavna slika + thumbnail strip ispod
  - Klik na thumbnail menja glavnu sliku
  - Zoom on hover (uvecanje detalja)
  - Fullscreen lightbox mod
  - Automatsko prebacivanje na slike variante kad se izabere druga boja/opcija

- [x] **Cena i popust**
  - Prikaz regularne cene i sale cene
  - Sale badge sa procentom popusta (-20%)
  - Precrtana originalna cena kad je popust aktivan
  - Unit price prikaz (npr. "125 RSD/m")

- [x] **Najniza cena u 30 dana (Zakon o trgovini RS, cl. 32a)**
  - Kad je proizvod na akciji, prikazuje se najniza cena iz prethodnih 30 dana
  - `ProductPriceHistory` belezi snapshot cene/sale cene pri svakoj promeni (ProductObserver)
  - `lowest_price_30_days` racuna minimum iz 30 dana PRE pocetka tekuce akcije (ne ukljucuje samu akciju)
  - Izlozeno storefront-u kroz ProductResource
  - `php artisan price-history:backfill` popunjava istoriju za postojece proizvode

- [x] **Social proof**
  - "X ljudi trenutno gleda ovaj proizvod" — real-time brojac iz cache-a
  - "Kupljeno X puta" — ukupan broj prodaja
  - Oba se prikazuju ispod cene za urgency efekat

- [x] **Sale countdown tajmer**
  - Prikaz preostalih dana:sati:minuta:sekundi do kraja akcije
  - Pojavljuje se samo kad proizvod ima sale_price_to datum
  - Automatski nestaje kad akcija istekne

- [x] **Variant selektor**
  - Swatch mode: vizuelni krugovi za boju (color hex), slike za image atribute
  - Table mode: tabelarni prikaz svih varijanti sa cenom, stockom i dugmetom za dodavanje
  - Kontrolise se feature flag-om (swatch/table/both)
  - Nedostupne varijante su oznacene kao crossed-out
  - Izbor variante menja cenu, SKU, stock status i galeriju

- [x] **Stock status**
  - "Na stanju" / "Malo na stanju" / "Nema na stanju" sa odgovarajucom bojom
  - Kad je stock 0, Add to Cart je disabled

- [x] **Add to Cart**
  - Quantity selector (+/-) sa minimalnom kolicinom 1
  - Dugme dodaje u korpu sa feedback-om (drawer/modal/toast — konfigurisano u admin settings)
  - Za proizvode sa varijantama, mora se izabrati varijanta pre dodavanja

- [x] **Notify Me forma**
  - Pojavljuje se umesto Add to Cart kad je proizvod out-of-stock
  - Korisnik unosi email
  - Sistem cuva notifikaciju i salje email kad proizvod ponovo bude dostupan

- [x] **SKU prikaz**
  - Prikazuje SKU kod proizvoda ili izabrane variante
  - Menja se dinamicki kad korisnik bira drugu varijantu

- [x] **Callback request modal**
  - Dugme "Zatrazi poziv" otvara modal
  - Polja: ime, telefon, kanal (poziv/SMS/WhatsApp), opciona poruka
  - Zahtev se salje na API i pojavljuje u admin panelu

- [x] **Trust row**
  - Red ikonica ispod Add to Cart: besplatna dostava, povrat, sigurna kupovina, podrska
  - Tekstovi se konfigurisu iz admin Settings (Trust & Conversion tab)

- [x] **Social share**
  - Dugmad za deljenje: Facebook, X/Twitter, Viber, WhatsApp, kopiraj link
  - Svako dugme otvara odgovarajuci share dialog sa URL-om proizvoda

- [x] **Tabovi sa sadrzajem**
  - Opis: HTML opis proizvoda
  - Recenzije: lista odobrenih recenzija sa rating distribution bar chartom, sort, paginacija, forma za ostavljanje recenzije (samo ulogovani)
  - Vodic za velicine: konfigurabilna tabela (redovi/kolone) iz admin forme
  - Custom tabovi: neogranicen broj dodatnih tabova (naslov + HTML) definisanih u admin formi
  - Dostava/Povrat: staticki tekst iz settings-a
  - FAQ accordion: expand/collapse pitanja i odgovori

- [x] **Related products**
  - Carousel sa povezanim proizvodima
  - Moze biti rucno setovano iz admina ili automatski po kategoriji
  - ProductCard komponenta sa hover efektima

- [x] **Up-sell carousel**
  - Carousel sa skuplim/boljim proizvodima (definisano u admin)
  - Prikazuje se ispod related products sekcije

- [x] **Recently viewed carousel**
  - Poslednji pregledani proizvodi (do 12) iz localStorage-a
  - Trenutni proizvod je iskljucen iz liste

- [x] **Sticky add-to-cart bar**
  - Fiksiran bar na dnu ekrana koji se pojavljuje kad originalni Add to Cart izadje iz viewport-a
  - Sadrzi: ime proizvoda, cenu, quantity i Add to Cart dugme

- [x] **Prev/next navigacija**
  - Strelice za prelazak na prethodni/sledeci proizvod
  - Navigacija unutar iste kategorije ili pretrage

- [x] **Schema.org JSON-LD**
  - Product schema: ime, opis, slika, cena, valuta, dostupnost, SKU, prosecna ocena
  - BreadcrumbList schema: hijerarhija stranica
  - Injektuju se u `<head>` za SEO

---

## Korpa (Cart)

- [x] **Cart drawer**
  - Slide-in panel sa desne strane koji se otvara nakon dodavanja u korpu
  - Lista stavki sa slikom, imenom, cenom i kolicinom
  - Free shipping progress bar: "Jos X RSD do besplatne dostave" sa vizuelnim napretkom
  - Total i dugme za checkout
  - Zatvara se klikom na backdrop ili X dugme

- [x] **Full cart stranica**
  - Standard layout: kartice sa detaljima svake stavke + sidebar sa totals-ima
  - Table layout: spreadsheet prikaz sa kolonama (slika, ime, cena, kolicina, ukupno)
  - Layout se moze konfigurisati iz admin settings-a

- [x] **Quantity selector i remove**
  - +/- dugmad za promenu kolicine (min 1)
  - Trash ikonica za uklanjanje stavke
  - Ukupna cena se azurira u realnom vremenu

- [x] **Cart totals**
  - Subtotal, dostava, ukupno
  - Input za kupon kod sa validacijom i prikazom popusta
  - Input za gift card kod sa prikazom iskoriscenog iznosa
  - Toggle za store credit (koristi raspolozivi balans)
  - Dugme "Nastavi na placanje"

- [x] **Exit intent popup**
  - Pojavljuje se kad korisnik pokusa da napusti stranicu (mouse exit na desktop, tab change na mobile)
  - Prikazuje poruku o korpi i CTA da se vrati
  - Prikuplja email za abandoned cart recovery
  - Kontrolise se feature flag-om `cart_feature_abandoned_cart`

---

## Checkout

- [x] **Multi-step checkout**
  - Shipping: izbor sacuvane adrese ili unos nove, izbor metode dostave
  - Payment: izbor nacina placanja (pouzece, virman, itd.)
  - Kupon: unos i validacija kupon koda sa prikazom popusta
  - Gift card: unos koda, skida se iznos sa kartice
  - Loyalty poeni: toggle za koriscenje bodova (konverzija poeni → RSD)
  - Store credits: toggle za primenu raspolozivog balansa
  - Pregled narudzbine pre potvrde

- [x] **Guest checkout**
  - Mogucnost kupovine bez registracije
  - Kontrolise se feature flag-om `guest_checkout_enabled`
  - Guest unosi email, ime, adresu direktno u checkout formi

- [x] **Shipping zone kalkulacija**
  - Sistem pronalazi odgovarajucu zonu po drzavi i postanskom broju
  - Prikazuje dostupne metode dostave sa cenama i procenjenim vremenom
  - Flat rate, po tezini, po ceni, besplatna — automatski racuna

- [x] **Tax kalkulacija**
  - Automatski primenjuje poresku stopu po drzavi kupca
  - Default stopa ako zemlja nije pronadjena (PDV 20% za RS)
  - Iznos poreza prikazan u pregledu narudzbine

- [x] **Stock rezervacija**
  - Tokom checkout-a, proizvodi se privremeno rezervisu (15 min)
  - Sprecava overselling kod istovremenih kupovina
  - Istekle rezervacije se automatski otpustaju (queue job)

- [x] **Order success stranica**
  - Prikaz broja narudzbine (format: EL-GGMMDD-XXXX)
  - Linkovi: nastavi kupovinu, pregledaj narudzbine
  - Konfirmacioni detalji o narudzbini

---

## Korisnicki nalog

- [x] **Registracija, login, forgot password**
  - Registracija: ime, email, telefon, password + potvrda
  - Login: email + password, link ka registraciji i forgot password
  - Forgot password: unos emaila → reset link na mail → nova lozinka
  - Field-level validacija gresaka (real-time feedback)

- [x] **Profil**
  - Prikaz i izmena: ime, email, telefon
  - Promena lozinke (stara + nova + potvrda)
  - Newsletter toggle (uklj/iskljuci pretplatu, sync sa API-jem)
  - Sve promene se cuvaju preko API poziva sa validacijom

- [x] **Adrese**
  - Lista sacuvanih adresa sa svim detaljima
  - Dodavanje nove adrese kroz modal formu
  - Izmena postojece adrese
  - Brisanje adrese
  - Oznacavanje default adrese (koristi se automatski na checkout-u)
  - Polja: ime, prezime, adresa, grad, postanski broj, drzava, telefon

- [x] **Istorija narudzbina**
  - Paginirana lista svih narudzbina
  - Svaka prikazuje: broj, datum, ukupno, status badge u boji
  - 8 statusa: pending, confirmed, processing, shipped, delivered, completed, cancelled, refunded
  - Klik otvara detalj narudzbine

- [x] **Detalj narudzbine**
  - Vizuelni progress bar (5 koraka): potvrdjena → u obradi → poslata → isporucena → zavrsena
  - Cancelled/refunded baner kad je narudzbina otkazana ili vracena
  - Tracking info: kurir, tracking broj, eksterni link za pracenje
  - Lista stavki sa slikom proizvoda
  - Totals: subtotal, dostava, porez, popust, ukupno
  - Adresa isporuke
  - Beleske kupca

- [x] **Wishlist**
  - Srce ikonica na svakom ProductCard-u za toggle
  - Za goste: cuva se u localStorage
  - Za ulogovane: sync sa serverom (merge lokalno + server)
  - Wishlist stranica: grid prikaz proizvoda sa X dugmetom za uklanjanje
  - Wishlist count badge u headeru

- [x] **Loyalty poeni**
  - Dashboard: raspolozivi bodovi, trenutni tier, ukupno zaradjeno
  - Tier sistem: Bronze (0+), Silver (1000+), Gold (5000+), Platinum (15000+)
  - Progress bar do sledeceg tiera
  - Tabela istorije transakcija: datum, bodovi (+/-), opis, tip
  - Bodovi se mogu iskoristiti pri checkout-u

- [x] **Store krediti**
  - Prikaz raspolozivog balansa u RSD
  - Potpuna istorija transakcija sa before/after balansom
  - Svaki red: datum, iznos, tip (credit/debit), razlog, balans posle
  - Kredit se moze iskoristiti kao popust na checkout-u

- [x] **Digitalna preuzimanja**
  - Lista kupljenih digitalnih fajlova po narudzbini
  - Svaki fajl prikazuje: ime, velicinu, broj preuzimanja / max dozvoljenih, datum isteka
  - Download dugme (token-based URL)
  - Blokira preuzimanje kad je istekao rok ili dostignut limit

- [x] **Login lockout**
  - Nakon vise neuspesnih pokusaja logovanja, nalog se privremeno zakljucava
  - Trajanje lockout-a raste sa svakim neuspehom
  - Zastita od brute force napada (radi i za admin i za korisnike)

- [x] **Email verifikacija**
  - Nakon registracije salje se verifikacioni email
  - Korisnik klikce link za potvrdu
  - Mogucnost ponovnog slanja verifikacionog emaila

- [x] **Brisanje naloga**
  - Korisnik moze trajno obrisati svoj nalog
  - Soft delete (podaci se cuvaju ali nalog je neaktivan)

---

## Pretraga

- [x] **Search results stranica**
  - Prikazuje rezultate pretrage u ProductGrid-u
  - Sort opcije (cena, ime, relevantnost)
  - Per-page selector
  - Paginacija
  - Empty state poruka kad nema rezultata

- [x] **Live search autocomplete**
  - Search bar u headeru sa debounced live pretragom
  - Dok korisnik kuca, padajuci meni prikazuje rezultate iz API-ja
  - Klik na rezultat vodi na PDP, Enter na search results stranicu

- [x] **Recent searches**
  - Poslednji pretrazivani termini (do 5) cuvaju se u localStorage
  - Prikazuju se kad korisnik fokusira search bar bez query-ja
  - "Obrisi istoriju" link

- [x] **Zero-results handling**
  - Poruka "Nema rezultata za X"
  - Prikazuje nedavne pretrage kao alternativu

- [x] **Search logging**
  - Svaka pretraga se loguje u bazu: query, broj rezultata, user ID, IP
  - Koristi se za admin izvestaje (top upiti, zero-result upiti)

---

## Blog

- [x] **Listing**
  - Search input za filtriranje postova
  - 3-kolonski grid sa karticama (featured image, naslov, excerpt, datum)
  - Sidebar: nedavni postovi, lista kategorija, tagovi
  - Paginacija

- [x] **Single post**
  - Header: kategorije, naslov, autor, datum, reading time (racuna se automatski na 200 reci/min)
  - Featured image
  - HTML content (full prose formatting)
  - Tag linkovi ispod sadrzaja
  - Social share dugmad
  - Related posts grid (ostali postovi iz iste kategorije)
  - Full OG meta tagovi za social preview

- [x] **Filtriranje**
  - Po kategoriji: `/blog/kategorija/saveti-za-roditelje`
  - Po tagu: `/blog/tag/diy`
  - Isti layout sa paginacijom

- [x] **SEO**
  - Meta title i description po postu
  - OG:image, OG:type, OG:title za Facebook/Twitter deljenje
  - Canonical URL

---

## Poredjenje proizvoda

- [x] **Comparison tabela**
  - Uporedi do 4 proizvoda uporedo u tabeli
  - Redovi: slika, ime, cena (sa sale), stock status, SKU, kratak opis
  - Dugmad "Ukloni" i "Pogledaj" za svaki proizvod

- [x] **Floating compare bar**
  - Sticky bar na dnu ekrana, pojavljuje se cim se doda 1+ proizvod u compare
  - Prikazuje thumbnailove izabranih proizvoda
  - Link ka comparison stranici

- [x] **Compare dugme na kartici**
  - Ikonica vage na svakom ProductCard-u
  - Toggle dodaj/ukloni iz compare liste
  - Max 4 proizvoda, nakon toga prikaz upozorenja

---

## Poklon kartice

- [x] **Kupovina**
  - Preset iznosi: 1.000, 2.000, 3.000, 5.000, 10.000 RSD
  - Custom iznos: korisnik unosi zeljeni iznos
  - Polja: ime primaoca, email primaoca, opciona poruka
  - Generise se unikatan kod (format: XXXX-XXXX-XXXX)
  - Kartica se prikazuje na success stranici

- [x] **Provera balansa**
  - Stranica `/poklon-kartica/provera`
  - Unos koda → prikaz preostalog balansa u RSD
  - Prikazuje status: aktivna, istekla, potpuno iskoriscena

- [x] **Primena na checkout-u**
  - Input za kod gift kartice u cart totals
  - Validacija koda (aktivan, ima balans, nije istekla)
  - Skida se iznos sa kartice, ostatak se placa drugim metodom
  - Kreirana transakcija sa zapisom

---

## Newsletter

- [x] **Popup**
  - Pojavljuje se nakon konfigurisanog delay-a (npr. 10 sekundi)
  - Ili na exit intent (mouse napusta viewport)
  - Kontrolise se feature flag-ovima `feature_newsletter` + `newsletter_popup_enabled`
  - Suppress za sesiju nakon zatvaranja
  - Ne pojavljuje se ako je korisnik vec pretplacen

- [x] **Inline forma**
  - Newsletter sekcija na homepage-u (Page Builder)
  - Email input + Subscribe dugme
  - Success poruka nakon pretplate

- [x] **Double opt-in**
  - Korisnik unosi email → status "pending"
  - Sistem salje potvrdni email sa tokenom
  - Klik na link u emailu → status "confirmed"

- [x] **Unsubscribe**
  - Link u emailu sa tokenom
  - Klik menja status u "unsubscribed"
  - Korisnik se vise ne pojavljuje u aktivnoj listi

---

## Ostale storefront funkcije

- [x] **Prodavnice (Store locator)**
  - Stranica `/prodavnice`
  - Pretraga po gradu (filter input)
  - Lista prodavnica: ime, adresa, telefon, radno vreme
  - Interaktivna Leaflet mapa sa markerima za svaku lokaciju
  - Klik na marker highlightuje prodavnicu u listi
  - Geo-radius filtriranje (Haversine formula) — prikazuje najblize lokacije

- [x] **Shop the Look**
  - Stranica `/izgled`
  - Editorijalne slike sa interaktivnim hotspot pinovima
  - Klik na pin otvara popover sa imenom proizvoda, cenom i Add to Cart
  - Feature flag gated (`feature_shop_the_look`)

- [x] **Pracenje narudzbine**
  - Stranica `/pracenje/[broj]`
  - Prikaz statusa narudzbine
  - Tracking info: kurir, tracking broj, link ka kurirskom sajtu
  - Zahteva login

- [x] **Kontakt forma**
  - Stranica sa formom: ime, email, predmet, poruka
  - Rate limited na API-ju (zastita od spama)
  - Success poruka posle slanja
  - Poruke idu u admin inbox

- [x] **CMS stranice**
  - Dinamicke stranice: O nama, Kontakt, Uslovi koriscenja, Politika privatnosti, Cesta pitanja
  - URL: `/[slug]` (npr. `/o-nama`)
  - HTML prose sadrzaj
  - Upravljanje kroz admin panel

- [x] **Cookie consent**
  - Baner na dnu stranice pri prvoj poseti
  - Tekst i linkovi se konfigurisu u admin GDPR settings
  - Accept dugme cuva pristanak u localStorage

- [x] **Multi-currency**
  - Dropdown switcher za valutu u headeru
  - Konverzija svih cena na izabranu valutu (exchange rate iz baze)
  - Formatiranje sa simbolom valute
  - Izbor se cuva u localStorage
  - Feature flag `feature_multi_currency`

- [x] **i18n (internacionalizacija)**
  - Language picker dropdown u headeru
  - Podrzani jezici: srpski (sr) i engleski (en)
  - Lokalizovani stringovi iz JSON fajlova
  - Nested key support sa interpolacijom: `t('cart.empty', { count: 0 })`
  - Izbor se cuva u localStorage

- [x] **Social proof popup**
  - Toast notifikacija "Marko iz Beograda je upravo kupio..."
  - Random proizvod i random srpski grad
  - Pojavljuje se na 30-60 sekundi interval, pocinje posle 15s delay-a

- [x] **Back to top**
  - Floating dugme u donjem desnom uglu
  - Pojavljuje se posle odredjenog scroll-a
  - Smooth scroll ka vrhu stranice

- [x] **Mobile nav**
  - Fiksiran bar na dnu ekrana na mobilnim uredjajima
  - Ikonice: Home, Pretraga, Korpa, Nalog
  - Korpa ikonica ima count badge

- [x] **Top bar**
  - Tanak baner iznad headera sa promotivnom porukom
  - Tekst, pozadinska boja i boja teksta se konfigurisu u admin Settings
  - Enable/disable toggle

- [x] **Mega menu**
  - Desktop navigacija: hover na "Kategorije" otvara full-width dropdown
  - Hijerarhijski prikaz kategorija sa podkategorijama
  - Mobile: expandable lista u hamburger meniju

---

## Homepage (Page Builder)

- [x] **14 tipova sekcija**
  - `hero_slideshow`: auto-playing slajdovi sa naslovom, tekstom, CTA dugmetom, pozadinskom bojom + category sidebar
  - `category_grid`: grid kartica kategorija sa slikama
  - `featured_products`: carousel istaknutih proizvoda
  - `new_arrivals`: carousel najnovijih proizvoda
  - `on_sale`: carousel proizvoda na akciji
  - `banner_full`: full-width baner sa slikom/tekstom
  - `banner_split`: dva banera uporedo (levo/desno)
  - `product_carousel`: custom carousel sa proizvodoima po izboru
  - `text_block`: slobodan HTML tekst blok
  - `newsletter`: inline newsletter signup forma
  - `trust_badges`: red ikonica poverenja
  - `blog_preview`: poslednji blog postovi
  - `recently_viewed`: poslednje pregledani proizvodi korisnika
  - `spacer`: vertikalni razmak izmedju sekcija

- [x] **Admin upravljanje**
  - Drag-and-drop reorder sekcija (up/down strelice)
  - Show/hide toggle po sekciji
  - JSON editor za konfiguraciju svake sekcije sa ugradjenim template/hint sistemom
  - Delete sekcija
  - Dodavanje novih sekcija sa dropdown-om za tip

---

---

## Admin — Dashboard

- [x] **Stat kartice**
  - Ukupno proizvoda u bazi
  - Aktivni proizvodi
  - Broj kategorija
  - Istaknuti (featured) proizvodi
  - Out-of-stock proizvodi

- [x] **Low stock widget**
  - Lista proizvoda ciji je stock ispod praga (konfigurisano u Settings)
  - Color-coded: crveno (0), narandzasto (1-5), normalno (6+)
  - Direktan link ka svakom proizvodu

---

## Admin — Proizvodi

- [x] **Lista proizvoda**
  - Pretraga po imenu, SKU-u
  - Filter: aktivan/neaktivan
  - Sortabilne kolone: ime, cena
  - Paginacija (15 po stranici)
  - Thumbnail slika, sale badge, stock level u boji
  - Delete sa confirm dijalogom

- [x] **Forma (6 tabova)**
  - General: ime, slug (auto-generisanje sa srpskim char mapiranjem c→c, s→s, z→z, dj→dj), kratak opis, pun opis, SKU, stock, active/featured toggle, sort order
  - Pricing: regularna cena, sale cena sa datumskim opsegom (od-do), cost price, unit price sa labelom
  - Kategorije: nested checkbox stablo za pridruzivanje kategorijama
  - Size Guide: editabilna grid tabela — dodavanje/brisanje redova i kolona
  - Custom Tabs: neogranicen broj dodatnih tabova (naslov + HTML), svaki se prikazuje na PDP-u
  - SEO: meta title, meta description

- [x] **Image sidebar**
  - Upload vise fajlova odjednom (drag & drop ili file picker)
  - Thumbnail pregled uploadovanih slika
  - Primary slika oznacena okvirom — klik menja primarnu
  - Alt text edit po slici
  - Reorder slika (drag or PATCH)
  - Delete po slici (hover overlay)

- [x] **Variant menadžment**
  - Izbor atributa i vrednosti (checkboxovi)
  - "Generisi varijante" — Cartesian product (npr. 3 boje × 3 velicine = 9 varijanti)
  - Preskace vec postojece kombinacije
  - Inline tabela: SKU, cena, stock, active checkbox, thumbnail slike
  - Bulk setter: postavi cenu ili stock za sve varijante odjednom
  - Duplicate varijanta
  - Delete varijanta
  - Bulk save (PUT)

- [x] **Product relacije**
  - Related products: rucno biranje povezanih proizvoda
  - Cross-sell: proizvodi koji se kupuju zajedno
  - Up-sell: skuplji/bolji alternativni proizvodi
  - Svaki tip ima svoj section u formi

---

## Admin — Narudzbine

- [x] **Lista**
  - Pretraga po broju narudzbine, imenu kupca, emailu
  - Filter po statusu (8 statusa)
  - Paginacija
  - Status badge u boji

- [x] **Detalj narudzbine**
  - Stavke sa slikom, imenom, SKU, cenom, kolicinom, line total
  - Totals breakdown: subtotal, dostava, porez, popust, ukupno
  - Order timeline: hronoloski pregled svih promena statusa sa beleskom i aktorom
  - Status changer: dropdown sa admin note poljem, menja status i dodaje timeline entry

- [x] **Tracking**
  - Dropdown za izbor kurira (iz carriers tabele)
  - Input za tracking broj
  - Auto-generisani tracking URL
  - Kupac vidi tracking info u svom nalogu

- [x] **Refund**
  - Parcijalni: unos iznosa za povrat
  - Potpuni: ceo iznos narudzbine
  - Metod: original payment ili store credit
  - Razlog (text field)
  - Kreira Refund zapis + azurira order

- [x] **Edit modal**
  - Za pending/processing narudzbine moguce je izmeniti detalje
  - Modal sa formom za adresu, beleske, itd.

- [x] **Print dokumenti**
  - Faktura: print-ready stranica sa brojem, datumom, kupcem, stavkama, totalima
  - Packing slip: adresa + lista artikala sa kolicinom i checkbox kolonom (bez cena)
  - Credit note: refund zapisi sa datumom, metodom, razlogom, iznosom
  - Svaki ima Print/PDF dugme i `@media print` CSS

- [x] **Shipment menadžment**
  - Kreiranje posiljke za narudzbinu
  - Tracking broj, kurir, URL, tezina
  - Status pracenje: pending → picked_up → in_transit → delivered → returned
  - Vise posiljki po narudzbini (parcijalna isporuka)

---

## Admin — Kupci

- [x] **Lista**
  - Pretraga po imenu i emailu
  - Kolone: ime, email, broj narudzbina, ukupno potroseno, datum registracije
  - Paginacija

- [x] **Detalj kupca**
  - Levo: istorija svih narudzbina (linked ka detalju narudzbine)
  - Desno sidebar: kontakt info (email, telefon), statistika (broj narudzbina, ukupno potroseno), sacuvane adrese, newsletter status

---

## Admin — Kategorije

- [x] **Hijerarhijski prikaz**
  - Tree prikaz: parent kategorije sa ugnjezdenim child kategorijama
  - Broj proizvoda po kategoriji
  - Active/inactive vizuelno oznaceno

- [x] **Reorder**
  - Up/down strelice za promenu redosleda
  - API poziv na `/admin/categories/reorder`
  - Instant update bez reload-a

- [x] **CRUD modal**
  - Ime i slug (auto-generisan iz imena)
  - Opis (text)
  - Slika: MediaPicker modal za izbor iz media biblioteke
  - Sort order (numericko polje)
  - Active toggle
  - Parent kategorija dropdown (za child kategorije)

---

## Admin — Atributi

- [x] **CRUD za atribute**
  - Ime, slug (auto)
  - Tip: select (obican dropdown), color (sa hex kodom), image (sa slikom)
  - Filterable: da li se prikazuje kao filter na PLP-u
  - Visible on card: da li se swatch prikazuje na ProductCard-u
  - Sort order

- [x] **Vrednosti atributa**
  - Inline dodavanje novih vrednosti
  - Za color tip: color picker sa hex kodom, swatch pregled
  - Za image tip: upload slike
  - Delete vrednosti
  - Reorder vrednosti

---

## Admin — Blog

- [x] **Lista postova**
  - Filter po statusu: draft (radna verzija), published (objavljeno), scheduled (zakazano)
  - Pretraga po naslovu
  - Kolone: naslov, autor, datum, status badge

- [x] **Create/Edit post**
  - Dvokolonski layout
  - Levo (content): naslov, slug (auto sa srpskim char mapiranjem), excerpt, HTML content textarea, SEO polja (meta title, description)
  - Desno (sidebar): status dropdown (draft/published/scheduled), datum objavljivanja (za scheduled), featured image URL, kategorije (checkbox lista), tagovi (pill input)
  - Slug automatski zakljucava za rucno editovanje kad korisnik klikne na slug polje

- [x] **Blog kategorije i tagovi CRUD**
  - Kategorije: ime, slug, opis, sort order
  - Tagovi: ime, slug
  - Upravljanje unutar blog admin sekcije

---

## Admin — Recenzije

- [x] **Filter i pregled**
  - Filter po statusu: pending (ceka moderaciju), approved (odobreno), rejected (odbijeno)
  - Filter po broju zvezdica (1-5)
  - Lista: proizvod, korisnik, rating zvezdice, tekst, datum

- [x] **Moderacija**
  - Approve dugme: recenzija postaje vidljiva na sajtu
  - Reject dugme: recenzija se sakriva
  - Bulk select checkboxovi + bulk approve/reject

- [x] **Admin reply**
  - Modal za pisanje odgovora na recenziju
  - Odgovor se prikazuje ispod recenzije na PDP-u
  - Timestamp kad je admin odgovorio

---

## Admin — Inventar

- [x] **Inventar tabela**
  - Lista svih proizvoda sa SKU i stock kolicinom
  - Color-coded status: crveno (0 — Nema), narandzasto (1-5 — Malo), zeleno (6+ — OK)
  - Badge: Nema / Malo / OK

- [x] **Adjust stock**
  - Individual: promeni stock za pojedinacni proizvod
  - Bulk: selektuj vise proizvoda, unesi novu kolicinu
  - Svaka promena kreira StockMovement zapis (audit trail)

- [x] **Import/Export**
  - Export: CSV fajl sa svim proizvodima i stock kolicinama
  - Import: upload CSV-a sa novim stock kolicinama
  - Bulk azuriranje iz CSV-a

---

## Admin — Media biblioteka

- [x] **Grid prikaz**
  - 6-kolonski grid sa thumbnail-ovima
  - Primary badge na primarnim slikama
  - Hover overlay sa akcijama

- [x] **Filteri**
  - Tip fajla: JPG, PNG, WebP
  - Datum uploada: range filter

- [x] **Bulk operacije**
  - Checkbox selekcija vise slika
  - Bulk delete sa konfirmacijom

- [x] **Per-image akcije**
  - Alt text edit modal
  - Usage info modal: prikazuje koji proizvodi i varijante koriste tu sliku
  - Delete

- [x] **Folder sistem**
  - Kreiranje foldera (Proizvodi, Blog, Baneri, itd.)
  - Hijerarhijski folderi (parent/child)
  - Premestanje slika izmedju foldera
  - Filtriranje prikaza po folderu

---

## Admin — Izvestaji

- [x] **Time range**
  - Selector: poslednjih 7, 30, 90 ili 365 dana
  - Svi tabovi koriste isti period

- [x] **Overview tab**
  - KPI kartice: ukupan prihod, broj narudzbina, prosecna vrednost narudzbine (AOV), novi kupci
  - Tabela: dnevna prodaja (datum, broj narudzbina, prihod)

- [x] **Products tab**
  - Top proizvodi po broju prodatih komada i prihodu
  - CSV export dugme

- [x] **Categories tab**
  - Prihod, prodaja i broj narudzbina po kategoriji
  - CSV export

- [x] **Customers tab**
  - Top kupci po broju narudzbina i ukupnoj potrosnji
  - CSV export

- [x] **Coupons tab**
  - Prihod sa kuponom vs bez kupona
  - Per-coupon statistika: kod, koriscenja, ukupan popust

- [x] **Search tab**
  - Ukupan broj pretraga
  - Top upiti sa prosecnim brojem rezultata
  - Zero-result upiti (sta korisnici traze a nemamo)

---

## Admin — Podesavanja (9 tabova)

- [x] **General**
  - Ime sajta, URL logoa, URL favicona
  - Adresa firme, telefon, email
  - Socijalne mreze: Facebook, Instagram, TikTok URL-ovi

- [x] **Storefront Layout**
  - A/B varijante za: header, PLP, PDP, cart (bira se A ili B)
  - Broj proizvoda po stranici na PLP-u
  - Variant display mode: swatch, table ili both

- [x] **Top Bar**
  - Enable/disable toggle
  - Tekst promotivne poruke
  - Pozadinska boja (color picker)
  - Boja teksta (color picker)

- [x] **Trust & Conversion**
  - Stock status display toggle
  - Urgency bar toggle (prikazuje "Samo X na stanju")
  - Countdown timer toggle (za sale proizvode)
  - Tekst za dostavu, povrat i dispatch
  - Trust badges toggle (ikone na PDP-u)

- [x] **Cart & Checkout**
  - Add-to-cart feedback tip: drawer (slide-in), modal, toast
  - Free shipping threshold (iznos za besplatnu dostavu)
  - Guest checkout toggle
  - Abandoned cart feature toggle

- [x] **Badges**
  - NEW badge: broj dana nakon kreiranja proizvoda za prikaz NEW badge-a
  - NEW badge boja (hex)
  - SALE badge boja (hex)

- [x] **SEO**
  - Meta title sablon sa tokenima: `{name} | {site_name}`
  - Meta description sablon sa tokenima
  - Google Analytics ID
  - Facebook Pixel ID

- [x] **GDPR**
  - Cookie consent tekst (prikazuje se u baneru)
  - Privacy policy URL
  - Terms of service URL

- [x] **Feature Flags**
  - Toggle prekidaci za svaki feature: wishlist, newsletter, compare, social proof, store credits, multi-currency, gift cards, loyalty, webhooks, abandoned cart, shop the look, store locator, downloads, multi-language
  - Iskljucen flag sakriva feature sa storefrontа i admin panela

---

## Admin — Kuponi

- [x] **Lista kupona**
  - Kolone: kod (monospace), tip, vrednost, usage count / max limit, active status
  - Delete akcija

- [x] **CRUD**
  - Kod: rucni unos ili auto-generisanje
  - Tip: percentage (%), fixed_amount (RSD), free_shipping
  - Vrednost popusta
  - Min iznos narudzbine za aktivaciju
  - Max iznos popusta (cap za procentualne)
  - Max upotreba (ukupno), max po korisniku
  - Datum pocetka i isteka
  - Active toggle
  - Opis

- [x] **Bulk generisanje**
  - Generisi N kupon kodova odjednom sa istim pravilima

- [x] **Coupon conditions (rule engine)**
  - Dodavanje uslova za aktivaciju kupona:
    - `min_items`: min broj artikala u korpi
    - `max_items`: max broj artikala
    - `specific_products`: vazi samo za odredjene proizvode
    - `specific_categories`: vazi samo za odredjene kategorije
    - `first_order`: samo za prvu kupovinu korisnika
    - `user_registered_days`: korisnik registrovan bar X dana
  - Operatori: eq, gt, lt, gte, lte, in, not_in
  - Svi uslovi moraju biti ispunjeni za aktivaciju

---

## Admin — Poklon kartice

- [x] **Lista**
  - Kolone: kod (format XXXX-XXXX-XXXX), balans / inicijalni iznos, ime primaoca, active status, datum isteka
  - Aktivne, iskoriscene i istekle kartice

- [x] **CRUD**
  - Kreiranje: inicijalni iznos, primalac (ime, email), poruka, datum isteka
  - Kod se auto-generise
  - Active toggle

- [x] **Balance adjustment**
  - Rucno dodavanje ili oduzimanje balansa
  - Svaka promena kreira GiftCardTransaction zapis

---

## Admin — Store krediti

- [x] **Lista**
  - Kolone: ime kupca, email, trenutni balans (RSD)

- [x] **Manual credit/debit**
  - Rucno dodavanje kredita (npr. refund na store credit)
  - Rucno skidanje kredita
  - Razlog za svaku transakciju (obavezan)
  - Svaka operacija kreira StoreCreditTransaction sa before/after balansom

---

## Admin — Loyalty

- [x] **Lista naloga**
  - Kolone: ime kupca, tier (bronze/silver/gold/platinum sa bojom), bodovi, ukupno zaradjeno

- [x] **Manual adjust**
  - Dodavanje ili oduzimanje bodova sa opisom
  - Tier se automatski preracunava
  - Transakcija se loguje

- [x] **Config**
  - Tier pragovi i pravila za zaradjivanje bodova

---

## Admin — Newsletter

- [x] **Stats bar**
  - 4 kartice: ukupno pretplatnika, confirmed, pending, unsubscribed

- [x] **Lista**
  - Kolone: email, status (badge u boji), izvor pretplate (popup/footer/registration/checkout)
  - Delete akcija po pretplatniku

- [x] **Export**
  - CSV export svih pretplatnika

---

## Admin — Abandoned Carts

- [x] **Stats**
  - Ukupno napustenih, oporavljenih, recovery rate u procentima
  - Ukupna vrednost napustenih korpi

- [x] **Lista**
  - Kolone: email, vrednost korpe, broj poslatih emailova, status (abandoned/recovered/expired)
  - Detalji: stavke u korpi

- [x] **3-step email drip recovery**
  - Step 1 (1h posle): podsetnik o korpi
  - Step 2 (24h posle): drugi podsetnik
  - Step 3 (72h posle): podsetnik sa kupon kodom za popust
  - Automatski se detektuje i salje preko queue job-a
  - Preskace ako je korpa vec oporavljena

---

## Admin — Shipping

- [x] **Shipping zones**
  - Kreiranje zone: ime, lista zemalja (JSON), lista postanskih kodova (JSON)
  - Active toggle
  - Sistem automatski matchuje zonu prema checkout adresi

- [x] **Shipping methods po zoni**
  - CRUD po metodi unutar zone
  - Tipovi: flat (fiksna cena), weight_based (po kg), price_based (po vrednosti korpe), free
  - Polja: ime, cena, free_above prag, per_kg_cost, procenjen broj dana
  - Active toggle

---

## Admin — Tax Rates

- [x] **CRUD**
  - Ime (npr. "PDV 20%")
  - Stopa u procentima (npr. 20.00)
  - ISO kod zemlje (npr. RS, HR, DE)
  - Default flag: koristi se kad zemlja kupca nije pronadjena
  - Active toggle

---

## Admin — Carriers

- [x] **CRUD**
  - Kod (interni identifikator, npr. "dexpress")
  - Ime za prikaz (npr. "D Express")
  - Tracking URL pattern sa placeholder-om `{tracking_number}`
  - Active toggle
  - Sort order

---

## Admin — Payment Methods

- [x] **CRUD**
  - Kod (npr. "cod", "bank_transfer")
  - Ime za prikaz
  - Opis (vidi kupac na checkout-u)
  - Instrukcije (npr. broj racuna za virman — prikazuju se posle narudzbine)
  - Dodatni trosak (npr. 150 RSD za pouzece)
  - Online/offline flag
  - Active toggle, sort order

---

## Admin — Currencies

- [x] **CRUD**
  - ISO kod (RSD, EUR, USD)
  - Ime, simbol
  - Exchange rate prema default valuti
  - Broj decimala
  - Default flag (osnovna valuta sajta)
  - Active toggle

---

## Admin — Store Locations

- [x] **CRUD**
  - Ime lokacije, adresa, grad, postanski broj, drzava
  - Telefon, email
  - GPS koordinate (latitude, longitude) — za prikaz na mapi
  - Radno vreme (JSON format, npr. pon-pet: 09-20, sub: 09-15, ned: zatvoreno)
  - Active toggle, sort order

---

## Admin — Webhooks

- [x] **CRUD**
  - URL endpointa
  - Events: lista dogadjaja za slanje (order.created, order.status_changed, product.updated, itd.)
  - Secret key (auto-generisan, format: whsec_XXXX)
  - Active toggle

- [x] **Test i logovi**
  - Test ping: salje test payload na webhook URL
  - Delivery log: status kod, response body, trajanje (ms), success/fail
  - Failure counter: automatski se broji broj neuspesnih isporuka

---

## Admin — Shop the Look

- [x] **Grid pregled**
  - Thumbnail slike sa hotspot markerima overlaid
  - Active/inactive oznaka

- [x] **Create/Edit**
  - Naslov look-a
  - Slika: upload ili izbor iz MediaPicker-a
  - Interaktivno postavljanje hotspot-ova: klik na sliku postavlja pin na X/Y poziciju (u procentima)
  - Svaki hotspot je povezan sa proizvodom (product search picker)
  - Label za svaki hotspot
  - Active toggle, sort order

---

## Admin — Prevodi (i18n)

- [x] **Model selector**
  - Dropdown za tip modela: Products, Categories, Blog posts, Pages
  - Dropdown za target locale (en, itd.)

- [x] **Lista sa progresom**
  - Svaki item prikazuje completion progress bar
  - Globalni procenat zavrsenosti
  - Klik otvara editor modal

- [x] **Editor modal**
  - Field-by-field prevod (ime, opis, slug, meta title, meta description)
  - Original tekst prikazan pored polja za prevod
  - Save per item

- [x] **CSV export/import**
  - Export svih prevoda u CSV
  - Import CSV-a za bulk azuriranje
  - Lista podrzanih jezika

---

## Admin — Admin korisnici

- [x] **CRUD**
  - Ime, email, password
  - Role: super_admin (sve dozvole), admin (konfigurisane dozvole), editor (ogranicene dozvole)
  - Active toggle

- [x] **Per-module permisije**
  - Checkbox lista modula: products, categories, orders, customers, reviews, blog, media, settings, coupons, reports
  - Super admin automatski ima pristup svemu (bypass permisija)
  - Admin i editor samo pristupaju modulima oznacenim u permisijama

---

## Admin — Activity Log

- [x] **Audit log**
  - Hronoloski prikaz svih admin akcija
  - Kolone: admin ime, akcija (created/updated/deleted/approved/login), model tip + ID, timestamp
  - Read-only (ne moze se brisati ili menjati)
  - Filter po adminu, tipu akcije, tipu modela

---

## Admin — Kontakt poruke

- [x] **Two-panel inbox**
  - Leva strana: lista poruka, nove highlightovane zutom, status badge (new/read/replied)
  - Desna strana: pun tekst izabrane poruke sa svim detaljima
  - Klik na poruku automatski menja status u "read"

- [x] **Akcije**
  - Delete poruka
  - Status se prati (new → read → replied)

---

## Admin — Callback Requests

- [x] **Lista**
  - Kolone: telefon, kanal (poziv/SMS/WhatsApp ikonica), proizvod (link), status badge, datum
  - Admin notes kolona

- [x] **Status workflow**
  - pending: novi zahtev, ceka kontaktiranje
  - contacted: admin je kontaktirao kupca (dugme za prelaz)
  - closed: zahtev je zavrsen (dugme za prelaz)
  - Admin notes polje za belesku uz svaki prelaz

---

## Admin — CMS Pages

- [x] **Lista**
  - Kolone: naslov, slug, active status
  - Delete akcija

---

## Admin — Page Builder

- [x] **14 tipova sekcija**
  - Svaki tip ima svoj JSON template sa hint-ovima za polja
  - Pregled svih sekcija u redosledu prikazivanja

- [x] **Upravljanje**
  - Drag up/down za reorder
  - Show/hide toggle po sekciji (is_active)
  - Delete sekcija
  - Inline JSON editor za konfiguraciju podataka sekcije

---

## Admin — Export/Import

- [x] **CSV export**
  - Proizvodi: sve kolone (ime, cena, stock, SKU, kategorije, itd.)
  - Narudzbine: sve kolone (broj, kupac, status, totali, datum)
  - Kupci: ime, email, telefon, broj narudzbina, ukupno potroseno
  - Streaming download (ne ceka ceo fajl)

- [x] **CSV import proizvoda**
  - Upload CSV fajla
  - Validacija i parsiranje redova
  - Kreiranje/azuriranje proizvoda iz CSV-a
  - Queue job za download slika iz URL-ova u CSV-u
  - Import history: pregled prethodnih importa sa statusom, brojem kreiranih/azuriranih/gresaka

- [x] **Import template**
  - Download praznog CSV sablona sa svim potrebnim kolonama

---

## Infrastruktura / Cross-cutting

- [x] **Queue Jobs**
  - `ProcessProductImage`: resize uploadovane slike u 3 velicine (150×150 thumb, 600×600 medium, 1200×1200 large)
  - `DispatchWebhook`: salje POST na webhook URL sa HMAC potpisom, 3 retry-a sa backoff-om (1/5/15 min)
  - `DetectAbandonedCarts`: skenira napustene korpe, salje podsetnik emailove u 3 koraka
  - `ExpireLoyaltyPoints`: pronalazi istekle bodove i kreira negativnu transakciju
  - `PublishScheduledPosts`: menja status "scheduled" postova u "published" kad dodje vreme
  - `ReleaseExpiredReservations`: otpusta istekle stock rezervacije
  - `ScheduledExport`: generise CSV exportove na zakazan interval
  - `SendBackInStockNotifications`: obavestava korisnike kad se proizvod vrati na stanje
  - `ImportProductImages`: preuzima slike iz URL-ova pri CSV importu

- [x] **Database backup**
  - Artisan komanda: `php artisan db:backup`
  - Tipovi: daily, weekly, monthly, pre-migration
  - Automatska rotacija: 7 daily, 4 weekly, 3 monthly
  - Storage: `storage/app/backups/{type}/`
  - Podrzava SQLite (file copy) i MySQL (mysqldump)

- [x] **Performance indexi**
  - Kompozitni i single-column indexi na svim high-traffic kolonama
  - Pokrivaju: products (slug, is_active, featured), orders (user_id, status), reviews (product_id, status), itd.

- [x] **Health check**
  - GET `/health` endpoint
  - Proverava konekciju ka bazi
  - Vraca 200 (OK) ili 503 (Service Unavailable) JSON

- [x] **Sitemap + robots.txt**
  - XML sitemap sa: svim aktivnim proizvodima, kategorijama, blog postovima, stranicama
  - robots.txt endpoint
  - Automatski generisan iz baze podataka

- [x] **Schema.org**
  - Product: ime, opis, slika, cena, valuta, dostupnost, SKU, prosecna ocena, broj recenzija
  - BreadcrumbList: hijerarhija navigacije
  - Organization: ime sajta, logo, kontakt
  - FAQPage: za stranice sa pitanjima i odgovorima
  - Injektovano kao JSON-LD u `<head>` tagove

- [x] **Feature flag sistem**
  - 14 flag-ova u Settings tabeli
  - Kontrolisu vidljivost: wishlist, newsletter, compare, social proof, store credits, multi-currency, gift cards, loyalty, webhooks, abandoned cart, shop the look, store locator, downloads, multi-language
  - Provera na frontendu: `useFeature().isEnabled('feature_name')`
  - Provera na backendu: `Setting::getValue('feature_name')`
  - Admin UI: toggle prekidaci u Settings → Feature Flags tabu

- [x] **Price history audit trail (30-dnevni minimum)**
  - `ProductPriceHistory` tabela: snapshot regularne i sale cene sa `recorded_at`
  - `ProductObserver` automatski belezi pri kreiranju proizvoda i svakoj promeni cene
  - `Product::lowest_price_30_days` accessor — najniza efektivna cena 30 dana pre starta akcije
  - Zakonska obaveza po Zakonu o trgovini RS (cl. 32a) — prikaz najnize cene pre sniženja
  - `BackfillPriceHistory` artisan komanda za inicijalno popunjavanje

- [x] **Stock movements audit trail**
  - Svaka promena stock-a se loguje: kolicina, tip (sale/return/adjustment/restock/cancellation), referenca, beleska, stock posle
  - Tipovi: prodaja (automatski pri checkout-u), povrat, rucna korekcija, restock, otkazivanje
  - Koristi se za inventurnu reviziju i debugging

- [x] **Login lockout**
  - Vazi za User i Admin (HasLoginLockout trait)
  - Broji neuspesne pokusaje logovanja
  - Privremeno zakljucava nalog posle previse pokusaja
  - locked_until timestamp kontrolise trajanje lockout-a

- [x] **Rate limiting**
  - Public API: `api-public` throttle group
  - Admin API: `api-auth` i `admin-login` throttle groups
  - Kontakt forma: dodatni throttle (zastita od spama)
  - Zastita od abuse-a i DDoS-a
