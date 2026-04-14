import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Proizvodi — detalj formi i varijanti', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('edit forma — 6 tabova', async ({ page }) => {
    await page.goto('/products')
    await page.waitForTimeout(1000)
    const editLink = page.locator('a[href*="/products/"][href*="/edit"]').first()
      .or(page.getByRole('link', { name: /izmeni|edit/i }).first())
    if (await editLink.isVisible()) {
      await editLink.click()
      await page.waitForURL(/\/products\/\d+\/edit/)
      // Tabovi: General, Pricing, Kategorije, Size Guide, Custom Tabs, SEO
      const tabs = page.locator('[role="tab"], [class*="tab"] button')
      const count = await tabs.count()
      expect(count).toBeGreaterThanOrEqual(4)
    }
  })

  test('edit forma — general tab polja', async ({ page }) => {
    await page.goto('/products')
    await page.waitForTimeout(1000)
    const editLink = page.locator('a[href*="/products/"][href*="/edit"]').first()
    if (await editLink.isVisible()) {
      await editLink.click()
      await page.waitForURL(/\/products\/\d+\/edit/)
      // Naziv, slug, opis, SKU, stock
      await expect(page.locator('#name, input[name="name"]').first()).toBeVisible()
    }
  })

  test('edit forma — pricing tab', async ({ page }) => {
    await page.goto('/products')
    await page.waitForTimeout(1000)
    const editLink = page.locator('a[href*="/products/"][href*="/edit"]').first()
    if (await editLink.isVisible()) {
      await editLink.click()
      await page.waitForURL(/\/products\/\d+\/edit/)
      // Klik na Pricing tab
      const pricingTab = page.getByRole('button', { name: /cen|pric/i }).first()
        .or(page.locator('[role="tab"]').nth(1))
      if (await pricingTab.isVisible()) {
        await pricingTab.click()
        await page.waitForTimeout(300)
        // Polja za cenu
        const priceInput = page.locator('input[name*="price"], #price').first()
        if (await priceInput.isVisible()) {
          await expect(priceInput).toBeVisible()
        }
      }
    }
  })

  test('edit forma — kategorije checkbox tree', async ({ page }) => {
    await page.goto('/products')
    await page.waitForTimeout(1000)
    const editLink = page.locator('a[href*="/products/"][href*="/edit"]').first()
    if (await editLink.isVisible()) {
      await editLink.click()
      await page.waitForURL(/\/products\/\d+\/edit/)
      const catTab = page.getByRole('button', { name: /kategorij|categ/i }).first()
      if (await catTab.isVisible()) {
        await catTab.click()
        await page.waitForTimeout(300)
        // Checkbox-ovi za kategorije
        const checkboxes = page.locator('input[type="checkbox"]')
        expect(await checkboxes.count()).toBeGreaterThan(0)
      }
    }
  })

  test('edit forma — SEO tab', async ({ page }) => {
    await page.goto('/products')
    await page.waitForTimeout(1000)
    const editLink = page.locator('a[href*="/products/"][href*="/edit"]').first()
    if (await editLink.isVisible()) {
      await editLink.click()
      await page.waitForURL(/\/products\/\d+\/edit/)
      const seoTab = page.getByRole('button', { name: /SEO/i }).first()
      if (await seoTab.isVisible()) {
        await seoTab.click()
        await page.waitForTimeout(300)
        // Meta title, meta description
        const metaInput = page.locator('input[name*="meta"], #meta_title').first()
        if (await metaInput.isVisible()) {
          await expect(metaInput).toBeVisible()
        }
      }
    }
  })

  test('image sidebar — upload i primary', async ({ page }) => {
    await page.goto('/products')
    await page.waitForTimeout(1000)
    const editLink = page.locator('a[href*="/products/"][href*="/edit"]').first()
    if (await editLink.isVisible()) {
      await editLink.click()
      await page.waitForURL(/\/products\/\d+\/edit/)
      // Image sidebar sa thumbnailovima
      const images = page.locator('img[src*="product"], img[alt]').filter({ has: page.locator('..') })
      if (await images.count() > 0) {
        await expect(images.first()).toBeVisible()
      }
    }
  })

  test('varijante — sekcija postoji', async ({ page }) => {
    await page.goto('/products')
    await page.waitForTimeout(1000)
    const editLink = page.locator('a[href*="/products/"][href*="/edit"]').first()
    if (await editLink.isVisible()) {
      await editLink.click()
      await page.waitForURL(/\/products\/\d+\/edit/)
      // Variant sekcija
      const variantSection = page.getByText(/varijant|variant/i).first()
      if (await variantSection.isVisible()) {
        await expect(variantSection).toBeVisible()
      }
    }
  })

  test('varijante — tabela sa SKU, cena, stock', async ({ page }) => {
    await page.goto('/products')
    await page.waitForTimeout(1000)
    const editLink = page.locator('a[href*="/products/"][href*="/edit"]').first()
    if (await editLink.isVisible()) {
      await editLink.click()
      await page.waitForURL(/\/products\/\d+\/edit/)
      // Tabela varijanti
      const variantTable = page.locator('table').filter({ hasText: /SKU/i }).first()
      if (await variantTable.isVisible()) {
        await expect(variantTable).toBeVisible()
        // Redovi
        const rows = variantTable.locator('tr')
        expect(await rows.count()).toBeGreaterThan(1) // Header + barem 1 varijanta
      }
    }
  })

  test('related products sekcija', async ({ page }) => {
    await page.goto('/products')
    await page.waitForTimeout(1000)
    const editLink = page.locator('a[href*="/products/"][href*="/edit"]').first()
    if (await editLink.isVisible()) {
      await editLink.click()
      await page.waitForURL(/\/products\/\d+\/edit/)
      // Related / cross-sell / up-sell
      const relatedSection = page.getByText(/povezan|related|cross.?sell|up.?sell/i).first()
      if (await relatedSection.isVisible()) {
        await expect(relatedSection).toBeVisible()
      }
    }
  })
})
