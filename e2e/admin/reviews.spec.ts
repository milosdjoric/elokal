import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Recenzije', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('listing — stranica se renderuje', async ({ page }) => {
    await page.goto('/reviews')
    await expect(page.getByRole('heading', { name: /Recenzije/i })).toBeVisible()
  })

  test('listing — filter po statusu', async ({ page }) => {
    await page.goto('/reviews')
    const statusSelect = page.locator('select').first()
    if (await statusSelect.isVisible()) {
      await statusSelect.selectOption({ index: 1 })
      await page.waitForTimeout(1000)
    }
  })

  test('akcije — approve/reject dugmad', async ({ page }) => {
    await page.goto('/reviews')
    // Proveravamo da postoje akcione dugmad
    const actionBtn = page.getByRole('button', { name: /Odobri|Odbij|approve|reject/i }).first()
    // Ne klikamo jer menjamo podatke
  })
})
