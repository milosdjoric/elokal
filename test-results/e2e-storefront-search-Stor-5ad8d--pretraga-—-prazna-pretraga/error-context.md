# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/search.spec.ts >> Storefront: Pretraga >> /pretraga — prazna pretraga
- Location: e2e/storefront/search.spec.ts:26:7

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/search", waiting until "load"

```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test'
  2  | 
  3  | test.describe('Storefront: Pretraga', () => {
  4  |   test('AJAX autocomplete u headeru', async ({ page }) => {
  5  |     await page.goto('/')
  6  |     const searchInput = page.locator('header input[type="text"], header input[type="search"]').first()
  7  |     if (await searchInput.isVisible()) {
  8  |       await searchInput.fill('majica')
  9  |       // Čekaj da se dropdown pojavi (debounce 300ms)
  10 |       await page.waitForTimeout(500)
  11 |       // Dropdown sa rezultatima
  12 |       const dropdown = page.locator('[class*="absolute"], [class*="dropdown"]').first()
  13 |       // Ne assertujemo jer zavisi od seed data
  14 |     }
  15 |   })
  16 | 
  17 |   test('/pretraga — search stranica renderuje rezultate', async ({ page }) => {
  18 |     await page.goto('/search?q=test')
  19 |     // Toolbar sa brojem rezultata ili "Nema rezultata" poruka
  20 |     await page.waitForTimeout(1000)
  21 |     const hasResults = await page.getByText(/\d+ proizvoda/).isVisible()
  22 |     const noResults = await page.getByText(/Nema|nema|rezultat/).isVisible()
  23 |     expect(hasResults || noResults).toBeTruthy()
  24 |   })
  25 | 
  26 |   test('/pretraga — prazna pretraga', async ({ page }) => {
> 27 |     await page.goto('/search')
     |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  28 |     // Treba da prikaže nešto (trending/recent ili empty state)
  29 |     await expect(page.locator('body')).toBeVisible()
  30 |   })
  31 | 
  32 |   test('recent searches — čuvaju se u localStorage', async ({ page }) => {
  33 |     await page.goto('/')
  34 |     await page.evaluate(() => localStorage.removeItem('recent_searches'))
  35 | 
  36 |     await page.goto('/search?q=test-pretaga-123')
  37 |     await page.waitForTimeout(1000)
  38 | 
  39 |     // Proverimo localStorage
  40 |     const recentSearches = await page.evaluate(() => localStorage.getItem('recent_searches'))
  41 |     // Može ili ne mora da čuva — zavisi od implementacije
  42 |   })
  43 | })
  44 | 
```