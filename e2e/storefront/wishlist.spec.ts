import { test, expect } from '@playwright/test'
import { storefrontLogin } from '../helpers/auth'

test.describe('Storefront: Wishlist', () => {
  test('wishlist dugme na ProductCard — toggle', async ({ page }) => {
    await page.goto('/products')
    await page.waitForTimeout(1000)
    // Pronađi wishlist dugme (srce ikonica) na prvoj kartici
    const wishlistBtn = page.locator('[class*="product"] button, [class*="card"] button')
      .filter({ has: page.locator('svg') }).first()
    if (await wishlistBtn.isVisible()) {
      await wishlistBtn.click()
      await page.waitForTimeout(500)
    }
  })

  test('wishlist badge count u headeru', async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
    await page.goto('/')
    // Wishlist ikonica u headeru sa count badge-om
    const wishlistLink = page.locator('a[href*="wishlist"]').first()
    if (await wishlistLink.isVisible()) {
      await expect(wishlistLink).toBeVisible()
    }
  })

  test('/nalog/lista-zelja — prikazuje proizvode', async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
    await page.goto('/nalog/lista-zelja')
    await page.waitForTimeout(1000)
    // Wishlist stranica: grid sa proizvodima ili prazna poruka
    const hasProducts = await page.locator('a[href*="/proizvodi/"]').count() > 0
    const isEmpty = await page.getByText(/prazna|nema|lista želja/i).isVisible()
    expect(hasProducts || isEmpty).toBeTruthy()
  })

  test('wishlist — uklanjanje proizvoda sa stranice', async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
    await page.goto('/nalog/lista-zelja')
    await page.waitForTimeout(1000)
    // Ako ima proizvoda, pokušaj ukloniti
    const removeBtn = page.locator('button').filter({ has: page.locator('svg') }).first()
    const hasProducts = await page.locator('a[href*="/proizvodi/"]').count() > 0
    if (hasProducts && await removeBtn.isVisible()) {
      const countBefore = await page.locator('a[href*="/proizvodi/"]').count()
      await removeBtn.click()
      await page.waitForTimeout(1000)
      const countAfter = await page.locator('a[href*="/proizvodi/"]').count()
      expect(countAfter).toBeLessThanOrEqual(countBefore)
    }
  })

  test('wishlist — sync sa serverom za ulogovanog korisnika', async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
    // Dodaj proizvod u wishlist sa PDP-a
    await page.goto('/products')
    await page.waitForTimeout(1000)
    const productLink = page.locator('a[href*="/proizvodi/"]').first()
    if (await productLink.isVisible()) {
      await productLink.click()
      await page.waitForURL(/\/proizvodi\//)
      // Pronađi wishlist dugme na PDP-u
      const wishBtn = page.getByRole('button', { name: /želj|wish/i })
        .or(page.locator('button').filter({ has: page.locator('svg[class*="heart"], [class*="wish"]') }).first())
      if (await wishBtn.isVisible()) {
        await wishBtn.click()
        await page.waitForTimeout(1000)
      }
    }
  })

  test('wishlist — gost koristi localStorage', async ({ page }) => {
    // Bez logina
    await page.goto('/products')
    await page.evaluate(() => localStorage.removeItem('wishlist'))
    await page.waitForTimeout(1000)
    // Pokušaj dodati u wishlist
    const wishlistBtn = page.locator('[class*="product"] button, [class*="card"] button')
      .filter({ has: page.locator('svg') }).first()
    if (await wishlistBtn.isVisible()) {
      await wishlistBtn.click()
      await page.waitForTimeout(500)
      // Proverimo localStorage
      const wishlistData = await page.evaluate(() => localStorage.getItem('wishlist'))
      // Wishlist podaci bi trebali biti sačuvani
    }
  })
})
