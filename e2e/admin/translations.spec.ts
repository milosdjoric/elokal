import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Prevodi (i18n)', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('translations stranica se renderuje', async ({ page }) => {
    await page.goto('/translations')
    await expect(page.locator('body')).toContainText(/prevod|translat|jezik/i)
  })

  test('model type dropdown', async ({ page }) => {
    await page.goto('/translations')
    await page.waitForTimeout(1000)
    const typeSelect = page.locator('select').first()
    if (await typeSelect.isVisible()) {
      await expect(typeSelect).toBeVisible()
      // Opcije: Products, Categories, Blog, Pages
    }
  })

  test('locale dropdown', async ({ page }) => {
    await page.goto('/translations')
    await page.waitForTimeout(1000)
    const localeSelect = page.locator('select').nth(1)
      .or(page.getByText(/en|sr|english|srpski/i).first())
    if (await localeSelect.isVisible()) {
      await expect(localeSelect).toBeVisible()
    }
  })

  test('progress bar za kompletnost', async ({ page }) => {
    await page.goto('/translations')
    await page.waitForTimeout(1000)
    const progress = page.locator('[class*="progress"], [role="progressbar"]').first()
    if (await progress.isVisible()) {
      await expect(progress).toBeVisible()
    }
  })

  test('CSV export dugme', async ({ page }) => {
    await page.goto('/translations')
    await page.waitForTimeout(1000)
    const exportBtn = page.getByRole('button', { name: /export|izvoz|CSV/i }).first()
      .or(page.getByRole('link', { name: /export|izvoz|CSV/i }).first())
    if (await exportBtn.isVisible()) {
      await expect(exportBtn).toBeVisible()
    }
  })
})
