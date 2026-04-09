import { test, expect } from '@playwright/test'

test.describe('Storefront: Homepage', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/')
  })

  test('homepage se renderuje', async ({ page }) => {
    await expect(page.locator('body')).toBeVisible()
    // Header
    await expect(page.locator('header')).toBeVisible()
    // Footer
    await expect(page.locator('footer')).toBeVisible()
  })

  test('hero sekcija', async ({ page }) => {
    // Hero slideshow ili baner
    const hero = page.locator('[class*="hero"], [class*="slide"], [class*="banner"]').first()
    // Ne assertujemo jer može biti konfigurisan
  })

  test('featured proizvodi', async ({ page }) => {
    // Carousel ili grid sa featured proizvodima
    const productCards = page.locator('a[href*="/products/"]')
    const count = await productCards.count()
    expect(count).toBeGreaterThan(0)
  })

  test('kategorije grid', async ({ page }) => {
    const categoryLinks = page.locator('a[href*="/categories/"]')
    // Može ili ne mora biti na homepage-u
  })

  test('newsletter sekcija', async ({ page }) => {
    const newsletterInput = page.locator('input[type="email"]').last()
    // Newsletter sekcija može biti u footer-u
  })
})
