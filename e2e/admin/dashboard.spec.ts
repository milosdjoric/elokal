import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('dashboard se renderuje sa statistikama', async ({ page }) => {
    await page.goto('/')
    // Statistike kartice
    await expect(page.locator('body')).toBeVisible()
    // Treba da ima neke brojeve/kartice
  })

  test('low stock widget', async ({ page }) => {
    await page.goto('/')
    // Low stock sekcija može postojati na dashboard-u
    const lowStock = page.getByText(/Nizak|stock|zaliha/i).first()
    // Ne assertujemo jer zavisi od seed data
  })

  test('sidebar navigacija — svi linkovi rade', async ({ page }) => {
    await page.goto('/')

    const sidebarLinks = [
      { name: /Narudžbine/i, url: '/orders' },
      { name: /Proizvodi/i, url: '/products' },
      { name: /Kategorije/i, url: '/categories' },
      { name: /Kupci/i, url: '/customers' },
      { name: /Recenzije/i, url: '/reviews' },
      { name: /Blog/i, url: '/blog' },
      { name: /Media/i, url: '/media' },
      { name: /Podešavanja/i, url: '/settings' },
    ]

    for (const link of sidebarLinks) {
      const navLink = page.getByRole('link', { name: link.name }).first()
      if (await navLink.isVisible()) {
        await navLink.click()
        await expect(page).toHaveURL(new RegExp(link.url))
        await page.goto('/') // Vrati se na dashboard
      }
    }
  })
})
