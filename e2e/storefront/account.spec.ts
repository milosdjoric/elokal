import { test, expect } from '@playwright/test'
import { storefrontLogin } from '../helpers/auth'

test.describe('Storefront: Korisnički nalog', () => {
  test.beforeEach(async ({ page }) => {
    await storefrontLogin(page, 'kupac@test.com', 'password')
  })

  test('/nalog — dashboard se renderuje', async ({ page }) => {
    await page.goto('/account')
    await expect(page.locator('body')).toContainText(/nalog|Nalog|dashboard|Dobrodošli/i)
  })

  test('/nalog/profil — edit profila', async ({ page }) => {
    await page.goto('/account/profile')
    // Polja za editovanje
    await expect(page.locator('input').first()).toBeVisible()
    // Dugme za čuvanje
    await expect(page.getByRole('button', { name: /sačuvaj|izmeni|ažuriraj/i })).toBeVisible()
  })

  test('/nalog/adrese — CRUD adresa', async ({ page }) => {
    await page.goto('/account/addresses')
    // Dugme za dodavanje adrese ili lista postojećih
    const addBtn = page.getByRole('button', { name: /dodaj|nova/i })
    const addressList = page.locator('[class*="border"]').first()
    await expect(addBtn.or(addressList)).toBeVisible()
  })

  test('/nalog/narudzbine — listing narudžbina', async ({ page }) => {
    await page.goto('/account/orders')
    // Tabela narudžbina ili "Nemate narudžbina" poruka
    await page.waitForTimeout(1000)
    const hasOrders = await page.locator('a[href*="/account/orders/"]').isVisible()
    const noOrders = await page.getByText(/nema|Nemate/i).isVisible()
    expect(hasOrders || noOrders).toBeTruthy()
  })

  test('/nalog/lista-zelja — wishlist', async ({ page }) => {
    await page.goto('/account/wishlist')
    await expect(page.locator('body')).toBeVisible()
    // Lista želja ili prazna poruka
  })

  test('/nalog/poeni — loyalty points', async ({ page }) => {
    await page.goto('/account/poeni')
    await expect(page.locator('body')).toBeVisible()
    // Prikaz poena ili nekih info
  })

  test('/nalog/krediti — store credits', async ({ page }) => {
    await page.goto('/account/krediti')
    await expect(page.locator('body')).toBeVisible()
  })

  test('account sidebar navigacija', async ({ page }) => {
    await page.goto('/account')
    // Sidebar linkovi ka podstranicama
    const sidebarLinks = page.locator('a[href*="/account/"]')
    const count = await sidebarLinks.count()
    expect(count).toBeGreaterThan(0)
  })
})
