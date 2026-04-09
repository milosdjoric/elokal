# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/misc.spec.ts >> Storefront: Ostale stranice i funkcionalnosti >> /pracenje/:number — tracking stranica
- Location: e2e/storefront/misc.spec.ts:19:7

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/pracenje/NEPOSTOJECI123", waiting until "load"

```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test'
  2  | 
  3  | test.describe('Storefront: Ostale stranice i funkcionalnosti', () => {
  4  |   test('/poklon-kartica/provera — gift card balance check', async ({ page }) => {
  5  |     await page.goto('/poklon-kartica/provera')
  6  |     await expect(page.locator('body')).toBeVisible()
  7  |     // Input za kod i dugme za proveru
  8  |     const input = page.locator('input').first()
  9  |     if (await input.isVisible()) {
  10 |       await input.fill('NEPOSTOJECI')
  11 |       const checkBtn = page.getByRole('button', { name: /proveri|Proveri/i })
  12 |       if (await checkBtn.isVisible()) {
  13 |         await checkBtn.click()
  14 |         await page.waitForTimeout(1000)
  15 |       }
  16 |     }
  17 |   })
  18 | 
  19 |   test('/pracenje/:number — tracking stranica', async ({ page }) => {
> 20 |     await page.goto('/pracenje/NEPOSTOJECI123')
     |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  21 |     // Treba da prikaže poruku da narudžbina nije pronađena ili tracking info
  22 |     await expect(page.locator('body')).toBeVisible()
  23 |   })
  24 | 
  25 |   test('404 stranica', async ({ page }) => {
  26 |     await page.goto('/nepostojeca-stranica-xyz-123')
  27 |     // Custom 404 ili Nuxt error page
  28 |     await expect(page.locator('body')).toBeVisible()
  29 |     // Može da sadrži "404" ili "nije pronađena"
  30 |   })
  31 | 
  32 |   test('cookie consent baner', async ({ page }) => {
  33 |     await page.goto('/')
  34 |     await page.evaluate(() => localStorage.removeItem('cookie_consent'))
  35 |     await page.reload()
  36 |     // Cookie baner može da se pojavi
  37 |     const cookieBanner = page.getByText(/kolačić|cookie/i).first()
  38 |     // Ne assertujemo jer zavisi od konfiguracije
  39 |   })
  40 | 
  41 |   test('mobile navigacija — bottom nav vidljiv na mobilnom', async ({ page }) => {
  42 |     // Simuliraj mobilni uređaj
  43 |     await page.setViewportSize({ width: 375, height: 812 })
  44 |     await page.goto('/')
  45 |     // Mobile bottom nav
  46 |     const mobileNav = page.locator('nav').last()
  47 |     await expect(mobileNav).toBeVisible()
  48 |   })
  49 | 
  50 |   test('top bar — promo poruka', async ({ page }) => {
  51 |     await page.goto('/')
  52 |     // Top bar sa promo porukom — zavisi od admin podešavanja
  53 |   })
  54 | 
  55 |   test('currency switcher', async ({ page }) => {
  56 |     await page.goto('/')
  57 |     // Dropdown za valutu u headeru — zavisi od konfiguracije
  58 |     const currencySwitch = page.locator('[class*="currency"], select').filter({ hasText: /RSD|EUR|USD/ }).first()
  59 |     // Ne assertujemo jer može biti isključen
  60 |   })
  61 | 
  62 |   test('back to top dugme', async ({ page }) => {
  63 |     await page.goto('/products')
  64 |     // Scroll dole
  65 |     await page.evaluate(() => window.scrollTo(0, 2000))
  66 |     await page.waitForTimeout(500)
  67 |     // Back to top dugme treba da se pojavi
  68 |     const backToTop = page.locator('button[title*="vrh"], button[aria-label*="vrh"]').first()
  69 |     // Ne assertujemo jer implementacija može varirati
  70 |   })
  71 | })
  72 | 
```