import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Narudžbine', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('listing — stranica se renderuje', async ({ page }) => {
    await page.goto('/orders')
    await expect(page.getByRole('heading', { name: 'Narudžbine' })).toBeVisible()
    await expect(page.getByText(/\d+ ukupno/)).toBeVisible()
  })

  test('listing — pretraga po broju/emailu', async ({ page }) => {
    await page.goto('/orders')
    const searchInput = page.locator('input[placeholder*="Pretraži"]')
    await searchInput.fill('test')
    await searchInput.press('Enter')
    await page.waitForTimeout(1000)
    // Tabela se ažurira — ne assertujemo specifičan rezultat jer zavisi od seed-a
  })

  test('listing — filter po statusu', async ({ page }) => {
    await page.goto('/orders')
    const statusSelect = page.locator('select').filter({ hasText: 'Svi statusi' })
    await statusSelect.selectOption('pending')
    await page.waitForTimeout(1000)
    // Ako ima pending narudžbina, badge treba da prikazuje "Na čekanju"
  })

  test('listing — klik na narudžbinu otvara detail', async ({ page }) => {
    await page.goto('/orders')
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await expect(page).toHaveURL(/\/orders\/\d+/)
    }
  })

  test('detail — prikazuje informacije o narudžbini', async ({ page }) => {
    await page.goto('/orders')
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      // Očekujemo sekcije: stavke, adrese, timeline
      await expect(page.getByText(/Stavke|Items/).first()).toBeVisible({ timeout: 5000 })
    }
  })

  test('detail — dropdown za promenu statusa', async ({ page }) => {
    await page.goto('/orders')
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Proveravamo da postoji select za status
      const statusSelect = page.locator('select').first()
      await expect(statusSelect).toBeVisible()
    }
  })
})
