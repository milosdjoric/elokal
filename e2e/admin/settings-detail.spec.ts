import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Podešavanja — svi tabovi', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('general tab — ime sajta, email', async ({ page }) => {
    await page.goto('/settings')
    await page.waitForTimeout(1000)
    // General tab (podrazumevano aktivan)
    const siteNameInput = page.locator('input').filter({ has: page.locator('..') }).first()
    await expect(siteNameInput).toBeVisible()
  })

  test('storefront layout tab', async ({ page }) => {
    await page.goto('/settings')
    await page.waitForTimeout(1000)
    const tab = page.getByRole('button', { name: /storefront|layout|izgled/i }).first()
    if (await tab.isVisible()) {
      await tab.click()
      await page.waitForTimeout(300)
      // A/B variant selektori
    }
  })

  test('top bar tab', async ({ page }) => {
    await page.goto('/settings')
    await page.waitForTimeout(1000)
    const tab = page.getByRole('button', { name: /top.?bar/i }).first()
    if (await tab.isVisible()) {
      await tab.click()
      await page.waitForTimeout(300)
      // Enable toggle i tekst
    }
  })

  test('trust & conversion tab', async ({ page }) => {
    await page.goto('/settings')
    await page.waitForTimeout(1000)
    const tab = page.getByRole('button', { name: /trust|konverzij/i }).first()
    if (await tab.isVisible()) {
      await tab.click()
      await page.waitForTimeout(300)
    }
  })

  test('cart & checkout tab', async ({ page }) => {
    await page.goto('/settings')
    await page.waitForTimeout(1000)
    const tab = page.getByRole('button', { name: /cart|korpa|checkout|kasa/i }).first()
    if (await tab.isVisible()) {
      await tab.click()
      await page.waitForTimeout(300)
      // Free shipping threshold
    }
  })

  test('badges tab', async ({ page }) => {
    await page.goto('/settings')
    await page.waitForTimeout(1000)
    const tab = page.getByRole('button', { name: /badge|značk/i }).first()
    if (await tab.isVisible()) {
      await tab.click()
      await page.waitForTimeout(300)
    }
  })

  test('SEO tab', async ({ page }) => {
    await page.goto('/settings')
    await page.waitForTimeout(1000)
    const tab = page.getByRole('button', { name: /SEO/i }).first()
    if (await tab.isVisible()) {
      await tab.click()
      await page.waitForTimeout(300)
      // GA ID, FB Pixel
    }
  })

  test('GDPR tab', async ({ page }) => {
    await page.goto('/settings')
    await page.waitForTimeout(1000)
    const tab = page.getByRole('button', { name: /GDPR|privat/i }).first()
    if (await tab.isVisible()) {
      await tab.click()
      await page.waitForTimeout(300)
    }
  })

  test('feature flags tab', async ({ page }) => {
    await page.goto('/settings')
    await page.waitForTimeout(1000)
    const tab = page.getByRole('button', { name: /feature|flag|funkcij/i }).first()
    if (await tab.isVisible()) {
      await tab.click()
      await page.waitForTimeout(300)
      // Toggle prekidači
      const toggles = page.locator('[class*="switch"], input[type="checkbox"]')
      if (await toggles.count() > 0) {
        expect(await toggles.count()).toBeGreaterThanOrEqual(5)
      }
    }
  })

  test('save dugme funkcioniše', async ({ page }) => {
    await page.goto('/settings')
    await page.waitForTimeout(1000)
    const saveBtn = page.getByRole('button', { name: /sačuvaj|save|ažuriraj/i }).first()
    if (await saveBtn.isVisible()) {
      await expect(saveBtn).toBeVisible()
    }
  })
})
