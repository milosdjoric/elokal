import { test, expect } from '@playwright/test'

test.describe('Storefront: Korpa', () => {
  // Pomoćna: dodaj proizvod u korpu navigacijom na PDP
  async function addProductToCart(page: import('@playwright/test').Page) {
    // Idemo na prodavnicu, kliknemo prvi proizvod
    await page.goto('/products')
    await page.locator('a[href*="/products/"]').first().click()
    await page.waitForURL(/\/products\//)

    // Sačekaj da se PDP učita
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible()

    // Klikni "Dodaj u korpu" (ako postoje varijante, možda treba selekcija — preskoči za osnovni test)
    const addBtn = page.getByRole('button', { name: 'Dodaj u korpu' }).first()
    if (await addBtn.isVisible()) {
      await addBtn.click()
    }
  }

  test('korpa stranica — prazna korpa prikazuje poruku', async ({ page }) => {
    // Očisti localStorage
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('cart'))

    await page.goto('/cart')
    await expect(page.getByText('Vaša korpa je prazna')).toBeVisible()
    await expect(page.getByRole('button', { name: 'Nastavi kupovinu' }).or(page.getByRole('link', { name: 'Nastavi kupovinu' }))).toBeVisible()
  })

  test('dodavanje proizvoda u korpu sa PDP-a', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('cart'))

    await addProductToCart(page)

    // Cart badge ili drawer treba da pokaže 1
    // Idemo na cart stranicu da proverimo
    await page.goto('/cart')
    await expect(page.getByText('Vaša korpa je prazna')).not.toBeVisible()
    // Treba da postoji barem jedan CartItem
    await expect(page.getByText('Ukloni').first()).toBeVisible()
  })

  test('uklanjanje proizvoda iz korpe', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('cart'))

    await addProductToCart(page)
    await page.goto('/cart')

    // Klikni "Ukloni"
    await page.getByText('Ukloni').first().click()

    // Korpa treba da bude prazna
    await expect(page.getByText('Vaša korpa je prazna')).toBeVisible()
  })

  test('persistent cart — korpa preživljava refresh', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('cart'))

    await addProductToCart(page)
    await page.goto('/cart')

    // Zapamti naziv proizvoda
    const productName = await page.locator('a[href*="/products/"]').first().innerText()

    // Refresh
    await page.reload()

    // Proizvod još uvek u korpi
    await expect(page.getByText(productName)).toBeVisible()
  })

  test('cart totals — prikazuje pregled narudžbine', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('cart'))

    await addProductToCart(page)
    await page.goto('/cart')

    // CartTotals sekcija
    await expect(page.getByText('Pregled narudžbine')).toBeVisible()
    await expect(page.getByText('Ukupno')).toBeVisible()
    // "Nastavi ka plaćanju" dugme
    await expect(page.getByRole('link', { name: /plaćanj/i }).or(page.getByRole('button', { name: /plaćanj/i }))).toBeVisible()
  })

  test('coupon — nevažeći kupon prikazuje grešku', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('cart'))

    await addProductToCart(page)
    await page.goto('/cart')

    // Unesite nevažeći kupon
    const couponInput = page.locator('input[placeholder="Kod kupona"]')
    if (await couponInput.isVisible()) {
      await couponInput.fill('NEPOSTOJECI123')
      await page.getByRole('button', { name: 'Primeni' }).click()

      // Očekujemo error
      await expect(page.locator('.text-red-600')).toBeVisible({ timeout: 5000 })
    }
  })

  test('nastavi ka plaćanju — vodi na checkout', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('cart'))

    await addProductToCart(page)
    await page.goto('/cart')

    // Klikni "Nastavi ka plaćanju"
    await page.getByRole('link', { name: /plaćanj/i }).or(page.getByRole('button', { name: /plaćanj/i })).click()
    await expect(page).toHaveURL(/\/checkout/)
  })
})
