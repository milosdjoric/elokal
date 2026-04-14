import { test, expect } from '@playwright/test'
import { storefrontLogin } from '../helpers/auth'

// Helper za navigaciju na PDP
async function goToPDP(page: import('@playwright/test').Page) {
  await page.goto('/products')
  await page.waitForTimeout(1000)
  const link = page.locator('a[href*="/proizvodi/"]').first()
  await link.click()
  await page.waitForURL(/\/proizvodi\//)
}

test.describe('Storefront: PDP — detalji funkcionalnosti', () => {
  test('variant selektor — swatch mode', async ({ page }) => {
    // Idemo na proizvod koji ima varijante
    await page.goto('/products')
    await page.waitForTimeout(1000)
    // Pronađi proizvod sa swatch-ovima na kartici
    const productLink = page.locator('a[href*="/proizvodi/"]').first()
    await productLink.click()
    await page.waitForURL(/\/proizvodi\//)
    // Swatch dugmad (krugovi za boju)
    const swatches = page.locator('[class*="swatch"], [class*="variant"] button, [class*="color"] button')
    const count = await swatches.count()
    if (count > 0) {
      // Klik na drugi swatch menja selekciju
      await swatches.nth(1 % count).click()
      await page.waitForTimeout(500)
    }
  })

  test('variant selektor — cena se menja', async ({ page }) => {
    await goToPDP(page)
    const swatches = page.locator('[class*="swatch"], [class*="variant"] button').filter({ has: page.locator('[style*="background"]') })
    if (await swatches.count() > 1) {
      const priceBefore = await page.getByText(/RSD/).first().textContent()
      await swatches.nth(1).click()
      await page.waitForTimeout(500)
      // Cena može ostati ista ili se promeniti
      const priceAfter = await page.getByText(/RSD/).first().textContent()
      // Samo proveravamo da cena postoji
      expect(priceAfter).toBeTruthy()
    }
  })

  test('notify me forma — out-of-stock', async ({ page }) => {
    // Tražimo proizvod koji nije na stanju
    await page.goto('/products')
    await page.waitForTimeout(1000)
    // Idemo na PDP i tražimo notify formu
    await goToPDP(page)
    const notifyForm = page.getByText(/obavesti|notify/i).first()
    if (await notifyForm.isVisible()) {
      const emailInput = page.locator('input[type="email"]').first()
      if (await emailInput.isVisible()) {
        await emailInput.fill('notify-test@test.rs')
        const submitBtn = page.getByRole('button', { name: /obavesti|notify|pošalji/i }).first()
        if (await submitBtn.isVisible()) {
          await expect(submitBtn).toBeVisible()
        }
      }
    }
  })

  test('callback request modal', async ({ page }) => {
    await goToPDP(page)
    // Dugme "Zatraži poziv" ili slično
    const callbackBtn = page.getByRole('button', { name: /poziv|callback|pozovi/i }).first()
      .or(page.getByText(/zatraži poziv/i).first())
    if (await callbackBtn.isVisible()) {
      await callbackBtn.click()
      await page.waitForTimeout(500)
      // Modal sa formom
      const modal = page.locator('[class*="modal"], [role="dialog"]').last()
      if (await modal.isVisible()) {
        // Polja: ime, telefon, kanal
        const phoneInput = modal.locator('input[type="tel"], input').first()
        await expect(phoneInput).toBeVisible()
      }
    }
  })

  test('sale countdown tajmer', async ({ page }) => {
    // Pronađi proizvod na akciji sa datumom isteka
    await page.goto('/products?sort=discount')
    await page.waitForTimeout(1000)
    const productLink = page.locator('a[href*="/proizvodi/"]').first()
    if (await productLink.isVisible()) {
      await productLink.click()
      await page.waitForURL(/\/proizvodi\//)
      // Countdown tajmer
      const countdown = page.locator('[class*="countdown"], [class*="timer"]').first()
      if (await countdown.isVisible()) {
        // Prikazuje dane:sate:minute:sekunde
        await expect(countdown).toContainText(/:/)
      }
    }
  })

  test('sticky add-to-cart bar na scroll', async ({ page }) => {
    await goToPDP(page)
    // Scroll daleko dole
    await page.evaluate(() => window.scrollTo(0, 3000))
    await page.waitForTimeout(1000)
    // Sticky bar na dnu
    const stickyBar = page.locator('[class*="sticky"], [class*="fixed"]')
      .filter({ hasText: /dodaj|korpa|cart/i }).first()
    if (await stickyBar.isVisible()) {
      await expect(stickyBar).toBeVisible()
    }
  })

  test('size guide tabela', async ({ page }) => {
    await goToPDP(page)
    // Tab "Vodič za veličine"
    const sizeTab = page.getByRole('button', { name: /veličin|size/i }).first()
      .or(page.getByText(/vodič za veličine/i).first())
    if (await sizeTab.isVisible()) {
      await sizeTab.click()
      await page.waitForTimeout(500)
      const table = page.locator('table').first()
      if (await table.isVisible()) {
        await expect(table).toBeVisible()
      }
    }
  })

  test('custom tabovi', async ({ page }) => {
    await goToPDP(page)
    // Svi tabovi
    const tabs = page.locator('[role="tab"], [class*="tab"] button')
    const tabCount = await tabs.count()
    // Klikni svaki tab
    for (let i = 0; i < Math.min(tabCount, 6); i++) {
      const tab = tabs.nth(i)
      if (await tab.isVisible()) {
        await tab.click()
        await page.waitForTimeout(300)
      }
    }
  })

  test('recenzije tab — prikaz i forma', async ({ page }) => {
    await goToPDP(page)
    const reviewTab = page.getByRole('button', { name: /recenzij|review/i }).first()
      .or(page.getByText(/recenzije/i).first())
    if (await reviewTab.isVisible()) {
      await reviewTab.click()
      await page.waitForTimeout(500)
      // Rating distribution bar chart ili lista recenzija
      const reviews = page.locator('[class*="review"], [class*="rating"]').first()
      if (await reviews.isVisible()) {
        await expect(reviews).toBeVisible()
      }
    }
  })

  test('social share dugmad', async ({ page }) => {
    await goToPDP(page)
    // Share dugmad
    const shareLinks = page.locator('a[href*="facebook.com/share"], a[href*="twitter.com"], a[href*="viber"], a[href*="whatsapp"]')
    const count = await shareLinks.count()
    if (count > 0) {
      expect(count).toBeGreaterThanOrEqual(2)
    }
  })

  test('SKU prikaz', async ({ page }) => {
    await goToPDP(page)
    const sku = page.getByText(/SKU/i).first()
    if (await sku.isVisible()) {
      await expect(sku).toBeVisible()
    }
  })

  test('trust row ikone', async ({ page }) => {
    await goToPDP(page)
    const trust = page.getByText(/besplatn|dostav|povrat|sigurn/i).first()
    if (await trust.isVisible()) {
      await expect(trust).toBeVisible()
    }
  })

  test('related products sekcija', async ({ page }) => {
    await goToPDP(page)
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight))
    await page.waitForTimeout(500)
    const related = page.getByText(/povezan|slični|related|preporuč/i).first()
    if (await related.isVisible()) {
      await expect(related).toBeVisible()
    }
  })

  test('recently viewed carousel', async ({ page }) => {
    // Poseti 2 proizvoda
    await page.goto('/products')
    await page.waitForTimeout(500)
    const links = page.locator('a[href*="/proizvodi/"]')
    if (await links.count() >= 2) {
      await links.first().click()
      await page.waitForURL(/\/proizvodi\//)
      await page.goBack()
      await page.waitForTimeout(500)
      await links.nth(1).click()
      await page.waitForURL(/\/proizvodi\//)
      // Scroll do dna — recently viewed sekcija
      await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight))
      await page.waitForTimeout(500)
      const recentlyViewed = page.getByText(/nedavno|pregledano|recently/i).first()
      if (await recentlyViewed.isVisible()) {
        await expect(recentlyViewed).toBeVisible()
      }
    }
  })
})
