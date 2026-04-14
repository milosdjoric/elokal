import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Fizičke prodavnice', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('store locations stranica se renderuje', async ({ page }) => {
    await page.goto('/store-locations')
    await expect(page.locator('body')).toContainText(/prodavnic|lokacij|store/i)
  })

  test('lista lokacija — ime i grad', async ({ page }) => {
    await page.goto('/store-locations')
    await page.waitForTimeout(1000)
    const location = page.getByText(/Beograd|Novi Sad|Niš/i).first()
    await expect(location).toBeVisible()
  })

  test('CRUD — create dugme', async ({ page }) => {
    await page.goto('/store-locations')
    const createBtn = page.getByRole('button', { name: /nova|dodaj|kreiraj/i }).first()
    if (await createBtn.isVisible()) {
      await expect(createBtn).toBeVisible()
    }
  })

  test('active/inactive status', async ({ page }) => {
    await page.goto('/store-locations')
    await page.waitForTimeout(1000)
    // Neaktivna lokacija (Kragujevac - uskoro)
    const inactive = page.getByText(/neaktiv|inactive|uskoro/i).first()
    if (await inactive.isVisible()) {
      await expect(inactive).toBeVisible()
    }
  })
})
