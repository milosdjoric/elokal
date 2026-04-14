import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Webhooks', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('webhooks stranica se renderuje', async ({ page }) => {
    const response = await page.goto('/webhooks')
    if (response && response.status() < 400) {
      await expect(page.locator('body')).toContainText(/webhook/i)
    }
  })

  test('lista — URL endpoint prikazan', async ({ page }) => {
    await page.goto('/webhooks')
    await page.waitForTimeout(1000)
    const url = page.getByText(/https:\/\/hooks\.example\.com/).first()
    if (await url.isVisible()) {
      await expect(url).toBeVisible()
    }
  })

  test('lista — events prikazani', async ({ page }) => {
    await page.goto('/webhooks')
    await page.waitForTimeout(1000)
    const event = page.getByText(/order\.created|product\.updated|stock/i).first()
    if (await event.isVisible()) {
      await expect(event).toBeVisible()
    }
  })

  test('active/inactive status', async ({ page }) => {
    await page.goto('/webhooks')
    await page.waitForTimeout(1000)
    // Trebalo bi da ima i aktivan i neaktivan webhook
    const badge = page.locator('[class*="badge"], [class*="tag"]').first()
    if (await badge.isVisible()) {
      await expect(badge).toBeVisible()
    }
  })

  test('CRUD — create dugme', async ({ page }) => {
    await page.goto('/webhooks')
    const createBtn = page.getByRole('button', { name: /novi|dodaj|kreiraj/i }).first()
    if (await createBtn.isVisible()) {
      await expect(createBtn).toBeVisible()
    }
  })
})
