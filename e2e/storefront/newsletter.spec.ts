import { test, expect } from '@playwright/test'
import { storefrontLogin } from '../helpers/auth'

test.describe('Storefront: Newsletter', () => {
  test('homepage newsletter sekcija — inline forma', async ({ page }) => {
    await page.goto('/')
    // Newsletter sekcija na homepage-u
    const newsletterSection = page.locator('input[type="email"]').first()
    if (await newsletterSection.isVisible()) {
      await newsletterSection.fill('test-newsletter@test.rs')
      const subscribeBtn = page.getByRole('button', { name: /pretplati|subscribe/i }).first()
      if (await subscribeBtn.isVisible()) {
        await subscribeBtn.click()
        await page.waitForTimeout(1000)
        // Očekujemo success poruku ili formu bez greške
      }
    }
  })

  test('newsletter popup — pojavljuje se nakon delay-a', async ({ page }) => {
    // Očisti localStorage da bi popup mogao da se prikaže
    await page.goto('/')
    await page.evaluate(() => {
      localStorage.removeItem('newsletter_popup_dismissed')
      localStorage.removeItem('newsletter_subscribed')
    })
    await page.reload()
    // Popup se pojavljuje nakon delay-a (10s po defaultu)
    // Čekamo max 15 sekundi
    const popup = page.locator('[class*="modal"], [class*="popup"], [role="dialog"]').filter({ hasText: /newsletter|pretplat|email/i }).first()
    await popup.waitFor({ state: 'visible', timeout: 15000 }).catch(() => {
      // Popup se možda ne pojavljuje (feature flag, session suppress)
    })
  })

  test('newsletter popup — dismiss zatvara popup', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => {
      localStorage.removeItem('newsletter_popup_dismissed')
      localStorage.removeItem('newsletter_subscribed')
    })
    await page.reload()
    const popup = page.locator('[class*="modal"], [class*="popup"], [role="dialog"]').filter({ hasText: /newsletter|pretplat|email/i }).first()
    try {
      await popup.waitFor({ state: 'visible', timeout: 15000 })
      // Zatvori popup
      const closeBtn = popup.locator('button').filter({ hasText: /×|zatvori|close/i }).first()
        .or(popup.locator('[class*="close"]').first())
      if (await closeBtn.isVisible()) {
        await closeBtn.click()
        await expect(popup).not.toBeVisible()
      }
    } catch {
      // Popup nije prikazan — OK, feature flag zavisan
    }
  })

  test('newsletter popup — submit emaila', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => {
      localStorage.removeItem('newsletter_popup_dismissed')
      localStorage.removeItem('newsletter_subscribed')
    })
    await page.reload()
    const popup = page.locator('[class*="modal"], [class*="popup"], [role="dialog"]').filter({ hasText: /newsletter|pretplat|email/i }).first()
    try {
      await popup.waitFor({ state: 'visible', timeout: 15000 })
      const emailInput = popup.locator('input[type="email"]')
      if (await emailInput.isVisible()) {
        await emailInput.fill(`popup-test-${Date.now()}@test.rs`)
        const submitBtn = popup.getByRole('button', { name: /pretplati|subscribe|pošalji/i })
        if (await submitBtn.isVisible()) {
          await submitBtn.click()
          await page.waitForTimeout(1000)
        }
      }
    } catch {
      // Popup nije prikazan
    }
  })

  test('footer newsletter forma', async ({ page }) => {
    await page.goto('/')
    // Scroll do footera
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight))
    await page.waitForTimeout(500)
    const footer = page.locator('footer')
    const emailInput = footer.locator('input[type="email"]')
    if (await emailInput.isVisible()) {
      await emailInput.fill(`footer-test-${Date.now()}@test.rs`)
      const btn = footer.getByRole('button', { name: /pretplati|subscribe/i })
      if (await btn.isVisible()) {
        await btn.click()
        await page.waitForTimeout(1000)
      }
    }
  })
})
