# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/blog.spec.ts >> Storefront: Blog >> sidebar — kategorije, tagovi, recent posts
- Location: e2e/storefront/blog.spec.ts:26:7

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/blog", waiting until "load"

```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test'
  2  | 
  3  | test.describe('Storefront: Blog', () => {
  4  |   test('/blog — listing se renderuje', async ({ page }) => {
  5  |     await page.goto('/blog')
  6  |     await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  7  |   })
  8  | 
  9  |   test('/blog — postovi su vidljivi ili prazna poruka', async ({ page }) => {
  10 |     await page.goto('/blog')
  11 |     await page.waitForTimeout(1000)
  12 |     const hasPost = await page.locator('a[href*="/blog/"]').first().isVisible()
  13 |     const isEmpty = await page.getByText(/nema|prazno/i).isVisible()
  14 |     expect(hasPost || isEmpty || true).toBeTruthy() // Prolazi uvek, samo proveravamo da nema crash
  15 |   })
  16 | 
  17 |   test('/blog/:slug — post stranica', async ({ page }) => {
  18 |     await page.goto('/blog')
  19 |     const postLink = page.locator('a[href*="/blog/"]').filter({ hasNot: page.locator('a[href*="/kategorija/"]') }).first()
  20 |     if (await postLink.isVisible()) {
  21 |       await postLink.click()
  22 |       await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  23 |     }
  24 |   })
  25 | 
  26 |   test('sidebar — kategorije, tagovi, recent posts', async ({ page }) => {
> 27 |     await page.goto('/blog')
     |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  28 |     // Sidebar widgeti
  29 |     const sidebar = page.locator('aside').first()
  30 |     // Ne assertujemo jer layout može varirati
  31 |   })
  32 | })
  33 | 
```