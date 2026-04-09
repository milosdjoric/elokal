# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/admin/auth.spec.ts >> Admin: Autentifikacija >> neuspešan login sa pogrešnim kredencijalima
- Location: e2e/admin/auth.spec.ts:19:7

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/login", waiting until "load"

```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test'
  2  | import { adminLogin, adminLogout } from '../helpers/auth'
  3  | 
  4  | test.describe('Admin: Autentifikacija', () => {
  5  |   test('login stranica se renderuje', async ({ page }) => {
  6  |     await page.goto('/login')
  7  |     await expect(page.getByRole('heading', { name: 'eLokal Admin' })).toBeVisible()
  8  |     await expect(page.locator('form')).toBeVisible()
  9  |   })
  10 | 
  11 |   test('uspešan login sa admin kredencijalima', async ({ page }) => {
  12 |     await adminLogin(page, 'admin@webshop.test', 'password')
  13 |     // Treba da budemo na dashboard-u
  14 |     await expect(page).toHaveURL('/')
  15 |     // Dashboard content treba da bude vidljiv
  16 |     await expect(page.locator('body')).not.toContainText('Prijavi se')
  17 |   })
  18 | 
  19 |   test('neuspešan login sa pogrešnim kredencijalima', async ({ page }) => {
> 20 |     await page.goto('/login')
     |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  21 |     await page.locator('#email').fill('admin@webshop.test')
  22 |     await page.locator('#password').fill('pogresna-lozinka')
  23 |     await page.getByRole('button', { name: 'Prijavi se' }).click()
  24 | 
  25 |     // Očekujemo error poruku
  26 |     await expect(page.locator('.bg-red-50')).toBeVisible()
  27 |     // Ostajemo na login stranici
  28 |     await expect(page).toHaveURL(/\/login/)
  29 |   })
  30 | 
  31 |   test('auth guard — dashboard bez tokena redirectuje na login', async ({ page }) => {
  32 |     await page.goto('/')
  33 |     await expect(page).toHaveURL(/\/login/)
  34 |   })
  35 | 
  36 |   test('auth guard — products stranica bez tokena redirectuje na login', async ({ page }) => {
  37 |     await page.goto('/products')
  38 |     await expect(page).toHaveURL(/\/login/)
  39 |   })
  40 | 
  41 |   test('logout — vraća na login stranicu', async ({ page }) => {
  42 |     await adminLogin(page, 'admin@webshop.test', 'password')
  43 |     await adminLogout(page)
  44 |     await expect(page).toHaveURL(/\/login/)
  45 |   })
  46 | 
  47 |   test('nakon logina — sidebar navigacija je vidljiva', async ({ page }) => {
  48 |     await adminLogin(page, 'admin@webshop.test', 'password')
  49 |     // Proveravamo da sidebar postoji sa ključnim linkovima
  50 |     const sidebar = page.locator('nav, aside').first()
  51 |     await expect(sidebar).toBeVisible()
  52 |   })
  53 | })
  54 | 
```