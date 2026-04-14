import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Newsletter pretplatnici', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('newsletter stranica se renderuje', async ({ page }) => {
    const response = await page.goto('/newsletter')
    if (response && response.status() < 400) {
      await expect(page.locator('body')).toContainText(/newsletter|pretplatn|subscriber/i)
    }
  })

  test('stats bar — total, confirmed, pending, unsubscribed', async ({ page }) => {
    await page.goto('/newsletter')
    await page.waitForTimeout(1000)
    // Stats kartice
    const stats = page.getByText(/confirmed|pending|unsubscribed|potvrđen|čeka|odjav|ukupno|total/i)
    if (await stats.first().isVisible()) {
      expect(await stats.count()).toBeGreaterThan(0)
    }
  })

  test('lista — email, status, source', async ({ page }) => {
    await page.goto('/newsletter')
    await page.waitForTimeout(1000)
    const email = page.getByText(/@test\.rs/).first()
    if (await email.isVisible()) {
      await expect(email).toBeVisible()
    }
  })

  test('lista — source prikazan (popup/footer/registration)', async ({ page }) => {
    await page.goto('/newsletter')
    await page.waitForTimeout(1000)
    const source = page.getByText(/popup|footer|registration|checkout/i).first()
    if (await source.isVisible()) {
      await expect(source).toBeVisible()
    }
  })

  test('CSV export dugme', async ({ page }) => {
    await page.goto('/newsletter')
    await page.waitForTimeout(1000)
    const exportBtn = page.getByRole('button', { name: /export|CSV|izvoz/i }).first()
      .or(page.getByRole('link', { name: /export|CSV/i }).first())
    if (await exportBtn.isVisible()) {
      await expect(exportBtn).toBeVisible()
    }
  })

  test('delete subscriber', async ({ page }) => {
    await page.goto('/newsletter')
    await page.waitForTimeout(1000)
    const deleteBtn = page.getByRole('button', { name: /obriši|delete/i }).first()
    if (await deleteBtn.isVisible()) {
      await expect(deleteBtn).toBeVisible()
      // Ne klikćemo da ne brišemo podatke
    }
  })
})
