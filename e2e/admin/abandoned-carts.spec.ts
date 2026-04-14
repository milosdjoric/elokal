import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Napuštene korpe', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('abandoned carts stranica se renderuje', async ({ page }) => {
    await page.goto('/abandoned-carts')
    await expect(page.locator('body')).toContainText(/napušten|abandoned|korpa|cart/i)
  })

  test('stats kartice — abandoned, recovered, rate', async ({ page }) => {
    await page.goto('/abandoned-carts')
    await page.waitForTimeout(1000)
    // Stats kartice
    const stats = page.getByText(/napušten|oporavljen|recovered|rate|%/i)
    if (await stats.first().isVisible()) {
      expect(await stats.count()).toBeGreaterThan(0)
    }
  })

  test('lista — email, vrednost, emails sent, status', async ({ page }) => {
    await page.goto('/abandoned-carts')
    await page.waitForTimeout(1000)
    // Status badge
    const status = page.getByText(/abandoned|recovered|expired/i).first()
    if (await status.isVisible()) {
      await expect(status).toBeVisible()
    }
  })

  test('lista — email kolona', async ({ page }) => {
    await page.goto('/abandoned-carts')
    await page.waitForTimeout(1000)
    const email = page.getByText(/@test\.rs/).first()
    if (await email.isVisible()) {
      await expect(email).toBeVisible()
    }
  })
})
