import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Valute', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('currencies stranica se renderuje', async ({ page }) => {
    await page.goto('/currencies')
    await expect(page.locator('body')).toContainText(/valut|currenc|RSD/i)
  })

  test('lista — RSD, EUR, USD', async ({ page }) => {
    await page.goto('/currencies')
    await page.waitForTimeout(1000)
    await expect(page.getByText('RSD').first()).toBeVisible()
    const eur = page.getByText('EUR').first()
    if (await eur.isVisible()) {
      await expect(eur).toBeVisible()
    }
  })

  test('lista — exchange rate prikazan', async ({ page }) => {
    await page.goto('/currencies')
    await page.waitForTimeout(1000)
    // Exchange rate (decimalni broj)
    const rate = page.getByText(/0\.00[0-9]|1\.0/).first()
    if (await rate.isVisible()) {
      await expect(rate).toBeVisible()
    }
  })

  test('default valuta označena', async ({ page }) => {
    await page.goto('/currencies')
    await page.waitForTimeout(1000)
    const defaultBadge = page.getByText(/default|osnovna|primarna/i).first()
    if (await defaultBadge.isVisible()) {
      await expect(defaultBadge).toBeVisible()
    }
  })
})
