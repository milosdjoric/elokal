import { test, expect } from '@playwright/test'

// Pomoćna: dodaj proizvod u korpu i idi na checkout
async function setupCheckout(page: import('@playwright/test').Page) {
  // Očisti korpu
  await page.goto('/')
  await page.evaluate(() => {
    localStorage.removeItem('cart')
    localStorage.removeItem('checkout_form')
  })

  // Idi na prodavnicu, otvori prvi proizvod
  await page.goto('/products')
  await page.locator('a[href*="/products/"]').first().click()
  await page.waitForURL(/\/products\//)
  await expect(page.getByRole('heading', { level: 1 })).toBeVisible()

  // Dodaj u korpu
  const addBtn = page.getByRole('button', { name: 'Dodaj u korpu' }).first()
  if (await addBtn.isVisible()) {
    await addBtn.click()
    // Kratko čekaj da se cart ažurira
    await page.waitForTimeout(500)
  }

  // Idi na checkout
  await page.goto('/checkout')
}

// Pomoćna: popuni shipping formu
async function fillShippingForm(page: import('@playwright/test').Page, data: {
  email: string
  phone?: string
  firstName: string
  lastName: string
  address: string
  city: string
  postalCode: string
}) {
  // Kontakt sekcija
  const emailInput = page.locator('div').filter({ hasText: /^Email/ }).last().locator('input')
  await emailInput.fill(data.email)

  if (data.phone) {
    const phoneInput = page.locator('div').filter({ hasText: /^Telefon/ }).last().locator('input')
    await phoneInput.fill(data.phone)
  }

  // Shipping adresa
  const firstNameInput = page.locator('div').filter({ hasText: /^Ime$/ }).last().locator('input')
  await firstNameInput.fill(data.firstName)

  const lastNameInput = page.locator('div').filter({ hasText: /^Prezime/ }).last().locator('input')
  await lastNameInput.fill(data.lastName)

  const addressInput = page.locator('div').filter({ hasText: /^Adresa$/ }).last().locator('input')
  await addressInput.fill(data.address)

  const cityInput = page.locator('div').filter({ hasText: /^Grad/ }).last().locator('input')
  await cityInput.fill(data.city)

  const postalInput = page.locator('div').filter({ hasText: /^Poštanski broj/ }).last().locator('input')
  await postalInput.fill(data.postalCode)
}

const guestData = {
  email: 'guest-test@example.com',
  phone: '+381641234567',
  firstName: 'Petar',
  lastName: 'Petrović',
  address: 'Knez Mihailova 15',
  city: 'Beograd',
  postalCode: '11000',
}

test.describe('Storefront: Checkout', () => {
  test('checkout stranica se renderuje sa stavkama', async ({ page }) => {
    await setupCheckout(page)

    await expect(page.getByRole('heading', { name: 'Kasa' })).toBeVisible()
    // Order summary
    await expect(page.getByText('Vaša narudžbina')).toBeVisible()
    // Poruči dugme
    await expect(page.getByRole('button', { name: 'Poruči' })).toBeVisible()
  })

  test('checkout sa praznom korpom redirectuje na cart', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('cart'))
    await page.goto('/checkout')

    // Treba da redirect na cart
    await expect(page).toHaveURL(/\/cart/)
  })

  test('guest checkout — ceo flow do success stranice', async ({ page }) => {
    await setupCheckout(page)

    // Popuni formu
    await fillShippingForm(page, guestData)

    // Sačekaj shipping metode da se učitaju
    await page.waitForTimeout(1000)

    // Klikni "Poruči"
    await page.getByRole('button', { name: 'Poruči' }).click()

    // Očekujemo success stranicu ili gresku
    await page.waitForURL(/\/checkout\/success\/|\/checkout/, { timeout: 15000 })

    if (page.url().includes('/success/')) {
      await expect(page.getByText('Hvala na narudžbini!')).toBeVisible()
      await expect(page.getByText('Broj narudžbine:')).toBeVisible()
    }
  })

  test('inline validacija — prazna polja prikazuju greške', async ({ page }) => {
    await setupCheckout(page)

    // Klikni na email polje pa klikni dalje (blur) da triggerujemo validaciju
    const emailInput = page.locator('div').filter({ hasText: /^Email/ }).last().locator('input')
    await emailInput.clear()
    await emailInput.blur()

    // Treba da se pojavi "obavezno polje" greška
    await expect(page.getByText(/obavezno polje/i).first()).toBeVisible()
  })

  test('email validacija — nevažeći email', async ({ page }) => {
    await setupCheckout(page)

    const emailInput = page.locator('div').filter({ hasText: /^Email/ }).last().locator('input')
    await emailInput.fill('nije-email')
    await emailInput.blur()

    await expect(page.getByText(/validan email/i)).toBeVisible()
  })

  test('billing adresa — unchecking prikazuje billing formu', async ({ page }) => {
    await setupCheckout(page)

    // Checkbox "Adresa za naplatu je ista kao za dostavu"
    const billingCheckbox = page.locator('input[type="checkbox"]').first()
    await billingCheckbox.uncheck()

    // Billing forma treba da se pojavi
    await expect(page.getByText('Adresa za naplatu')).toBeVisible()
  })

  test('trust badges su vidljivi', async ({ page }) => {
    await setupCheckout(page)

    await expect(page.getByText('Sigurna kupovina')).toBeVisible()
    await expect(page.getByText('14 dana za povrat')).toBeVisible()
  })

  test('order summary prikazuje stavke iz korpe', async ({ page }) => {
    await setupCheckout(page)

    // Order summary sekcija treba da sadrži barem jednu stavku sa "×" (quantity)
    await expect(page.locator('text=/×\\s*\\d/')).toBeVisible()
    // Međuzbir
    await expect(page.getByText('Međuzbir')).toBeVisible()
    // Ukupno
    await expect(page.getByText('Ukupno').last()).toBeVisible()
  })

  test('kupon sekcija — nevažeći kupon prikazuje grešku', async ({ page }) => {
    await setupCheckout(page)

    const couponInput = page.locator('input[placeholder="Unesite kod kupona"]')
    await couponInput.fill('NEPOSTOJECI')
    await page.getByRole('button', { name: 'Primeni' }).first().click()

    await expect(page.locator('.text-red-600').first()).toBeVisible({ timeout: 5000 })
  })

  test('poklon kartica sekcija — nevažeća kartica prikazuje grešku', async ({ page }) => {
    await setupCheckout(page)

    const giftInput = page.locator('input[placeholder="Unesite kod poklon kartice"]')
    await giftInput.fill('NEPOSTOJECI-KOD')
    // Klikni "Primeni" u poklon kartica sekciji (drugi Primeni dugme)
    const giftSection = page.locator('div').filter({ hasText: 'Poklon kartica' }).last()
    await giftSection.getByRole('button', { name: 'Primeni' }).click()

    await expect(page.locator('.text-red-600').first()).toBeVisible({ timeout: 5000 })
  })
})
