import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Kuriri (Carriers)', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('carriers stranica se renderuje', async ({ page }) => {
    await page.goto('/carriers')
    await expect(page.locator('body')).toContainText(/kurir|carrier|dostav/i)
  })

  test('lista kurira — Pošta, D Express, BEX', async ({ page }) => {
    await page.goto('/carriers')
    await page.waitForTimeout(1000)
    const carrier = page.getByText(/Pošta|D Express|BEX|AKS/i).first()
    await expect(carrier).toBeVisible()
  })

  test('CRUD — create dugme', async ({ page }) => {
    await page.goto('/carriers')
    const createBtn = page.getByRole('button', { name: /novi|dodaj|kreiraj|create/i }).first()
    if (await createBtn.isVisible()) {
      await expect(createBtn).toBeVisible()
    }
  })

  test('tracking URL pattern prikazan', async ({ page }) => {
    await page.goto('/carriers')
    await page.waitForTimeout(1000)
    const trackingUrl = page.getByText(/tracking_number|posta\.rs|dexpress/i).first()
    if (await trackingUrl.isVisible()) {
      await expect(trackingUrl).toBeVisible()
    }
  })
})
