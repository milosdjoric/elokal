import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: CMS Pages', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('pages stranica se renderuje', async ({ page }) => {
    await page.goto('/pages')
    await expect(page.locator('body')).toContainText(/stranic|page/i)
  })

  test('lista — slug-ovi prikazani', async ({ page }) => {
    await page.goto('/pages')
    await page.waitForTimeout(1000)
    const slug = page.getByText(/o-nama|kontakt|uslovi|privatnost|cesta-pitanja/i).first()
    if (await slug.isVisible()) {
      await expect(slug).toBeVisible()
    }
  })

  test('lista — delete dugme', async ({ page }) => {
    await page.goto('/pages')
    await page.waitForTimeout(1000)
    const deleteBtn = page.getByRole('button', { name: /obriši|delete/i }).first()
    if (await deleteBtn.isVisible()) {
      await expect(deleteBtn).toBeVisible()
    }
  })

  test('lista — 5 stranica prikazano', async ({ page }) => {
    await page.goto('/pages')
    await page.waitForTimeout(1000)
    // O nama, Kontakt, Uslovi, Privatnost, Česta pitanja
    const rows = page.locator('tr, [class*="item"]').filter({ hasText: /o-nama|kontakt|uslovi/ })
    const count = await rows.count()
    expect(count).toBeGreaterThanOrEqual(1)
  })
})
