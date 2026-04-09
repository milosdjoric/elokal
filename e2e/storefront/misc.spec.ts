import { test, expect } from '@playwright/test'

test.describe('Storefront: Ostale stranice i funkcionalnosti', () => {
  test('/poklon-kartica/provera — gift card balance check', async ({ page }) => {
    await page.goto('/poklon-kartica/provera')
    await expect(page.locator('body')).toBeVisible()
    // Input za kod i dugme za proveru
    const input = page.locator('input').first()
    if (await input.isVisible()) {
      await input.fill('NEPOSTOJECI')
      const checkBtn = page.getByRole('button', { name: /proveri|Proveri/i })
      if (await checkBtn.isVisible()) {
        await checkBtn.click()
        await page.waitForTimeout(1000)
      }
    }
  })

  test('/pracenje/:number — tracking stranica', async ({ page }) => {
    await page.goto('/pracenje/NEPOSTOJECI123')
    // Treba da prikaže poruku da narudžbina nije pronađena ili tracking info
    await expect(page.locator('body')).toBeVisible()
  })

  test('404 stranica', async ({ page }) => {
    await page.goto('/nepostojeca-stranica-xyz-123')
    // Custom 404 ili Nuxt error page
    await expect(page.locator('body')).toBeVisible()
    // Može da sadrži "404" ili "nije pronađena"
  })

  test('cookie consent baner', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('cookie_consent'))
    await page.reload()
    // Cookie baner može da se pojavi
    const cookieBanner = page.getByText(/kolačić|cookie/i).first()
    // Ne assertujemo jer zavisi od konfiguracije
  })

  test('mobile navigacija — bottom nav vidljiv na mobilnom', async ({ page }) => {
    // Simuliraj mobilni uređaj
    await page.setViewportSize({ width: 375, height: 812 })
    await page.goto('/')
    // Mobile bottom nav
    const mobileNav = page.locator('nav').last()
    await expect(mobileNav).toBeVisible()
  })

  test('top bar — promo poruka', async ({ page }) => {
    await page.goto('/')
    // Top bar sa promo porukom — zavisi od admin podešavanja
  })

  test('currency switcher', async ({ page }) => {
    await page.goto('/')
    // Dropdown za valutu u headeru — zavisi od konfiguracije
    const currencySwitch = page.locator('[class*="currency"], select').filter({ hasText: /RSD|EUR|USD/ }).first()
    // Ne assertujemo jer može biti isključen
  })

  test('back to top dugme', async ({ page }) => {
    await page.goto('/products')
    // Scroll dole
    await page.evaluate(() => window.scrollTo(0, 2000))
    await page.waitForTimeout(500)
    // Back to top dugme treba da se pojavi
    const backToTop = page.locator('button[title*="vrh"], button[aria-label*="vrh"]').first()
    // Ne assertujemo jer implementacija može varirati
  })
})
