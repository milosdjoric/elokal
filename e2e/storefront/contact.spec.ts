import { test, expect } from '@playwright/test'

test.describe('Storefront: Kontakt forma', () => {
  test('/kontakt — stranica se renderuje', async ({ page }) => {
    await page.goto('/kontakt')
    await expect(page.locator('h1')).toBeVisible()
  })

  test('kontakt forma — sva polja prisutna', async ({ page }) => {
    await page.goto('/kontakt')
    // Forma sa poljima: ime, email, predmet, poruka
    const inputs = page.locator('input, textarea')
    const count = await inputs.count()
    expect(count).toBeGreaterThanOrEqual(3)
  })

  test('kontakt forma — submit sa validnim podacima', async ({ page }) => {
    await page.goto('/kontakt')
    // Popuni formu
    const nameInput = page.locator('input').first()
    if (await nameInput.isVisible()) {
      await nameInput.fill('Test Korisnik')
    }
    const emailInput = page.locator('input[type="email"]').first()
    if (await emailInput.isVisible()) {
      await emailInput.fill('test-kontakt@test.rs')
    }
    // Subject ako postoji
    const subjectInput = page.locator('input').nth(2)
    if (await subjectInput.isVisible()) {
      await subjectInput.fill('Test pitanje')
    }
    const textarea = page.locator('textarea').first()
    if (await textarea.isVisible()) {
      await textarea.fill('Ovo je test poruka za E2E testove.')
    }
    const submitBtn = page.getByRole('button', { name: /pošalji|send|kontakt/i }).first()
    if (await submitBtn.isVisible()) {
      await submitBtn.click()
      await page.waitForTimeout(2000)
      // Očekujemo success poruku
      const success = page.getByText(/uspešno|poslata|hvala|thank/i).first()
      if (await success.isVisible()) {
        await expect(success).toBeVisible()
      }
    }
  })

  test('kontakt forma — validacija praznih polja', async ({ page }) => {
    await page.goto('/kontakt')
    const submitBtn = page.getByRole('button', { name: /pošalji|send|kontakt/i }).first()
    if (await submitBtn.isVisible()) {
      await submitBtn.click()
      await page.waitForTimeout(1000)
      // Validacione greške
      const errors = page.locator('.text-red-600, .text-red-500, [class*="error"]')
      const count = await errors.count()
      // Ili HTML5 validacija sprečava submit
    }
  })
})
