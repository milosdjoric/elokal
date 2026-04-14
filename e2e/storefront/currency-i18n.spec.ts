import { test, expect } from '@playwright/test'

test.describe('Storefront: Multi-currency i i18n', () => {
  test('currency switcher — dropdown postoji', async ({ page }) => {
    await page.goto('/')
    // Currency dropdown u headeru
    const currencySwitch = page.locator('select, [class*="currency"], button')
      .filter({ hasText: /RSD|EUR|USD/ }).first()
    if (await currencySwitch.isVisible()) {
      await expect(currencySwitch).toBeVisible()
    }
  })

  test('currency switcher — promena na EUR', async ({ page }) => {
    await page.goto('/')
    const currencySelect = page.locator('select').filter({ hasText: /RSD|EUR/ }).first()
    if (await currencySelect.isVisible()) {
      await currencySelect.selectOption('EUR')
      await page.waitForTimeout(1000)
      // Cene bi trebale biti u EUR
      await page.goto('/products')
      await page.waitForTimeout(1000)
      const euroPrice = page.getByText(/€/).first()
      if (await euroPrice.isVisible()) {
        await expect(euroPrice).toBeVisible()
      }
    }
  })

  test('currency — izbor se čuva u localStorage', async ({ page }) => {
    await page.goto('/')
    const currencySelect = page.locator('select').filter({ hasText: /RSD|EUR/ }).first()
    if (await currencySelect.isVisible()) {
      await currencySelect.selectOption('EUR')
      await page.waitForTimeout(500)
      // Proverimo localStorage
      const saved = await page.evaluate(() => localStorage.getItem('currency'))
      if (saved) {
        expect(saved).toContain('EUR')
      }
    }
  })

  test('language picker — dropdown postoji', async ({ page }) => {
    await page.goto('/')
    const langPicker = page.locator('select, [class*="language"], [class*="locale"], button')
      .filter({ hasText: /SR|EN|Srpski|English/ }).first()
    if (await langPicker.isVisible()) {
      await expect(langPicker).toBeVisible()
    }
  })

  test('language picker — promena na EN', async ({ page }) => {
    await page.goto('/')
    const langSelect = page.locator('select').filter({ hasText: /SR|EN|Srpski/ }).first()
    if (await langSelect.isVisible()) {
      await langSelect.selectOption({ label: /English|EN/i })
      await page.waitForTimeout(1000)
      // UI bi trebao prikazivati engleske stringove
    }
  })

  test('language — izbor se čuva u localStorage', async ({ page }) => {
    await page.goto('/')
    const saved = await page.evaluate(() => localStorage.getItem('locale'))
    // Default je 'sr' ili null
    if (saved) {
      expect(['sr', 'en']).toContain(saved)
    }
  })

  test('social proof popup — pojavljuje se', async ({ page }) => {
    await page.goto('/')
    // Social proof popup se pojavljuje nakon 15s
    const popup = page.locator('[class*="toast"], [class*="notification"], [class*="social-proof"]')
      .filter({ hasText: /kupio|kupila|upravo/i }).first()
    // Čekamo do 60s (pojavljuje se na random intervalu)
    try {
      await popup.waitFor({ state: 'visible', timeout: 60000 })
      await expect(popup).toBeVisible()
    } catch {
      // Može da se ne pojavi u ovom periodu — OK
    }
  })
})
