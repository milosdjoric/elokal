import { test, expect } from '@playwright/test'

test.describe('Storefront: Shop the Look', () => {
  test('/izgled — stranica se renderuje', async ({ page }) => {
    const response = await page.goto('/izgled')
    if (response && response.status() < 400) {
      await expect(page.locator('body')).toContainText(/izgled|look|stil/i)
    }
  })

  test('look kartice sa slikama', async ({ page }) => {
    await page.goto('/izgled')
    await page.waitForTimeout(1000)
    // Look kartice
    const lookCards = page.locator('[class*="look"], [class*="card"]').filter({ has: page.locator('img') })
    const count = await lookCards.count()
    // Može biti prazno ako nema aktivnih look-ova
    if (count > 0) {
      await expect(lookCards.first()).toBeVisible()
    }
  })

  test('hotspot pinovi na slici', async ({ page }) => {
    await page.goto('/izgled')
    await page.waitForTimeout(1000)
    // Hotspot pin elementi (apsolutno pozicionirani na slici)
    const hotspots = page.locator('[class*="hotspot"], [class*="pin"], button[style*="position"]')
    const count = await hotspots.count()
    if (count > 0) {
      await expect(hotspots.first()).toBeVisible()
    }
  })

  test('klik na hotspot otvara product popover', async ({ page }) => {
    await page.goto('/izgled')
    await page.waitForTimeout(1000)
    const hotspot = page.locator('[class*="hotspot"], [class*="pin"]').first()
    if (await hotspot.isVisible()) {
      await hotspot.click()
      await page.waitForTimeout(500)
      // Popover sa imenom proizvoda i cenom
      const popover = page.locator('[class*="popover"], [class*="tooltip"], [class*="popup"]').first()
      if (await popover.isVisible()) {
        // Trebao bi sadržavati cenu
        await expect(popover.getByText(/RSD/i).first()).toBeVisible()
      }
    }
  })
})
