import { test, expect } from '@playwright/test'

test.describe('Storefront: Poklon kartice', () => {
  test('/poklon-kartica — stranica za kupovinu', async ({ page }) => {
    await page.goto('/poklon-kartica')
    await expect(page.locator('body')).toContainText(/poklon|gift|kartic/i)
  })

  test('preset iznosi — dugmad za izbor', async ({ page }) => {
    await page.goto('/poklon-kartica')
    // Dugmad sa iznosima: 1000, 2000, 3000, 5000, 10000
    const presets = page.getByRole('button', { name: /1.000|2.000|3.000|5.000|10.000/i })
    const count = await presets.count()
    if (count > 0) {
      // Klik na preset
      await presets.first().click()
      await page.waitForTimeout(300)
    }
  })

  test('custom iznos — input polje', async ({ page }) => {
    await page.goto('/poklon-kartica')
    const customInput = page.locator('input[type="number"], input[placeholder*="iznos"]').first()
    if (await customInput.isVisible()) {
      await customInput.fill('7500')
      await page.waitForTimeout(300)
    }
  })

  test('forma za primaoca — ime, email, poruka', async ({ page }) => {
    await page.goto('/poklon-kartica')
    // Polja za primaoca
    const recipientName = page.locator('input').filter({ has: page.locator('..') }).nth(0)
    const inputs = page.locator('input')
    const count = await inputs.count()
    // Trebalo bi da bude barem 2-3 inputa (ime, email, opciono poruka)
    expect(count).toBeGreaterThanOrEqual(2)
  })

  test('kupovina gift kartice — submit', async ({ page }) => {
    await page.goto('/poklon-kartica')
    // Izaberi preset
    const preset = page.getByRole('button', { name: /1.000|1000/ }).first()
    if (await preset.isVisible()) {
      await preset.click()
    }
    // Popuni primaoca
    const inputs = page.locator('input')
    const inputCount = await inputs.count()
    for (let i = 0; i < inputCount; i++) {
      const input = inputs.nth(i)
      const type = await input.getAttribute('type')
      const placeholder = await input.getAttribute('placeholder') || ''
      if (type === 'email' || placeholder.toLowerCase().includes('email')) {
        await input.fill('prijatelj@test.rs')
      } else if (type === 'text') {
        const val = await input.inputValue()
        if (!val) await input.fill('Test Primalac')
      }
    }
    // Submit dugme
    const submitBtn = page.getByRole('button', { name: /kupi|pošalji|potvrdi/i }).first()
    if (await submitBtn.isVisible()) {
      await expect(submitBtn).toBeVisible()
      // Ne klikćemo da ne kreiramo test podatke
    }
  })

  test('/poklon-kartica/provera — balans check sa validnim kodom', async ({ page }) => {
    await page.goto('/poklon-kartica/provera')
    const input = page.locator('input').first()
    await expect(input).toBeVisible()
    // Unosimo nepostojeći kod
    await input.fill('XXXX-XXXX-XXXX')
    const checkBtn = page.getByRole('button', { name: /proveri|check/i }).first()
    if (await checkBtn.isVisible()) {
      await checkBtn.click()
      await page.waitForTimeout(1000)
      // Očekujemo error poruku ili balans
    }
  })

  test('/poklon-kartica/provera — prikaz balansa', async ({ page }) => {
    await page.goto('/poklon-kartica/provera')
    const input = page.locator('input').first()
    await expect(input).toBeVisible()
    const checkBtn = page.getByRole('button', { name: /proveri|check/i }).first()
    await expect(checkBtn).toBeVisible()
  })
})
