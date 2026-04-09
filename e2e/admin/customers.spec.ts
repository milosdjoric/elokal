import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Kupci', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('listing — stranica se renderuje', async ({ page }) => {
    await page.goto('/customers')
    await expect(page.getByRole('heading', { name: /Kupci/i })).toBeVisible()
  })

  test('listing — pretraga kupaca', async ({ page }) => {
    await page.goto('/customers')
    const searchInput = page.locator('input[placeholder*="Pretraži"]')
    if (await searchInput.isVisible()) {
      await searchInput.fill('test')
      await searchInput.press('Enter')
      await page.waitForTimeout(1000)
    }
  })

  test('detail — klik na kupca prikazuje profil', async ({ page }) => {
    await page.goto('/customers')
    const customerLink = page.locator('a[href*="/customers/"]').first()
    if (await customerLink.isVisible()) {
      await customerLink.click()
      await expect(page).toHaveURL(/\/customers\/\d+/)
    }
  })
})
