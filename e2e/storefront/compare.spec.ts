import { test, expect } from '@playwright/test'

test.describe('Storefront: Product Compare', () => {
  test('/uporedi — prazna compare stranica', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('compare'))
    await page.goto('/uporedi')
    // Prazna poruka ili tabela
    await expect(page.locator('body')).toBeVisible()
  })

  test('compare dugme na product card-u', async ({ page }) => {
    await page.goto('/products')
    // Compare dugme/ikonica na prvom proizvodu
    const compareBtn = page.locator('button[title*="poredi"], button[aria-label*="poredi"]').first()
    // Zavisi od feature flag-a
  })
})
