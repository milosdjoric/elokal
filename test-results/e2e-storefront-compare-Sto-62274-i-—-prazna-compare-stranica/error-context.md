# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/compare.spec.ts >> Storefront: Product Compare >> /uporedi — prazna compare stranica
- Location: e2e/storefront/compare.spec.ts:4:7

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
  3  | test.describe('Storefront: Product Compare', () => {
  4  |   test('/uporedi — prazna compare stranica', async ({ page }) => {
> 5  |     await page.goto('/')
     |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  6  |     await page.evaluate(() => localStorage.removeItem('compare'))
  7  |     await page.goto('/uporedi')
  8  |     // Prazna poruka ili tabela
  9  |     await expect(page.locator('body')).toBeVisible()
  10 |   })
  11 | 
  12 |   test('compare dugme na product card-u', async ({ page }) => {
  13 |     await page.goto('/products')
  14 |     // Compare dugme/ikonica na prvom proizvodu
  15 |     const compareBtn = page.locator('button[title*="poredi"], button[aria-label*="poredi"]').first()
  16 |     // Zavisi od feature flag-a
  17 |   })
  18 | })
  19 | 
```