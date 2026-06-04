# Opendesk seed alati

Jednokratne skripte kojima su skinuti sloj proizvodi sa [opendesk.cc](https://opendesk.cc)
i očišćene slike. **Nisu deo aktivne aplikacije** — čuvaju se ovde kao referenca
(prebačeno iz starog `MdjWeb/sloj` foldera koji je obrisan).

## Komande

| Fajl | Artisan komanda | Šta radi |
|------|-----------------|----------|
| `OpendeskScrape.php` | `php artisan opendesk:scrape` | Skida katalog sa opendesk.cc → `.data/opendesk.json` + `storage/app/public/products/opendesk/` |
| `OpendeskRemoveBg.php` | `php artisan opendesk:remove-bg` | Skida pozadinu sa slika preko `rembg`, snima kao PNG, update-uje `ProductImage` path-ove |

## Kako se koriste (ako ikad zatreba re-scrape)

Ovo su Laravel Artisan komande (`namespace App\Console\Commands`). Da bi radile,
kopiraj ih nazad u Laravel app:

```bash
cp tools/opendesk/Opendesk*.php api/app/Console/Commands/
php artisan opendesk:scrape
php artisan opendesk:remove-bg   # zahteva `rembg` instaliran lokalno
```

> Baza je već seed-ovana (proizvodi u Neon), pa ovo ne treba za normalan rad —
> samo ako praviš novi katalog iz Opendesk izvora.
