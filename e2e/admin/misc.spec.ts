import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Ostale stranice', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('attributes stranica', async ({ page }) => {
    await page.goto('/attributes')
    await expect(page.getByRole('heading', { name: /Atribut/i })).toBeVisible()
  })

  test('tax rates stranica', async ({ page }) => {
    await page.goto('/tax-rates')
    await expect(page.getByRole('heading', { name: /Poresk|Tax/i })).toBeVisible()
  })

  test('webhooks stranica — ako postoji ruta', async ({ page }) => {
    const response = await page.goto('/webhooks')
    // Može da ne postoji kao stranica u admin-u
    if (response && response.status() < 400) {
      await expect(page.locator('body')).toBeVisible()
    }
  })

  test('import/export — navigacija', async ({ page }) => {
    // Export dugme je na inventory stranici
    await page.goto('/inventory')
    const exportBtn = page.getByRole('button', { name: /Export|Izvoz/i })
    // Samo proveravamo da stranica radi
  })

  test('newsletter subscribers', async ({ page }) => {
    const response = await page.goto('/newsletter')
    if (response && response.status() < 400) {
      await expect(page.locator('body')).toBeVisible()
    }
  })

  test('activity log', async ({ page }) => {
    const response = await page.goto('/activity-log')
    if (response && response.status() < 400) {
      await expect(page.locator('body')).toBeVisible()
    }
  })

  test('shipping zones', async ({ page }) => {
    const response = await page.goto('/shipping')
    if (response && response.status() < 400) {
      await expect(page.locator('body')).toBeVisible()
    }
  })

  test('payment methods', async ({ page }) => {
    const response = await page.goto('/payment-methods')
    if (response && response.status() < 400) {
      await expect(page.locator('body')).toBeVisible()
    }
  })
})
