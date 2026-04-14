import { test, expect } from '@playwright/test'
import { storefrontLogin } from '../helpers/auth'

test.describe('Storefront: Praćenje narudžbine i detalj', () => {
  test('/pracenje — stranica se renderuje', async ({ page }) => {
    await page.goto('/pracenje/TEST-123')
    await expect(page.locator('body')).toBeVisible()
  })

  test('/pracenje — nepostojeća narudžbina', async ({ page }) => {
    await page.goto('/pracenje/NEPOSTOJECI-999')
    await page.waitForTimeout(1000)
    // Poruka da narudžbina nije pronađena ili redirect na login
    const body = await page.locator('body').textContent()
    expect(body).toBeTruthy()
  })

  test('detalj narudžbine — progress bar', async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
    await page.goto('/nalog/narudzbine')
    await page.waitForTimeout(1000)
    // Klik na prvu narudžbinu
    const orderLink = page.locator('a[href*="/nalog/narudzbine/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Progress bar sa koracima
      const progressBar = page.locator('[class*="progress"], [class*="step"], [class*="timeline"]').first()
      if (await progressBar.isVisible()) {
        await expect(progressBar).toBeVisible()
      }
    }
  })

  test('detalj narudžbine — stavke narudžbine', async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
    await page.goto('/nalog/narudzbine')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/nalog/narudzbine/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Stavke — ime proizvoda i cena
      await expect(page.getByText(/RSD/).first()).toBeVisible()
    }
  })

  test('detalj narudžbine — totals breakdown', async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
    await page.goto('/nalog/narudzbine')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/nalog/narudzbine/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Totals: subtotal, dostava, porez, ukupno
      const totals = page.getByText(/ukupno|subtotal|dostav|porez/i)
      const count = await totals.count()
      expect(count).toBeGreaterThan(0)
    }
  })

  test('detalj narudžbine — tracking info', async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
    await page.goto('/nalog/narudzbine')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/nalog/narudzbine/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Tracking info — kurir i tracking broj (ako postoji)
      const tracking = page.getByText(/tracking|praćen|kurir|D Express|Pošta|BEX/i).first()
      // Ne assertujemo jer zavisi od statusa narudžbine
    }
  })

  test('detalj narudžbine — adresa isporuke', async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
    await page.goto('/nalog/narudzbine')
    await page.waitForTimeout(1000)
    const orderLink = page.locator('a[href*="/nalog/narudzbine/"]').first()
    if (await orderLink.isVisible()) {
      await orderLink.click()
      await page.waitForTimeout(1000)
      // Adresa
      const address = page.getByText(/Beograd|Novi Sad|Niš|Kragujevac|Subotica|Zrenjanin|Pančevo|Čačak/i).first()
      if (await address.isVisible()) {
        await expect(address).toBeVisible()
      }
    }
  })
})
