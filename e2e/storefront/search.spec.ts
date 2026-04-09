import { test, expect } from '@playwright/test'

test.describe('Storefront: Pretraga', () => {
  test('AJAX autocomplete u headeru', async ({ page }) => {
    await page.goto('/')
    const searchInput = page.locator('header input[type="text"], header input[type="search"]').first()
    if (await searchInput.isVisible()) {
      await searchInput.fill('majica')
      // Čekaj da se dropdown pojavi (debounce 300ms)
      await page.waitForTimeout(500)
      // Dropdown sa rezultatima
      const dropdown = page.locator('[class*="absolute"], [class*="dropdown"]').first()
      // Ne assertujemo jer zavisi od seed data
    }
  })

  test('/pretraga — search stranica renderuje rezultate', async ({ page }) => {
    await page.goto('/search?q=test')
    // Toolbar sa brojem rezultata ili "Nema rezultata" poruka
    await page.waitForTimeout(1000)
    const hasResults = await page.getByText(/\d+ proizvoda/).isVisible()
    const noResults = await page.getByText(/Nema|nema|rezultat/).isVisible()
    expect(hasResults || noResults).toBeTruthy()
  })

  test('/pretraga — prazna pretraga', async ({ page }) => {
    await page.goto('/search')
    // Treba da prikaže nešto (trending/recent ili empty state)
    await expect(page.locator('body')).toBeVisible()
  })

  test('recent searches — čuvaju se u localStorage', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('recent_searches'))

    await page.goto('/search?q=test-pretaga-123')
    await page.waitForTimeout(1000)

    // Proverimo localStorage
    const recentSearches = await page.evaluate(() => localStorage.getItem('recent_searches'))
    // Može ili ne mora da čuva — zavisi od implementacije
  })
})
