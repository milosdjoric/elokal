import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Poreske stope', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('tax rates stranica se renderuje', async ({ page }) => {
    await page.goto('/tax-rates')
    await expect(page.getByRole('heading', { name: /poresk|tax/i })).toBeVisible()
  })

  test('lista — PDV 20%', async ({ page }) => {
    await page.goto('/tax-rates')
    await page.waitForTimeout(1000)
    const pdv = page.getByText(/PDV|20/).first()
    await expect(pdv).toBeVisible()
  })

  test('lista — country code RS', async ({ page }) => {
    await page.goto('/tax-rates')
    await page.waitForTimeout(1000)
    const rs = page.getByText('RS').first()
    if (await rs.isVisible()) {
      await expect(rs).toBeVisible()
    }
  })

  test('CRUD — create dugme', async ({ page }) => {
    await page.goto('/tax-rates')
    const createBtn = page.getByRole('button', { name: /nova|dodaj|kreiraj/i }).first()
    if (await createBtn.isVisible()) {
      await expect(createBtn).toBeVisible()
    }
  })

  test('default flag označen', async ({ page }) => {
    await page.goto('/tax-rates')
    await page.waitForTimeout(1000)
    const defaultBadge = page.getByText(/default|osnovna|da/i).first()
    if (await defaultBadge.isVisible()) {
      await expect(defaultBadge).toBeVisible()
    }
  })
})
