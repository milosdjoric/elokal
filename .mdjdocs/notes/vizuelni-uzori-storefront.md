# Vizuelni uzori — storefront (sloj redizajn)

> Zabeleženo 2026-06-18. Ova dva uzora nisu bila nigde u `.mdjdocs/` —
> živela su samo u git commit porukama i komentarima koda. Ovde su sad zvanično.

Redizajn storefront-a (`clients/sloj/storefront`, Nuxt layer nad elokal base-om) imao je
**dva odvojena vizuelna uzora**, svaki za svoj sloj odluka:

| Uzor                         | Šta je diktirao                                                                   | Gde je trag       |
| ---------------------------- | --------------------------------------------------------------------------------- | ----------------- |
| **Vitra** (vitra.com)        | **layout / struktura** — header, hero, grid, PDP, "mirna" hijerarhija, puno belog | komentari u kodu  |
| **Pangaia** (thepangaia.com) | **brending** — paleta boja, tipografija, editorial ton                            | git commit poruke |

## Vitra — layout uzor

Pominje se direktno u komentarima kroz ceo sloj storefront:

- `components/layout/SiteHeader.vue` — top utility traka ("Find Vitra | Contact" stil), Title Case nav
- `pages/index.vue` — hero full-bleed + centered overlay; full-bleed photo strip kategorije;
  "Naš izbor" carousel; editorial mosaic ("Scout Work / Accessories" stil); "Vitra-restraint" story sekcija
- `components/product/ProductCard.vue` — bela bg, `object-contain`, dosta whitespace, "Vitra calm hierarchy"
- `components/product/ProductGallery.vue` — bela bg galerija, `object-contain`
- `pages/proizvodi/index.vue` — crni breadcrumb band; hero strip (foto + naslov); full-bleed 4-col grid; hidden filter panel
- `pages/proizvodi/[slug].vue` — akordeon sekcije na PDP-u

Napomena: Vitra je relevantna i kao **brend** — jedini ovlašćeni proizvođač Eames
**Plywood Group** (LCW/DCW) stolica od savijene šperploče, pa se uklapa i tematski
(sloj demo = nameštaj od šperploče, scrape sa opendesk.cc).

## Pangaia — brend uzor

Trag u git commit porukama (redizajn je rađen u starom `/MdjWeb/sloj` forku):

```
c17eb6a  rebrand to Pangaia-inspired earth palette + Newsreader/Manrope fonts
2509335  rebrand homepage to Pangaia editorial
2982b64  docs: track Pangaia rebrand v0.7.x in todo (6 fazes)
3837131  rebrand v2 — Pangaia accurate (white + electric blue + sans bold)
```

Dve iteracije:

1. **v1** — "Pangaia-inspired earth palette", tipografija Newsreader/Manrope
2. **v2** — "Pangaia accurate": bela pozadina + electric blue akcent + bold sans

## Veza sa arhitekturom

Ovaj redizajn je razlog postojanja layer modela — vidi [CORE-004](../tasks/CORE-004.md)
(fork → Nuxt Layer) i [arhitektura-core-sloj.md](arhitektura-core-sloj.md). Vizuelne
razlike žive **per-klijent** (u sloj layeru), elokal storefront ostaje neutralan base.
