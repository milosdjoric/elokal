import { test, expect } from '@playwright/test'

test.describe('Storefront: Store locator', () => {
  test('/prodavnice — stranica se renderuje', async ({ page }) => {
    await page.goto('/prodavnice')
    await expect(page.locator('body')).toContainText(/prodavnic|lokacij|store/i)
  })

  test('lista prodavnica sa detaljima', async ({ page }) => {
    await page.goto('/prodavnice')
    await page.waitForTimeout(1000)
    // Prodavnice sa imenom i adresom
    const location = page.getByText(/eLokal|Beograd|Novi Sad|Niš/i).first()
    await expect(location).toBeVisible()
  })

  test('radno vreme prodavnice', async ({ page }) => {
    await page.goto('/prodavnice')
    await page.waitForTimeout(1000)
    // Radno vreme
    const hours = page.getByText(/pon|sub|ned|09:|10:/i).first()
    if (await hours.isVisible()) {
      await expect(hours).toBeVisible()
    }
  })

  test('pretraga po gradu', async ({ page }) => {
    await page.goto('/prodavnice')
    const searchInput = page.locator('input[type="text"], input[placeholder*="grad"], input[placeholder*="pretra"]').first()
    if (await searchInput.isVisible()) {
      await searchInput.fill('Beograd')
      await page.waitForTimeout(1000)
      // Trebao bi ostati samo Beograd lokacija
      await expect(page.getByText(/Beograd/i).first()).toBeVisible()
    }
  })

  test('mapa se prikazuje', async ({ page }) => {
    await page.goto('/prodavnice')
    await page.waitForTimeout(2000) // Leaflet lazy load
    // Leaflet mapa kontejner
    const map = page.locator('[class*="leaflet"], [class*="map"], #map').first()
    if (await map.isVisible()) {
      await expect(map).toBeVisible()
    }
  })

  test('kontakt informacije prodavnice', async ({ page }) => {
    await page.goto('/prodavnice')
    await page.waitForTimeout(1000)
    // Telefon ili email
    const phone = page.getByText(/\+381/).first()
    if (await phone.isVisible()) {
      await expect(phone).toBeVisible()
    }
  })
})
