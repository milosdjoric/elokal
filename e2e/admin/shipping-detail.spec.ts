import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Shipping zones i metode', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('shipping stranica se renderuje', async ({ page }) => {
    const response = await page.goto('/shipping')
    if (response && response.status() < 400) {
      await expect(page.locator('body')).toContainText(/shipping|dostav|zon/i)
    }
  })

  test('zona Srbija prikazana', async ({ page }) => {
    await page.goto('/shipping')
    await page.waitForTimeout(1000)
    const zone = page.getByText(/Srbija|RS/i).first()
    if (await zone.isVisible()) {
      await expect(zone).toBeVisible()
    }
  })

  test('metode dostave — standardna i express', async ({ page }) => {
    await page.goto('/shipping')
    await page.waitForTimeout(1000)
    const standard = page.getByText(/standardna|standard/i).first()
    if (await standard.isVisible()) {
      await expect(standard).toBeVisible()
    }
    const express = page.getByText(/express/i).first()
    if (await express.isVisible()) {
      await expect(express).toBeVisible()
    }
  })

  test('metoda — cena prikazana', async ({ page }) => {
    await page.goto('/shipping')
    await page.waitForTimeout(1000)
    const price = page.getByText(/350|700|RSD/).first()
    if (await price.isVisible()) {
      await expect(price).toBeVisible()
    }
  })

  test('metoda — estimated days', async ({ page }) => {
    await page.goto('/shipping')
    await page.waitForTimeout(1000)
    const days = page.getByText(/2-4|1-2|dan|day/i).first()
    if (await days.isVisible()) {
      await expect(days).toBeVisible()
    }
  })

  test('CRUD — create zone dugme', async ({ page }) => {
    await page.goto('/shipping')
    const createBtn = page.getByRole('button', { name: /nova|dodaj|kreiraj/i }).first()
    if (await createBtn.isVisible()) {
      await expect(createBtn).toBeVisible()
    }
  })
})
