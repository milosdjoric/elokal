# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/storefront/checkout.spec.ts >> Storefront: Checkout >> checkout sa praznom korpom redirectuje na cart
- Location: e2e/storefront/checkout.spec.ts:87:7

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
  6   |   await page.goto('/')
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
> 88  |     await page.goto('/')
      |                ^ Error: page.goto: Protocol error (Page.navigate): Cannot navigate to invalid URL
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
  107 | 
  108 |     // Očekujemo success stranicu ili gresku
  109 |     await page.waitForURL(/\/checkout\/success\/|\/checkout/, { timeout: 15000 })
  110 | 
  111 |     if (page.url().includes('/success/')) {
  112 |       await expect(page.getByText('Hvala na narudžbini!')).toBeVisible()
  113 |       await expect(page.getByText('Broj narudžbine:')).toBeVisible()
  114 |     }
  115 |   })
  116 | 
  117 |   test('inline validacija — prazna polja prikazuju greške', async ({ page }) => {
  118 |     await setupCheckout(page)
  119 | 
  120 |     // Klikni na email polje pa klikni dalje (blur) da triggerujemo validaciju
  121 |     const emailInput = page.locator('div').filter({ hasText: /^Email/ }).last().locator('input')
  122 |     await emailInput.clear()
  123 |     await emailInput.blur()
  124 | 
  125 |     // Treba da se pojavi "obavezno polje" greška
  126 |     await expect(page.getByText(/obavezno polje/i).first()).toBeVisible()
  127 |   })
  128 | 
  129 |   test('email validacija — nevažeći email', async ({ page }) => {
  130 |     await setupCheckout(page)
  131 | 
  132 |     const emailInput = page.locator('div').filter({ hasText: /^Email/ }).last().locator('input')
  133 |     await emailInput.fill('nije-email')
  134 |     await emailInput.blur()
  135 | 
  136 |     await expect(page.getByText(/validan email/i)).toBeVisible()
  137 |   })
  138 | 
  139 |   test('billing adresa — unchecking prikazuje billing formu', async ({ page }) => {
  140 |     await setupCheckout(page)
  141 | 
  142 |     // Checkbox "Adresa za naplatu je ista kao za dostavu"
  143 |     const billingCheckbox = page.locator('input[type="checkbox"]').first()
  144 |     await billingCheckbox.uncheck()
  145 | 
  146 |     // Billing forma treba da se pojavi
  147 |     await expect(page.getByText('Adresa za naplatu')).toBeVisible()
  148 |   })
  149 | 
  150 |   test('trust badges su vidljivi', async ({ page }) => {
  151 |     await setupCheckout(page)
  152 | 
  153 |     await expect(page.getByText('Sigurna kupovina')).toBeVisible()
  154 |     await expect(page.getByText('14 dana za povrat')).toBeVisible()
  155 |   })
  156 | 
  157 |   test('order summary prikazuje stavke iz korpe', async ({ page }) => {
  158 |     await setupCheckout(page)
  159 | 
  160 |     // Order summary sekcija treba da sadrži barem jednu stavku sa "×" (quantity)
  161 |     await expect(page.locator('text=/×\\s*\\d/')).toBeVisible()
  162 |     // Međuzbir
  163 |     await expect(page.getByText('Međuzbir')).toBeVisible()
  164 |     // Ukupno
  165 |     await expect(page.getByText('Ukupno').last()).toBeVisible()
  166 |   })
  167 | 
  168 |   test('kupon sekcija — nevažeći kupon prikazuje grešku', async ({ page }) => {
  169 |     await setupCheckout(page)
  170 | 
  171 |     const couponInput = page.locator('input[placeholder="Unesite kod kupona"]')
  172 |     await couponInput.fill('NEPOSTOJECI')
  173 |     await page.getByRole('button', { name: 'Primeni' }).first().click()
  174 | 
  175 |     await expect(page.locator('.text-red-600').first()).toBeVisible({ timeout: 5000 })
  176 |   })
  177 | 
  178 |   test('poklon kartica sekcija — nevažeća kartica prikazuje grešku', async ({ page }) => {
  179 |     await setupCheckout(page)
  180 | 
  181 |     const giftInput = page.locator('input[placeholder="Unesite kod poklon kartice"]')
  182 |     await giftInput.fill('NEPOSTOJECI-KOD')
  183 |     // Klikni "Primeni" u poklon kartica sekciji (drugi Primeni dugme)
  184 |     const giftSection = page.locator('div').filter({ hasText: 'Poklon kartica' }).last()
  185 |     await giftSection.getByRole('button', { name: 'Primeni' }).click()
  186 | 
  187 |     await expect(page.locator('.text-red-600').first()).toBeVisible({ timeout: 5000 })
  188 |   })
```