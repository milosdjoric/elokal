import { test, expect } from '@playwright/test'
import { adminLogin, adminLogout } from '../helpers/auth'

test.describe('Admin: Autentifikacija', () => {
  test('login stranica se renderuje', async ({ page }) => {
    await page.goto('/login')
    await expect(page.getByRole('heading', { name: 'eLokal Admin' })).toBeVisible()
    await expect(page.locator('form')).toBeVisible()
  })

  test('uspešan login sa admin kredencijalima', async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
    // Treba da budemo na dashboard-u
    await expect(page).toHaveURL('/')
    // Dashboard content treba da bude vidljiv
    await expect(page.locator('body')).not.toContainText('Prijavi se')
  })

  test('neuspešan login sa pogrešnim kredencijalima', async ({ page }) => {
    await page.goto('/login')
    await page.locator('#email').fill('admin@webshop.test')
    await page.locator('#password').fill('pogresna-lozinka')
    await page.getByRole('button', { name: 'Prijavi se' }).click()

    // Očekujemo error poruku
    await expect(page.locator('.bg-red-50')).toBeVisible()
    // Ostajemo na login stranici
    await expect(page).toHaveURL(/\/login/)
  })

  test('auth guard — dashboard bez tokena redirectuje na login', async ({ page }) => {
    await page.goto('/')
    await expect(page).toHaveURL(/\/login/)
  })

  test('auth guard — products stranica bez tokena redirectuje na login', async ({ page }) => {
    await page.goto('/products')
    await expect(page).toHaveURL(/\/login/)
  })

  test('logout — vraća na login stranicu', async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
    await adminLogout(page)
    await expect(page).toHaveURL(/\/login/)
  })

  test('nakon logina — sidebar navigacija je vidljiva', async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
    // Proveravamo da sidebar postoji sa ključnim linkovima
    const sidebar = page.locator('nav, aside').first()
    await expect(sidebar).toBeVisible()
  })
})
