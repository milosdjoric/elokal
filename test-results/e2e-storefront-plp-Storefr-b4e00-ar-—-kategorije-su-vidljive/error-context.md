# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/plp.spec.ts >> Storefront: PLP (Product Listing Page) >> sidebar — kategorije su vidljive
- Location: e2e/storefront/plp.spec.ts:41:7

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/products", waiting until "load"

```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test'
  2  | 
  3  | test.describe('Storefront: PLP (Product Listing Page)', () => {
  4  |   test.beforeEach(async ({ page }) => {
> 5  |     await page.goto('/products')
     |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  6  |     await expect(page.getByRole('heading', { name: 'Proizvodi', exact: false })).toBeVisible({ timeout: 10000 })
  7  |   })
  8  | 
  9  |   test('stranica se renderuje sa proizvodima', async ({ page }) => {
  10 |     // Toolbar sa brojem proizvoda
  11 |     await expect(page.getByText(/\d+ proizvoda/)).toBeVisible()
  12 |     // Grid sa proizvodima
  13 |     await expect(page.locator('a[href*="/products/"]').first()).toBeVisible()
  14 |   })
  15 | 
  16 |   test('sortiranje — promena sort dropdown-a', async ({ page }) => {
  17 |     const sortSelect = page.locator('select').filter({ hasText: 'Najnovije' })
  18 |     await sortSelect.selectOption('price')
  19 |     // URL treba da sadrži sort=price
  20 |     await expect(page).toHaveURL(/sort=price/)
  21 |   })
  22 | 
  23 |   test('per page — promena broja po stranici', async ({ page }) => {
  24 |     const perPageSelect = page.locator('select').filter({ hasText: '12' })
  25 |     await perPageSelect.selectOption('24')
  26 |     await expect(page).toHaveURL(/per_page=24/)
  27 |   })
  28 | 
  29 |   test('layout switcher — grid/list/compact', async ({ page }) => {
  30 |     // Lista dugme
  31 |     const listBtn = page.locator('button[title="Lista"]')
  32 |     await listBtn.click()
  33 |     // Compact dugme
  34 |     const compactBtn = page.locator('button[title="Kompaktno"]')
  35 |     await compactBtn.click()
  36 |     // Grid dugme
  37 |     const gridBtn = page.locator('button[title="Grid"]')
  38 |     await gridBtn.click()
  39 |   })
  40 | 
  41 |   test('sidebar — kategorije su vidljive', async ({ page }) => {
  42 |     await expect(page.getByText('Kategorije')).toBeVisible()
  43 |     await expect(page.getByText('Sve kategorije')).toBeVisible()
  44 |   })
  45 | 
  46 |   test('sidebar — klik na kategoriju filtrira', async ({ page }) => {
  47 |     // Klikni prvu kategoriju (ne "Sve kategorije")
  48 |     const catButton = page.locator('aside button').nth(1)
  49 |     const catName = await catButton.innerText()
  50 |     await catButton.click()
  51 |     // URL treba da sadrži category param
  52 |     await expect(page).toHaveURL(/category=/)
  53 |   })
  54 | 
  55 |   test('sidebar — cena filter', async ({ page }) => {
  56 |     await expect(page.getByText('Cena')).toBeVisible()
  57 |     // Popuni min cenu
  58 |     const minInput = page.locator('aside input[type="number"]').first()
  59 |     await minInput.fill('1000')
  60 |     // Klikni "Primeni"
  61 |     await page.locator('aside').getByText('Primeni').click()
  62 |     await expect(page).toHaveURL(/min_price=1000/)
  63 |   })
  64 | 
  65 |   test('active filters — prikaz i uklanjanje', async ({ page }) => {
  66 |     // Selektuj kategoriju da imamo active filter
  67 |     const catButton = page.locator('aside button').nth(1)
  68 |     await catButton.click()
  69 |     await page.waitForTimeout(500)
  70 | 
  71 |     // Active filter chip treba da bude vidljiv
  72 |     const filterChip = page.locator('.rounded-full').first()
  73 |     if (await filterChip.isVisible()) {
  74 |       // Klikni X da ukloniš filter
  75 |       await filterChip.locator('button').click()
  76 |       // URL ne treba da ima category
  77 |       await expect(page).not.toHaveURL(/category=/)
  78 |     }
  79 |   })
  80 | 
  81 |   test('paginacija — klik na sledeću stranicu', async ({ page }) => {
  82 |     // Proveravamo da li postoji paginacija
  83 |     const pageButtons = page.locator('.flex.justify-center.gap-2 button')
  84 |     const count = await pageButtons.count()
  85 |     if (count > 1) {
  86 |       await pageButtons.nth(1).click()
  87 |       await expect(page).toHaveURL(/page=2/)
  88 |     }
  89 |   })
  90 | 
  91 |   test('klik na proizvod vodi na PDP', async ({ page }) => {
  92 |     const productLink = page.locator('a[href*="/products/"]').first()
  93 |     await productLink.click()
  94 |     await expect(page).toHaveURL(/\/products\/[^/]+$/)
  95 |   })
  96 | })
  97 | 
```