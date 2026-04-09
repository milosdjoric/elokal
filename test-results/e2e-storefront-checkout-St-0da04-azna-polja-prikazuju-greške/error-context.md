# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/checkout.spec.ts >> Storefront: Checkout >> inline validacija — prazna polja prikazuju greške
- Location: e2e/storefront/checkout.spec.ts:117:7

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
  3   | // Pomoćna: dodaj proizvod u korpu i idi na checkout
  4   | async function setupCheckout(page: import('@playwright/test').Page) {
  5   |   // Očisti korpu
> 6   |   await page.goto('/')
      |              ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
  7   |   await page.evaluate(() => {
  8   |     localStorage.removeItem('cart')
  9   |     localStorage.removeItem('checkout_form')
  10  |   })
  11  | 
  12  |   // Idi na prodavnicu, otvori prvi proizvod
  13  |   await page.goto('/products')
  14  |   await page.locator('a[href*="/products/"]').first().click()
  15  |   await page.waitForURL(/\/products\//)
  16  |   await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
  17  | 
  18  |   // Dodaj u korpu
  19  |   const addBtn = page.getByRole('button', { name: 'Dodaj u korpu' }).first()
  20  |   if (await addBtn.isVisible()) {
  21  |     await addBtn.click()
  22  |     // Kratko čekaj da se cart ažurira
  23  |     await page.waitForTimeout(500)
  24  |   }
  25  | 
  26  |   // Idi na checkout
  27  |   await page.goto('/checkout')
  28  | }
  29  | 
  30  | // Pomoćna: popuni shipping formu
  31  | async function fillShippingForm(page: import('@playwright/test').Page, data: {
  32  |   email: string
  33  |   phone?: string
  34  |   firstName: string
  35  |   lastName: string
  36  |   address: string
  37  |   city: string
  38  |   postalCode: string
  39  | }) {
  40  |   // Kontakt sekcija
  41  |   const emailInput = page.locator('div').filter({ hasText: /^Email/ }).last().locator('input')
  42  |   await emailInput.fill(data.email)
  43  | 
  44  |   if (data.phone) {
  45  |     const phoneInput = page.locator('div').filter({ hasText: /^Telefon/ }).last().locator('input')
  46  |     await phoneInput.fill(data.phone)
  47  |   }
  48  | 
  49  |   // Shipping adresa
  50  |   const firstNameInput = page.locator('div').filter({ hasText: /^Ime$/ }).last().locator('input')
  51  |   await firstNameInput.fill(data.firstName)
  52  | 
  53  |   const lastNameInput = page.locator('div').filter({ hasText: /^Prezime/ }).last().locator('input')
  54  |   await lastNameInput.fill(data.lastName)
  55  | 
  56  |   const addressInput = page.locator('div').filter({ hasText: /^Adresa$/ }).last().locator('input')
  57  |   await addressInput.fill(data.address)
  58  | 
  59  |   const cityInput = page.locator('div').filter({ hasText: /^Grad/ }).last().locator('input')
  60  |   await cityInput.fill(data.city)
  61  | 
  62  |   const postalInput = page.locator('div').filter({ hasText: /^Poštanski broj/ }).last().locator('input')
  63  |   await postalInput.fill(data.postalCode)
  64  | }
  65  | 
  66  | const guestData = {
  67  |   email: 'guest-test@example.com',
  68  |   phone: '+381641234567',
  69  |   firstName: 'Petar',
  70  |   lastName: 'Petrović',
  71  |   address: 'Knez Mihailova 15',
  72  |   city: 'Beograd',
  73  |   postalCode: '11000',
  74  | }
  75  | 
  76  | test.describe('Storefront: Checkout', () => {
  77  |   test('checkout stranica se renderuje sa stavkama', async ({ page }) => {
  78  |     await setupCheckout(page)
  79  | 
  80  |     await expect(page.getByRole('heading', { name: 'Kasa' })).toBeVisible()
  81  |     // Order summary
  82  |     await expect(page.getByText('Vaša narudžbina')).toBeVisible()
  83  |     // Poruči dugme
  84  |     await expect(page.getByRole('button', { name: 'Poruči' })).toBeVisible()
  85  |   })
  86  | 
  87  |   test('checkout sa praznom korpom redirectuje na cart', async ({ page }) => {
  88  |     await page.goto('/')
  89  |     await page.evaluate(() => localStorage.removeItem('cart'))
  90  |     await page.goto('/checkout')
  91  | 
  92  |     // Treba da redirect na cart
  93  |     await expect(page).toHaveURL(/\/cart/)
  94  |   })
  95  | 
  96  |   test('guest checkout — ceo flow do success stranice', async ({ page }) => {
  97  |     await setupCheckout(page)
  98  | 
  99  |     // Popuni formu
  100 |     await fillShippingForm(page, guestData)
  101 | 
  102 |     // Sačekaj shipping metode da se učitaju
  103 |     await page.waitForTimeout(1000)
  104 | 
  105 |     // Klikni "Poruči"
  106 |     await page.getByRole('button', { name: 'Poruči' }).click()
```