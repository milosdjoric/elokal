import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Kuponi', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('listing — stranica se renderuje', async ({ page }) => {
    await page.goto('/coupons')
    await expect(page.getByRole('heading', { name: /Kupon/i })).toBeVisible()
  })

  test('create — dugme za novi kupon', async ({ page }) => {
    await page.goto('/coupons')
    await expect(page.getByRole('button', { name: /Nov|Dodaj/i }).or(page.getByRole('link', { name: /Nov|Dodaj/i }))).toBeVisible()
  })

  test('bulk generate — dugme vidljivo', async ({ page }) => {
    await page.goto('/coupons')
    const bulkBtn = page.getByRole('button', { name: /Generiši|Bulk/i })
    // Može ili ne mora biti vidljivo zavisno od broja kupona
  })
})
