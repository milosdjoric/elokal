# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/static-pages.spec.ts >> Storefront: Statičke stranice >> /kontakt — stranica se renderuje
- Location: e2e/storefront/static-pages.spec.ts:13:9

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/kontakt", waiting until "load"

```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test'
  2  | 
  3  | test.describe('Storefront: Statičke stranice', () => {
  4  |   const pages = [
  5  |     { path: '/o-nama', title: /o nama/i },
  6  |     { path: '/kontakt', title: /kontakt/i },
  7  |     { path: '/uslovi-koriscenja', title: /uslovi/i },
  8  |     { path: '/politika-privatnosti', title: /privatnost/i },
  9  |     { path: '/cesta-pitanja', title: /pitanja|FAQ/i },
  10 |   ]
  11 | 
  12 |   for (const pg of pages) {
  13 |     test(`${pg.path} — stranica se renderuje`, async ({ page }) => {
> 14 |       const response = await page.goto(pg.path)
     |                                   ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  15 |       // Ako stranica postoji (200) ili je catch-all slug
  16 |       if (response && response.status() < 400) {
  17 |         await expect(page.getByRole('heading', { level: 1 })).toBeVisible({ timeout: 5000 })
  18 |       }
  19 |     })
  20 |   }
  21 | })
  22 | 
```