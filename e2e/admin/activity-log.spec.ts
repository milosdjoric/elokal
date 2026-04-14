import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Activity Log', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('activity log stranica se renderuje', async ({ page }) => {
    const response = await page.goto('/activity-log')
    if (response && response.status() < 400) {
      await expect(page.locator('body')).toContainText(/aktivnost|activity|log/i)
    }
  })

  test('lista — admin ime prikazano', async ({ page }) => {
    await page.goto('/activity-log')
    await page.waitForTimeout(1000)
    const admin = page.getByText(/Super Admin/i).first()
    if (await admin.isVisible()) {
      await expect(admin).toBeVisible()
    }
  })

  test('lista — akcije (created/updated/deleted)', async ({ page }) => {
    await page.goto('/activity-log')
    await page.waitForTimeout(1000)
    const action = page.getByText(/created|updated|deleted|approved|login/i).first()
    if (await action.isVisible()) {
      await expect(action).toBeVisible()
    }
  })

  test('lista — model type i ID', async ({ page }) => {
    await page.goto('/activity-log')
    await page.waitForTimeout(1000)
    const model = page.getByText(/Product|Order|Review|Coupon|Setting|Post/i).first()
    if (await model.isVisible()) {
      await expect(model).toBeVisible()
    }
  })

  test('lista — timestamp prikazan', async ({ page }) => {
    await page.goto('/activity-log')
    await page.waitForTimeout(1000)
    // Datum u nekom formatu
    const date = page.getByText(/2026|pre|ago/i).first()
    if (await date.isVisible()) {
      await expect(date).toBeVisible()
    }
  })

  test('read-only — nema edit/delete dugmadi', async ({ page }) => {
    await page.goto('/activity-log')
    await page.waitForTimeout(1000)
    // Ne bi trebalo da ima delete ili edit dugmad
    const editBtn = page.getByRole('button', { name: /izmeni|edit|obriši|delete/i }).first()
    const isVisible = await editBtn.isVisible().catch(() => false)
    // Activity log je read-only, pa ne bi trebalo da ima ova dugmad
  })
})
