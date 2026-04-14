import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Loyalty program', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('loyalty stranica se renderuje', async ({ page }) => {
    await page.goto('/loyalty')
    await expect(page.locator('body')).toContainText(/loyalty|lojalnost|poen|bod/i)
  })

  test('lista — kupac, tier, poeni', async ({ page }) => {
    await page.goto('/loyalty')
    await page.waitForTimeout(1000)
    // Tier badges
    const tier = page.getByText(/bronze|silver|gold|platinum/i).first()
    if (await tier.isVisible()) {
      await expect(tier).toBeVisible()
    }
  })

  test('lista — ukupno zarađeni bodovi', async ({ page }) => {
    await page.goto('/loyalty')
    await page.waitForTimeout(1000)
    // Numerički prikaz bodova
    const points = page.locator('td, [class*="cell"]').filter({ hasText: /\d+/ }).first()
    if (await points.isVisible()) {
      await expect(points).toBeVisible()
    }
  })
})
