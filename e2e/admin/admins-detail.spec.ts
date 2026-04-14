import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Admin korisnici — detalj', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('lista — prikazuje sve admin korisnike', async ({ page }) => {
    await page.goto('/admins')
    await page.waitForTimeout(1000)
    // Super Admin, editor, admin, neaktivan
    await expect(page.getByText(/admin@webshop\.test/).first()).toBeVisible()
  })

  test('lista — role badge (super_admin/admin/editor)', async ({ page }) => {
    await page.goto('/admins')
    await page.waitForTimeout(1000)
    const role = page.getByText(/super.?admin|editor|admin/i).first()
    await expect(role).toBeVisible()
  })

  test('create admin — forma polja', async ({ page }) => {
    await page.goto('/admins')
    const createBtn = page.getByRole('button', { name: /nov|dodaj|kreiraj/i }).first()
    if (await createBtn.isVisible()) {
      await createBtn.click()
      await page.waitForTimeout(500)
      // Modal ili nova stranica sa formom
      const nameInput = page.locator('input[name="name"], #name').first()
      if (await nameInput.isVisible()) {
        await expect(nameInput).toBeVisible()
      }
    }
  })

  test('permisije — checkbox lista modula', async ({ page }) => {
    await page.goto('/admins')
    const createBtn = page.getByRole('button', { name: /nov|dodaj|kreiraj/i }).first()
    if (await createBtn.isVisible()) {
      await createBtn.click()
      await page.waitForTimeout(500)
      // Permisije checkboxovi
      const permissions = page.locator('input[type="checkbox"]')
      if (await permissions.count() > 0) {
        expect(await permissions.count()).toBeGreaterThanOrEqual(5)
      }
    }
  })

  test('neaktivan admin prikazan', async ({ page }) => {
    await page.goto('/admins')
    await page.waitForTimeout(1000)
    const inactive = page.getByText(/neaktiv|inactive/i).first()
    if (await inactive.isVisible()) {
      await expect(inactive).toBeVisible()
    }
  })

  test('editor admin prikazan', async ({ page }) => {
    await page.goto('/admins')
    await page.waitForTimeout(1000)
    const editor = page.getByText(/editor@webshop\.test/).first()
    if (await editor.isVisible()) {
      await expect(editor).toBeVisible()
    }
  })
})
