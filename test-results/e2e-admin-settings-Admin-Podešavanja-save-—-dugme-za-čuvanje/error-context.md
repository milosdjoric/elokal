# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/admin/settings.spec.ts >> Admin: Podešavanja >> save — dugme za čuvanje
- Location: e2e/admin/settings.spec.ts:30:7

# Error details

```
Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
Call log:
  - navigating to "/login", waiting until "load"

```

# Test source

```ts
  1  | import { type Page } from '@playwright/test'
  2  | 
  3  | /**
  4  |  * Helper za popunjavanje UiAtomsInput komponente.
  5  |  * Label nema for atribut, pa lociramo tekst labela i biramo input u istom kontejneru.
  6  |  */
  7  | async function fillInput(page: Page, labelText: string, value: string) {
  8  |   const container = page.locator('div').filter({ hasText: new RegExp(`^${labelText}`) }).last()
  9  |   await container.locator('input').fill(value)
  10 | }
  11 | 
  12 | export async function storefrontLogin(page: Page, email: string, password: string) {
  13 |   await page.goto('/account/login')
  14 |   await fillInput(page, 'Email', email)
  15 |   await fillInput(page, 'Lozinka', password)
  16 |   await page.getByRole('button', { name: 'Prijavi se' }).click()
  17 |   await page.waitForURL(/\/(account|$)/)
  18 | }
  19 | 
  20 | export async function storefrontRegister(
  21 |   page: Page,
  22 |   user: { name: string; email: string; password: string; phone?: string }
  23 | ) {
  24 |   await page.goto('/account/register')
  25 |   await fillInput(page, 'Ime i prezime', user.name)
  26 |   await fillInput(page, 'Email', user.email)
  27 |   if (user.phone) {
  28 |     await fillInput(page, 'Telefon', user.phone)
  29 |   }
  30 |   await fillInput(page, 'Lozinka', user.password)
  31 |   await fillInput(page, 'Potvrdite lozinku', user.password)
  32 |   await page.getByRole('button', { name: 'Registruj se' }).click()
  33 |   await page.waitForURL(/\/account/)
  34 | }
  35 | 
  36 | export async function storefrontLogout(page: Page) {
  37 |   await page.goto('/account')
  38 |   await page.getByRole('link', { name: /odjav/i }).or(page.getByRole('button', { name: /odjav/i })).click()
  39 |   await page.waitForURL('/')
  40 | }
  41 | 
  42 | export async function adminLogin(page: Page, email: string, password: string) {
> 43 |   await page.goto('/login')
     |              ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  44 |   await page.locator('#email').fill(email)
  45 |   await page.locator('#password').fill(password)
  46 |   await page.getByRole('button', { name: 'Prijavi se' }).click()
  47 |   await page.waitForURL('/')
  48 | }
  49 | 
  50 | export async function adminLogout(page: Page) {
  51 |   await page.getByRole('button', { name: /odjav/i }).click()
  52 |   await page.waitForURL(/\/login/)
  53 | }
  54 | 
```