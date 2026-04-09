import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Kategorije', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('listing — tree prikaz kategorija', async ({ page }) => {
    await page.goto('/categories')
    await expect(page.getByRole('heading', { name: /Kategorije/i })).toBeVisible()
  })

  test('create — dugme za novu kategoriju', async ({ page }) => {
    await page.goto('/categories')
    await expect(page.getByRole('button', { name: /Nova|Dodaj/i })).toBeVisible()
  })

  test('delete — confirm modal', async ({ page }) => {
    await page.goto('/categories')
    const deleteBtn = page.getByText(/Obriši|obriši/i).first()
    if (await deleteBtn.isVisible()) {
      await deleteBtn.click()
      await expect(page.getByText(/sigurni/i)).toBeVisible()
    }
  })
})
