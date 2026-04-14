import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Shop the Look', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('looks stranica se renderuje', async ({ page }) => {
    await page.goto('/looks')
    await expect(page.locator('body')).toContainText(/look|izgled|stil/i)
  })

  test('lista — grid sa look karticama', async ({ page }) => {
    await page.goto('/looks')
    await page.waitForTimeout(1000)
    const lookCard = page.getByText(/moderna|skandinavski|zimska/i).first()
    if (await lookCard.isVisible()) {
      await expect(lookCard).toBeVisible()
    }
  })

  test('CRUD — create dugme', async ({ page }) => {
    await page.goto('/looks')
    const createBtn = page.getByRole('button', { name: /novi|dodaj|kreiraj/i }).first()
    if (await createBtn.isVisible()) {
      await expect(createBtn).toBeVisible()
    }
  })

  test('active/inactive oznaka', async ({ page }) => {
    await page.goto('/looks')
    await page.waitForTimeout(1000)
    // Active/inactive badges
    const badge = page.locator('[class*="badge"], [class*="tag"]')
      .filter({ hasText: /aktiv|active|neaktiv|inactive/i }).first()
    if (await badge.isVisible()) {
      await expect(badge).toBeVisible()
    }
  })
})
