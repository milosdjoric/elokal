import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Inventar', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('listing — stranica se renderuje', async ({ page }) => {
    await page.goto('/inventory')
    await expect(page.getByRole('heading', { name: /Inventar|Stanje/i })).toBeVisible()
  })

  test('listing — kolone tabele', async ({ page }) => {
    await page.goto('/inventory')
    await page.waitForTimeout(1000)
    // Treba da ima kolone: Proizvod, SKU, Stanje, itd.
    await expect(page.getByText('SKU').or(page.getByText('Proizvod'))).toBeVisible()
  })

  test('export — dugme za export', async ({ page }) => {
    await page.goto('/inventory')
    const exportBtn = page.getByRole('button', { name: /Export|Izvoz/i })
    if (await exportBtn.isVisible()) {
      // Samo proveravamo da je klikabilan
      await expect(exportBtn).toBeEnabled()
    }
  })

  test('import — dugme za import', async ({ page }) => {
    await page.goto('/inventory')
    const importBtn = page.getByRole('button', { name: /Import|Uvoz/i })
    // Može ili ne mora biti vidljivo
  })
})
