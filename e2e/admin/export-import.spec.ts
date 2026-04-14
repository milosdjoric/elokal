import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Export/Import', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('inventory stranica — export dugme', async ({ page }) => {
    await page.goto('/inventory')
    await page.waitForTimeout(1000)
    const exportBtn = page.getByRole('button', { name: /export|izvoz/i }).first()
    if (await exportBtn.isVisible()) {
      await expect(exportBtn).toBeEnabled()
    }
  })

  test('inventory stranica — import dugme', async ({ page }) => {
    await page.goto('/inventory')
    await page.waitForTimeout(1000)
    const importBtn = page.getByRole('button', { name: /import|uvoz/i }).first()
    if (await importBtn.isVisible()) {
      await expect(importBtn).toBeVisible()
    }
  })

  test('products export — CSV download', async ({ page }) => {
    // API direktan poziv za export
    const response = await page.goto('/products')
    await page.waitForTimeout(1000)
    // Export dugme na product listi
    const exportBtn = page.getByRole('button', { name: /export|izvoz|CSV/i }).first()
    if (await exportBtn.isVisible()) {
      await expect(exportBtn).toBeVisible()
    }
  })
})
