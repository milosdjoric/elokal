# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/auth.spec.ts >> Storefront: Autentifikacija >> registracija stranica se renderuje
- Location: e2e/storefront/auth.spec.ts:32:7

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/account/register", waiting until "load"

```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test'
  2  | import { storefrontLogin, storefrontRegister } from '../helpers/auth'
  3  | 
  4  | test.describe('Storefront: Autentifikacija', () => {
  5  |   test('login stranica se renderuje', async ({ page }) => {
  6  |     await page.goto('/account/login')
  7  |     await expect(page.getByRole('heading', { name: 'Prijavite se' })).toBeVisible()
  8  |     await expect(page.locator('form')).toBeVisible()
  9  |   })
  10 | 
  11 |   test('uspešan login sa validnim kredencijalima', async ({ page }) => {
  12 |     await storefrontLogin(page, 'kupac@test.com', 'password')
  13 |     await expect(page).toHaveURL(/\/account/)
  14 |   })
  15 | 
  16 |   test('neuspešan login sa pogrešnom lozinkom', async ({ page }) => {
  17 |     await page.goto('/account/login')
  18 | 
  19 |     const emailInput = page.locator('div').filter({ hasText: /^Email/ }).last().locator('input')
  20 |     const passwordInput = page.locator('div').filter({ hasText: /^Lozinka/ }).last().locator('input')
  21 | 
  22 |     await emailInput.fill('kupac@test.com')
  23 |     await passwordInput.fill('pogresna-lozinka')
  24 |     await page.getByRole('button', { name: 'Prijavi se' }).click()
  25 | 
  26 |     // Očekujemo error poruku
  27 |     await expect(page.locator('.bg-red-50')).toBeVisible()
  28 |     // Ostajemo na login stranici
  29 |     await expect(page).toHaveURL(/\/login/)
  30 |   })
  31 | 
  32 |   test('registracija stranica se renderuje', async ({ page }) => {
> 33 |     await page.goto('/account/register')
     |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  34 |     await expect(page.getByRole('heading', { name: 'Kreirajte nalog' })).toBeVisible()
  35 |   })
  36 | 
  37 |   test('uspešna registracija novog korisnika', async ({ page }) => {
  38 |     const timestamp = Date.now()
  39 |     await storefrontRegister(page, {
  40 |       name: `Test Korisnik ${timestamp}`,
  41 |       email: `test-${timestamp}@example.com`,
  42 |       password: 'Password123!',
  43 |       phone: '+381641234567',
  44 |     })
  45 |     await expect(page).toHaveURL(/\/account/)
  46 |   })
  47 | 
  48 |   test('registracija sa postojećim emailom prikazuje grešku', async ({ page }) => {
  49 |     await page.goto('/account/register')
  50 | 
  51 |     const form = page.locator('form')
  52 |     await form.locator('div').filter({ hasText: /^Ime i prezime/ }).last().locator('input').fill('Test Test')
  53 |     await form.locator('div').filter({ hasText: /^Email/ }).last().locator('input').fill('kupac@test.com')
  54 |     await form.locator('div').filter({ hasText: /^Lozinka$/ }).last().locator('input').fill('Password123!')
  55 |     await form.locator('div').filter({ hasText: /^Potvrdite lozinku/ }).last().locator('input').fill('Password123!')
  56 | 
  57 |     await page.getByRole('button', { name: 'Registruj se' }).click()
  58 | 
  59 |     // Očekujemo validacionu grešku za email
  60 |     await expect(page.locator('.text-red-600')).toBeVisible({ timeout: 5000 })
  61 |   })
  62 | 
  63 |   test('auth guard — zaštićena stranica redirectuje na login', async ({ page }) => {
  64 |     await page.goto('/account/orders')
  65 |     await expect(page).toHaveURL(/\/login|\/prijava/)
  66 |   })
  67 | 
  68 |   test('post-login redirect — vraća korisnika gde je bio', async ({ page }) => {
  69 |     // Pokušaj pristup zaštićenoj stranici
  70 |     await page.goto('/account/wishlist')
  71 | 
  72 |     // Ako redirect na login, logujemo se
  73 |     if (page.url().includes('login') || page.url().includes('prijava')) {
  74 |       await storefrontLogin(page, 'kupac@test.com', 'password')
  75 |       // Treba da nas vrati na wishlist
  76 |       await expect(page).toHaveURL(/\/wishlist|\/lista-zelja/)
  77 |     }
  78 |   })
  79 | 
  80 |   test('navigacija — link ka registraciji na login stranici', async ({ page }) => {
  81 |     await page.goto('/account/login')
  82 |     await page.getByRole('link', { name: 'Registrujte se' }).click()
  83 |     await expect(page).toHaveURL(/\/register|\/registracija/)
  84 |   })
  85 | 
  86 |   test('navigacija — link ka loginu na registraciji', async ({ page }) => {
  87 |     await page.goto('/account/register')
  88 |     await page.getByRole('link', { name: 'Prijavite se' }).click()
  89 |     await expect(page).toHaveURL(/\/login|\/prijava/)
  90 |   })
  91 | })
  92 | 
```