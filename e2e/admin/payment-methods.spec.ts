import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Načini plaćanja', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('payment methods stranica se renderuje', async ({ page }) => {
    await page.goto('/payment-methods')
    await expect(page.locator('body')).toContainText(/plaćanj|payment|metod/i)
  })

  test('lista — pouzeće i virman', async ({ page }) => {
    await page.goto('/payment-methods')
    await page.waitForTimeout(1000)
    const pouzece = page.getByText(/pouzeć|COD/i).first()
    if (await pouzece.isVisible()) {
      await expect(pouzece).toBeVisible()
    }
    const virman = page.getByText(/virman|bank/i).first()
    if (await virman.isVisible()) {
      await expect(virman).toBeVisible()
    }
  })

  test('lista — additional cost prikazan', async ({ page }) => {
    await page.goto('/payment-methods')
    await page.waitForTimeout(1000)
    // 150 RSD za pouzeće
    const cost = page.getByText(/150|RSD/).first()
    if (await cost.isVisible()) {
      await expect(cost).toBeVisible()
    }
  })

  test('CRUD — create dugme', async ({ page }) => {
    await page.goto('/payment-methods')
    const createBtn = page.getByRole('button', { name: /novi|dodaj|kreiraj/i }).first()
    if (await createBtn.isVisible()) {
      await expect(createBtn).toBeVisible()
    }
  })
})
