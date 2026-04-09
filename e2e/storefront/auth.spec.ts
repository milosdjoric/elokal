import { test, expect } from '@playwright/test'
import { storefrontLogin, storefrontRegister } from '../helpers/auth'

test.describe('Storefront: Autentifikacija', () => {
  test('login stranica se renderuje', async ({ page }) => {
    await page.goto('/account/login')
    await expect(page.getByRole('heading', { name: 'Prijavite se' })).toBeVisible()
    await expect(page.locator('form')).toBeVisible()
  })

  test('uspešan login sa validnim kredencijalima', async ({ page }) => {
    await storefrontLogin(page, 'kupac@test.com', 'password')
    await expect(page).toHaveURL(/\/account/)
  })

  test('neuspešan login sa pogrešnom lozinkom', async ({ page }) => {
    await page.goto('/account/login')

    const emailInput = page.locator('div').filter({ hasText: /^Email/ }).last().locator('input')
    const passwordInput = page.locator('div').filter({ hasText: /^Lozinka/ }).last().locator('input')

    await emailInput.fill('kupac@test.com')
    await passwordInput.fill('pogresna-lozinka')
    await page.getByRole('button', { name: 'Prijavi se' }).click()

    // Očekujemo error poruku
    await expect(page.locator('.bg-red-50')).toBeVisible()
    // Ostajemo na login stranici
    await expect(page).toHaveURL(/\/login/)
  })

  test('registracija stranica se renderuje', async ({ page }) => {
    await page.goto('/account/register')
    await expect(page.getByRole('heading', { name: 'Kreirajte nalog' })).toBeVisible()
  })

  test('uspešna registracija novog korisnika', async ({ page }) => {
    const timestamp = Date.now()
    await storefrontRegister(page, {
      name: `Test Korisnik ${timestamp}`,
      email: `test-${timestamp}@example.com`,
      password: 'Password123!',
      phone: '+381641234567',
    })
    await expect(page).toHaveURL(/\/account/)
  })

  test('registracija sa postojećim emailom prikazuje grešku', async ({ page }) => {
    await page.goto('/account/register')

    const form = page.locator('form')
    await form.locator('div').filter({ hasText: /^Ime i prezime/ }).last().locator('input').fill('Test Test')
    await form.locator('div').filter({ hasText: /^Email/ }).last().locator('input').fill('kupac@test.com')
    await form.locator('div').filter({ hasText: /^Lozinka$/ }).last().locator('input').fill('Password123!')
    await form.locator('div').filter({ hasText: /^Potvrdite lozinku/ }).last().locator('input').fill('Password123!')

    await page.getByRole('button', { name: 'Registruj se' }).click()

    // Očekujemo validacionu grešku za email
    await expect(page.locator('.text-red-600')).toBeVisible({ timeout: 5000 })
  })

  test('auth guard — zaštićena stranica redirectuje na login', async ({ page }) => {
    await page.goto('/account/orders')
    await expect(page).toHaveURL(/\/login|\/prijava/)
  })

  test('post-login redirect — vraća korisnika gde je bio', async ({ page }) => {
    // Pokušaj pristup zaštićenoj stranici
    await page.goto('/account/wishlist')

    // Ako redirect na login, logujemo se
    if (page.url().includes('login') || page.url().includes('prijava')) {
      await storefrontLogin(page, 'kupac@test.com', 'password')
      // Treba da nas vrati na wishlist
      await expect(page).toHaveURL(/\/wishlist|\/lista-zelja/)
    }
  })

  test('navigacija — link ka registraciji na login stranici', async ({ page }) => {
    await page.goto('/account/login')
    await page.getByRole('link', { name: 'Registrujte se' }).click()
    await expect(page).toHaveURL(/\/register|\/registracija/)
  })

  test('navigacija — link ka loginu na registraciji', async ({ page }) => {
    await page.goto('/account/register')
    await page.getByRole('link', { name: 'Prijavite se' }).click()
    await expect(page).toHaveURL(/\/login|\/prijava/)
  })
})
