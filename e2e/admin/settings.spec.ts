import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Podešavanja', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('settings stranica se renderuje', async ({ page }) => {
    await page.goto('/settings')
    await expect(page.getByRole('heading', { name: /Podešavanja|Settings/i })).toBeVisible()
  })

  test('tabovi su vidljivi', async ({ page }) => {
    await page.goto('/settings')
    // General tab
    await expect(page.getByText(/Opšte|General/i)).toBeVisible()
  })

  test('general tab — osnovna polja', async ({ page }) => {
    await page.goto('/settings')
    // Klikni na General tab ako nije aktivan
    const generalTab = page.getByText(/Opšte|General/i).first()
    await generalTab.click()
    await page.waitForTimeout(500)
    // Treba da ima input polja (naziv, email, itd.)
    await expect(page.locator('input').first()).toBeVisible()
  })

  test('save — dugme za čuvanje', async ({ page }) => {
    await page.goto('/settings')
    await expect(page.getByRole('button', { name: /Sačuvaj|Save/i })).toBeVisible()
  })
})
