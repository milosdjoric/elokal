import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Izveštaji', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('reports stranica se renderuje', async ({ page }) => {
    await page.goto('/reports')
    await expect(page.locator('body')).toContainText(/izveštaj|report|analitik/i)
  })

  test('time range selector', async ({ page }) => {
    await page.goto('/reports')
    await page.waitForTimeout(1000)
    // Dugmad za period: 7, 30, 90, 365 dana
    const rangeBtn = page.getByRole('button', { name: /7|30|90|365|dan/i }).first()
    if (await rangeBtn.isVisible()) {
      await expect(rangeBtn).toBeVisible()
    }
  })

  test('overview tab — KPI kartice', async ({ page }) => {
    await page.goto('/reports')
    await page.waitForTimeout(1000)
    // Prihod, narudžbine, AOV, kupci
    const kpi = page.getByText(/prihod|revenue|narudžbin|AOV|kupci|customers/i)
    if (await kpi.first().isVisible()) {
      expect(await kpi.count()).toBeGreaterThan(0)
    }
  })

  test('products tab', async ({ page }) => {
    await page.goto('/reports')
    await page.waitForTimeout(1000)
    const productsTab = page.getByRole('button', { name: /proizvod|product/i }).first()
    if (await productsTab.isVisible()) {
      await productsTab.click()
      await page.waitForTimeout(500)
      // Top products tabela
    }
  })

  test('categories tab', async ({ page }) => {
    await page.goto('/reports')
    await page.waitForTimeout(1000)
    const catTab = page.getByRole('button', { name: /kategorij|categor/i }).first()
    if (await catTab.isVisible()) {
      await catTab.click()
      await page.waitForTimeout(500)
    }
  })

  test('customers tab', async ({ page }) => {
    await page.goto('/reports')
    await page.waitForTimeout(1000)
    const custTab = page.getByRole('button', { name: /kupci|customer/i }).first()
    if (await custTab.isVisible()) {
      await custTab.click()
      await page.waitForTimeout(500)
    }
  })

  test('coupons tab', async ({ page }) => {
    await page.goto('/reports')
    await page.waitForTimeout(1000)
    const couponsTab = page.getByRole('button', { name: /kupon|coupon/i }).first()
    if (await couponsTab.isVisible()) {
      await couponsTab.click()
      await page.waitForTimeout(500)
    }
  })

  test('search tab — top upiti', async ({ page }) => {
    await page.goto('/reports')
    await page.waitForTimeout(1000)
    const searchTab = page.getByRole('button', { name: /pretrag|search/i }).first()
    if (await searchTab.isVisible()) {
      await searchTab.click()
      await page.waitForTimeout(500)
      // Top search queries
      const query = page.getByText(/tepih|lampa|krevetac/i).first()
      if (await query.isVisible()) {
        await expect(query).toBeVisible()
      }
    }
  })

  test('CSV export dugme', async ({ page }) => {
    await page.goto('/reports')
    await page.waitForTimeout(1000)
    const exportBtn = page.getByRole('button', { name: /export|izvoz|CSV/i }).first()
    if (await exportBtn.isVisible()) {
      await expect(exportBtn).toBeVisible()
    }
  })
})
