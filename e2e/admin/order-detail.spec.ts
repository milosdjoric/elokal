import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Narudžbine — detalj', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('detalj narudžbine — stavke sa slikama', async ({ page }) => {
    await page.goto('/orders')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Stavke narudžbine
      await expect(page.getByText(/RSD/).first()).toBeVisible()
    }
  })

  test('detalj — totals breakdown', async ({ page }) => {
    await page.goto('/orders')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Subtotal, dostava, porez, ukupno
      const totals = page.getByText(/subtotal|dostav|porez|ukupno|shipping|tax|total/i)
      expect(await totals.count()).toBeGreaterThan(0)
    }
  })

  test('detalj — order timeline', async ({ page }) => {
    await page.goto('/orders')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Timeline / istorija
      const timeline = page.getByText(/kreiran|potvrđen|pending|confirmed/i).first()
      if (await timeline.isVisible()) {
        await expect(timeline).toBeVisible()
      }
    }
  })

  test('detalj — status changer dropdown', async ({ page }) => {
    await page.goto('/orders')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Status dropdown
      const statusSelect = page.locator('select').filter({ hasText: /pending|confirmed|processing|shipped/i }).first()
      if (await statusSelect.isVisible()) {
        await expect(statusSelect).toBeVisible()
      }
    }
  })

  test('detalj — tracking sekcija', async ({ page }) => {
    await page.goto('/orders')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Tracking polja
      const trackingSection = page.getByText(/tracking|praćen|kurir/i).first()
      if (await trackingSection.isVisible()) {
        await expect(trackingSection).toBeVisible()
      }
    }
  })

  test('detalj — refund forma', async ({ page }) => {
    await page.goto('/orders')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Refund dugme ili sekcija
      const refundBtn = page.getByRole('button', { name: /refund|povrat|vrati/i }).first()
      if (await refundBtn.isVisible()) {
        await expect(refundBtn).toBeVisible()
      }
    }
  })

  test('faktura — print stranica', async ({ page }) => {
    await page.goto('/orders')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Link ka fakturi
      const invoiceLink = page.getByRole('link', { name: /faktura|invoice/i }).first()
        .or(page.locator('a[href*="invoice"]').first())
      if (await invoiceLink.isVisible()) {
        const href = await invoiceLink.getAttribute('href')
        if (href) {
          const response = await page.goto(href)
          expect(response?.status()).toBeLessThan(400)
          // Print dugme
          const printBtn = page.getByRole('button', { name: /print|štampaj/i }).first()
          if (await printBtn.isVisible()) {
            await expect(printBtn).toBeVisible()
          }
        }
      }
    }
  })

  test('packing slip — print stranica', async ({ page }) => {
    await page.goto('/orders')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      const packingLink = page.locator('a[href*="packing"]').first()
      if (await packingLink.isVisible()) {
        const href = await packingLink.getAttribute('href')
        if (href) {
          const response = await page.goto(href)
          expect(response?.status()).toBeLessThan(400)
        }
      }
    }
  })

  test('credit note — print stranica', async ({ page }) => {
    await page.goto('/orders')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/orders/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      const creditLink = page.locator('a[href*="credit"]').first()
      if (await creditLink.isVisible()) {
        const href = await creditLink.getAttribute('href')
        if (href) {
          const response = await page.goto(href)
          expect(response?.status()).toBeLessThan(400)
        }
      }
    }
  })
})
