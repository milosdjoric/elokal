# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/homepage.spec.ts >> Storefront: Homepage >> featured proizvodi
- Location: e2e/storefront/homepage.spec.ts:22:7

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/", waiting until "load"

```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test'
  2  | 
  3  | test.describe('Storefront: Homepage', () => {
  4  |   test.beforeEach(async ({ page }) => {
> 5  |     await page.goto('/')
     |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  6  |   })
  7  | 
  8  |   test('homepage se renderuje', async ({ page }) => {
  9  |     await expect(page.locator('body')).toBeVisible()
  10 |     // Header
  11 |     await expect(page.locator('header')).toBeVisible()
  12 |     // Footer
  13 |     await expect(page.locator('footer')).toBeVisible()
  14 |   })
  15 | 
  16 |   test('hero sekcija', async ({ page }) => {
  17 |     // Hero slideshow ili baner
  18 |     const hero = page.locator('[class*="hero"], [class*="slide"], [class*="banner"]').first()
  19 |     // Ne assertujemo jer može biti konfigurisan
  20 |   })
  21 | 
  22 |   test('featured proizvodi', async ({ page }) => {
  23 |     // Carousel ili grid sa featured proizvodima
  24 |     const productCards = page.locator('a[href*="/products/"]')
  25 |     const count = await productCards.count()
  26 |     expect(count).toBeGreaterThan(0)
  27 |   })
  28 | 
  29 |   test('kategorije grid', async ({ page }) => {
  30 |     const categoryLinks = page.locator('a[href*="/categories/"]')
  31 |     // Može ili ne mora biti na homepage-u
  32 |   })
  33 | 
  34 |   test('newsletter sekcija', async ({ page }) => {
  35 |     const newsletterInput = page.locator('input[type="email"]').last()
  36 |     // Newsletter sekcija može biti u footer-u
  37 |   })
  38 | })
  39 | 
```