# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/pdp.spec.ts >> Storefront: PDP (Product Detail Page) >> tabovi — opis, recenzije, dostava, FAQ
- Location: e2e/storefront/pdp.spec.ts:36:7

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/products", waiting until "load"

```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test'
  2   | 
  3   | test.describe('Storefront: PDP (Product Detail Page)', () => {
  4   |   // Navigiraj na PDP prvog proizvoda
  5   |   async function goToPDP(page: import('@playwright/test').Page) {
> 6   |     await page.goto('/products')
      |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  7   |     await page.locator('a[href*="/products/"]').first().click()
  8   |     await page.waitForURL(/\/products\//)
  9   |     await expect(page.getByRole('heading', { level: 1 })).toBeVisible({ timeout: 10000 })
  10  |   }
  11  | 
  12  |   test('PDP se renderuje sa svim sekcijama', async ({ page }) => {
  13  |     await goToPDP(page)
  14  | 
  15  |     // Naslov proizvoda
  16  |     await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  17  |     // Cena
  18  |     await expect(page.getByText('RSD')).toBeVisible()
  19  |     // Add to cart dugme (ako ima stock)
  20  |     const addBtn = page.getByRole('button', { name: 'Dodaj u korpu' }).first()
  21  |     // Može biti disabled ako treba varijanta
  22  |     await expect(addBtn.or(page.getByText('Obavesti me'))).toBeVisible()
  23  |   })
  24  | 
  25  |   test('galerija — thumbnail klik menja glavnu sliku', async ({ page }) => {
  26  |     await goToPDP(page)
  27  | 
  28  |     const thumbnails = page.locator('img').filter({ has: page.locator('[class*="cursor"]') })
  29  |     const count = await thumbnails.count()
  30  |     if (count > 1) {
  31  |       await thumbnails.nth(1).click()
  32  |       // Prosto proveravamo da nema greške
  33  |     }
  34  |   })
  35  | 
  36  |   test('tabovi — opis, recenzije, dostava, FAQ', async ({ page }) => {
  37  |     await goToPDP(page)
  38  | 
  39  |     // Tab navigacija
  40  |     await expect(page.getByText('Opis')).toBeVisible()
  41  |     await expect(page.getByText('Recenzije')).toBeVisible()
  42  |     await expect(page.getByText('Dostava i povrat')).toBeVisible()
  43  |     await expect(page.getByText('FAQ')).toBeVisible()
  44  | 
  45  |     // Klik na tab "Recenzije"
  46  |     await page.getByText('Recenzije').click()
  47  |     // Klik na tab "FAQ"
  48  |     await page.getByText('FAQ').click()
  49  |   })
  50  | 
  51  |   test('quantity selector', async ({ page }) => {
  52  |     await goToPDP(page)
  53  | 
  54  |     const qtyPlus = page.locator('button').filter({ hasText: '+' }).first()
  55  |     const qtyMinus = page.locator('button').filter({ hasText: '−' }).or(page.locator('button').filter({ hasText: '-' })).first()
  56  | 
  57  |     if (await qtyPlus.isVisible()) {
  58  |       await qtyPlus.click()
  59  |       // Količina treba da bude 2
  60  |     }
  61  |   })
  62  | 
  63  |   test('breadcrumbs su vidljivi', async ({ page }) => {
  64  |     await goToPDP(page)
  65  |     await expect(page.getByText('Početna').first()).toBeVisible()
  66  |   })
  67  | 
  68  |   test('related products sekcija', async ({ page }) => {
  69  |     await goToPDP(page)
  70  | 
  71  |     // Scroll do dna da se učita related
  72  |     await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight))
  73  |     await page.waitForTimeout(1000)
  74  | 
  75  |     // Related/slični proizvodi — može biti carousel
  76  |     const relatedSection = page.getByText(/Slični|Povezani|Možda|Related/).first()
  77  |     // Ne assert-ujemo jer ne mora da postoji
  78  |   })
  79  | 
  80  |   test('social share dugmad', async ({ page }) => {
  81  |     await goToPDP(page)
  82  |     // Social share sekcija (FB, X, Pinterest, etc.)
  83  |     const shareButtons = page.locator('a[href*="facebook"], a[href*="twitter"], a[href*="pinterest"]')
  84  |     // Ne assertujemo count jer zavisi od konfiguracije
  85  |   })
  86  | 
  87  |   test('add to cart — proizvod se dodaje', async ({ page }) => {
  88  |     await page.goto('/')
  89  |     await page.evaluate(() => localStorage.removeItem('cart'))
  90  | 
  91  |     await goToPDP(page)
  92  | 
  93  |     const addBtn = page.getByRole('button', { name: 'Dodaj u korpu' }).first()
  94  |     if (await addBtn.isVisible() && await addBtn.isEnabled()) {
  95  |       await addBtn.click()
  96  |       await page.waitForTimeout(500)
  97  | 
  98  |       // Idemo na cart da proverimo
  99  |       await page.goto('/cart')
  100 |       await expect(page.getByText('Vaša korpa je prazna')).not.toBeVisible()
  101 |     }
  102 |   })
  103 | })
  104 | 
```