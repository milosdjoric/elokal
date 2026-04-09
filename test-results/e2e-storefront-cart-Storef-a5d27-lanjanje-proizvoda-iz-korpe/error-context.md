# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/cart.spec.ts >> Storefront: Korpa >> uklanjanje proizvoda iz korpe
- Location: e2e/storefront/cart.spec.ts:45:7

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/", waiting until "load"

```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test'
  2   | 
  3   | test.describe('Storefront: Korpa', () => {
  4   |   // Pomoćna: dodaj proizvod u korpu navigacijom na PDP
  5   |   async function addProductToCart(page: import('@playwright/test').Page) {
  6   |     // Idemo na prodavnicu, kliknemo prvi proizvod
  7   |     await page.goto('/products')
  8   |     await page.locator('a[href*="/products/"]').first().click()
  9   |     await page.waitForURL(/\/products\//)
  10  | 
  11  |     // Sačekaj da se PDP učita
  12  |     await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  13  | 
  14  |     // Klikni "Dodaj u korpu" (ako postoje varijante, možda treba selekcija — preskoči za osnovni test)
  15  |     const addBtn = page.getByRole('button', { name: 'Dodaj u korpu' }).first()
  16  |     if (await addBtn.isVisible()) {
  17  |       await addBtn.click()
  18  |     }
  19  |   }
  20  | 
  21  |   test('korpa stranica — prazna korpa prikazuje poruku', async ({ page }) => {
  22  |     // Očisti localStorage
  23  |     await page.goto('/')
  24  |     await page.evaluate(() => localStorage.removeItem('cart'))
  25  | 
  26  |     await page.goto('/cart')
  27  |     await expect(page.getByText('Vaša korpa je prazna')).toBeVisible()
  28  |     await expect(page.getByRole('button', { name: 'Nastavi kupovinu' }).or(page.getByRole('link', { name: 'Nastavi kupovinu' }))).toBeVisible()
  29  |   })
  30  | 
  31  |   test('dodavanje proizvoda u korpu sa PDP-a', async ({ page }) => {
  32  |     await page.goto('/')
  33  |     await page.evaluate(() => localStorage.removeItem('cart'))
  34  | 
  35  |     await addProductToCart(page)
  36  | 
  37  |     // Cart badge ili drawer treba da pokaže 1
  38  |     // Idemo na cart stranicu da proverimo
  39  |     await page.goto('/cart')
  40  |     await expect(page.getByText('Vaša korpa je prazna')).not.toBeVisible()
  41  |     // Treba da postoji barem jedan CartItem
  42  |     await expect(page.getByText('Ukloni').first()).toBeVisible()
  43  |   })
  44  | 
  45  |   test('uklanjanje proizvoda iz korpe', async ({ page }) => {
> 46  |     await page.goto('/')
      |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  47  |     await page.evaluate(() => localStorage.removeItem('cart'))
  48  | 
  49  |     await addProductToCart(page)
  50  |     await page.goto('/cart')
  51  | 
  52  |     // Klikni "Ukloni"
  53  |     await page.getByText('Ukloni').first().click()
  54  | 
  55  |     // Korpa treba da bude prazna
  56  |     await expect(page.getByText('Vaša korpa je prazna')).toBeVisible()
  57  |   })
  58  | 
  59  |   test('persistent cart — korpa preživljava refresh', async ({ page }) => {
  60  |     await page.goto('/')
  61  |     await page.evaluate(() => localStorage.removeItem('cart'))
  62  | 
  63  |     await addProductToCart(page)
  64  |     await page.goto('/cart')
  65  | 
  66  |     // Zapamti naziv proizvoda
  67  |     const productName = await page.locator('a[href*="/products/"]').first().innerText()
  68  | 
  69  |     // Refresh
  70  |     await page.reload()
  71  | 
  72  |     // Proizvod još uvek u korpi
  73  |     await expect(page.getByText(productName)).toBeVisible()
  74  |   })
  75  | 
  76  |   test('cart totals — prikazuje pregled narudžbine', async ({ page }) => {
  77  |     await page.goto('/')
  78  |     await page.evaluate(() => localStorage.removeItem('cart'))
  79  | 
  80  |     await addProductToCart(page)
  81  |     await page.goto('/cart')
  82  | 
  83  |     // CartTotals sekcija
  84  |     await expect(page.getByText('Pregled narudžbine')).toBeVisible()
  85  |     await expect(page.getByText('Ukupno')).toBeVisible()
  86  |     // "Nastavi ka plaćanju" dugme
  87  |     await expect(page.getByRole('link', { name: /plaćanj/i }).or(page.getByRole('button', { name: /plaćanj/i }))).toBeVisible()
  88  |   })
  89  | 
  90  |   test('coupon — nevažeći kupon prikazuje grešku', async ({ page }) => {
  91  |     await page.goto('/')
  92  |     await page.evaluate(() => localStorage.removeItem('cart'))
  93  | 
  94  |     await addProductToCart(page)
  95  |     await page.goto('/cart')
  96  | 
  97  |     // Unesite nevažeći kupon
  98  |     const couponInput = page.locator('input[placeholder="Kod kupona"]')
  99  |     if (await couponInput.isVisible()) {
  100 |       await couponInput.fill('NEPOSTOJECI123')
  101 |       await page.getByRole('button', { name: 'Primeni' }).click()
  102 | 
  103 |       // Očekujemo error
  104 |       await expect(page.locator('.text-red-600')).toBeVisible({ timeout: 5000 })
  105 |     }
  106 |   })
  107 | 
  108 |   test('nastavi ka plaćanju — vodi na checkout', async ({ page }) => {
  109 |     await page.goto('/')
  110 |     await page.evaluate(() => localStorage.removeItem('cart'))
  111 | 
  112 |     await addProductToCart(page)
  113 |     await page.goto('/cart')
  114 | 
  115 |     // Klikni "Nastavi ka plaćanju"
  116 |     await page.getByRole('link', { name: /plaćanj/i }).or(page.getByRole('button', { name: /plaćanj/i })).click()
  117 |     await expect(page).toHaveURL(/\/checkout/)
  118 |   })
  119 | })
  120 | 
```