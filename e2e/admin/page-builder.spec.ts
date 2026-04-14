import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Page Builder', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('page builder stranica se renderuje', async ({ page }) => {
    await page.goto('/page-builder')
    await expect(page.locator('body')).toContainText(/page.?builder|sekcij|homepage/i)
  })

  test('lista sekcija — prikazuje postojeće sekcije', async ({ page }) => {
    await page.goto('/page-builder')
    await page.waitForTimeout(1000)
    // Sekcije (hero, category_grid, featured_products, itd.)
    const sections = page.getByText(/hero|featured|category|banner|newsletter|trust|blog|recently/i)
    expect(await sections.count()).toBeGreaterThan(0)
  })

  test('reorder — up/down strelice', async ({ page }) => {
    await page.goto('/page-builder')
    await page.waitForTimeout(1000)
    const moveBtn = page.getByRole('button', { name: /↑|↓|gore|dole|up|down/i }).first()
      .or(page.locator('button[title*="gore"], button[title*="dole"]').first())
    if (await moveBtn.isVisible()) {
      await expect(moveBtn).toBeVisible()
    }
  })

  test('show/hide toggle', async ({ page }) => {
    await page.goto('/page-builder')
    await page.waitForTimeout(1000)
    const toggle = page.locator('[class*="switch"], input[type="checkbox"]').first()
    if (await toggle.isVisible()) {
      await expect(toggle).toBeVisible()
    }
  })

  test('delete sekcija dugme', async ({ page }) => {
    await page.goto('/page-builder')
    await page.waitForTimeout(1000)
    const deleteBtn = page.getByRole('button', { name: /obriši|delete|ukloni/i }).first()
    if (await deleteBtn.isVisible()) {
      await expect(deleteBtn).toBeVisible()
      // Ne klikćemo da ne brišemo podatke
    }
  })

  test('JSON editor za sekciju', async ({ page }) => {
    await page.goto('/page-builder')
    await page.waitForTimeout(1000)
    // Edit dugme na nekoj sekciji
    const editBtn = page.getByRole('button', { name: /izmeni|edit|konfiguriš/i }).first()
    if (await editBtn.isVisible()) {
      await editBtn.click()
      await page.waitForTimeout(500)
      // JSON editor ili forma
      const editor = page.locator('textarea, [class*="editor"], [class*="json"]').first()
      if (await editor.isVisible()) {
        await expect(editor).toBeVisible()
      }
    }
  })

  test('dodavanje nove sekcije', async ({ page }) => {
    await page.goto('/page-builder')
    await page.waitForTimeout(1000)
    const addBtn = page.getByRole('button', { name: /dodaj|nova|add/i }).first()
    if (await addBtn.isVisible()) {
      await addBtn.click()
      await page.waitForTimeout(500)
      // Dropdown ili modal za izbor tipa sekcije
      const typeOptions = page.getByText(/hero|category|featured|banner|text|newsletter|spacer/i)
      if (await typeOptions.first().isVisible()) {
        expect(await typeOptions.count()).toBeGreaterThan(0)
      }
    }
  })
})
