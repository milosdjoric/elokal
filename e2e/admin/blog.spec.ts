import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Blog', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('blog listing se renderuje', async ({ page }) => {
    await page.goto('/blog')
    await expect(page.getByRole('heading', { name: /Blog/i })).toBeVisible()
  })

  test('create — dugme za novi post', async ({ page }) => {
    await page.goto('/blog')
    await expect(page.getByRole('button', { name: /Nov|Dodaj/i }).or(page.getByRole('link', { name: /Nov|Dodaj/i }))).toBeVisible()
  })

  test('create — navigacija na formu', async ({ page }) => {
    await page.goto('/blog')
    const createBtn = page.getByRole('button', { name: /Nov|Dodaj/i }).or(page.getByRole('link', { name: /Nov|Dodaj/i }))
    if (await createBtn.isVisible()) {
      await createBtn.click()
      await expect(page).toHaveURL(/\/blog\/create/)
    }
  })

  test('edit — klik na post otvara edit', async ({ page }) => {
    await page.goto('/blog')
    const editLink = page.locator('a[href*="/blog/"][href*="/edit"]').first()
    if (await editLink.isVisible()) {
      await editLink.click()
      await expect(page).toHaveURL(/\/blog\/\d+\/edit/)
    }
  })
})
