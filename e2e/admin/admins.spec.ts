import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Admin korisnici', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('admins listing se renderuje', async ({ page }) => {
    await page.goto('/admins')
    await expect(page.getByRole('heading', { name: /Admin/i })).toBeVisible()
  })

  test('create — dugme za novog admina', async ({ page }) => {
    await page.goto('/admins')
    await expect(page.getByRole('button', { name: /Nov|Dodaj/i })).toBeVisible()
  })

  test('listing — tabela sa adminima', async ({ page }) => {
    await page.goto('/admins')
    await page.waitForTimeout(1000)
    // Admin email treba da bude vidljiv
    await expect(page.getByText('admin@webshop.test')).toBeVisible()
  })
})
