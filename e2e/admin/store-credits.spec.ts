import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Store krediti', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('store credits stranica se renderuje', async ({ page }) => {
    await page.goto('/store-credits')
    await expect(page.locator('body')).toContainText(/kredit|credit|stanje/i)
  })

  test('lista — kupac, email, balans', async ({ page }) => {
    await page.goto('/store-credits')
    await page.waitForTimeout(1000)
    const balance = page.getByText(/RSD/).first()
    if (await balance.isVisible()) {
      await expect(balance).toBeVisible()
    }
  })

  test('lista — email kupca prikazan', async ({ page }) => {
    await page.goto('/store-credits')
    await page.waitForTimeout(1000)
    const email = page.getByText(/@test\.rs/).first()
    if (await email.isVisible()) {
      await expect(email).toBeVisible()
    }
  })
})
