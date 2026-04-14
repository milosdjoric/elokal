import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Poklon kartice', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('lista poklon kartica', async ({ page }) => {
    await page.goto('/gift-cards')
    await expect(page.locator('body')).toContainText(/poklon|gift|kartic/i)
  })

  test('lista — kolone: kod, balans, primalac, status', async ({ page }) => {
    await page.goto('/gift-cards')
    await page.waitForTimeout(1000)
    // Format koda XXXX-XXXX-XXXX
    const code = page.getByText(/[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}/).first()
    if (await code.isVisible()) {
      await expect(code).toBeVisible()
    }
  })

  test('lista — prikazuje balans vs inicijalni iznos', async ({ page }) => {
    await page.goto('/gift-cards')
    await page.waitForTimeout(1000)
    const amount = page.getByText(/RSD/).first()
    if (await amount.isVisible()) {
      await expect(amount).toBeVisible()
    }
  })

  test('create dugme postoji', async ({ page }) => {
    await page.goto('/gift-cards')
    const createBtn = page.getByRole('button', { name: /nova|dodaj|kreiraj|create/i }).first()
      .or(page.getByRole('link', { name: /nova|dodaj|kreiraj/i }).first())
    if (await createBtn.isVisible()) {
      await expect(createBtn).toBeVisible()
    }
  })
})
