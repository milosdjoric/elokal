import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Proizvodi CRUD', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('listing — stranica se renderuje sa tabelom', async ({ page }) => {
    await page.goto('/products')
    await expect(page.getByRole('heading', { name: 'Proizvodi' })).toBeVisible()
    await expect(page.getByRole('button', { name: /Novi proizvod/ })).toBeVisible()
  })

  test('listing — pretraga proizvoda', async ({ page }) => {
    await page.goto('/products')
    const searchInput = page.locator('input[placeholder*="Pretraži"]')
    await searchInput.fill('test')
    await searchInput.press('Enter')
    await page.waitForTimeout(1000)
  })

  test('listing — filter po statusu', async ({ page }) => {
    await page.goto('/products')
    const statusSelect = page.locator('select').filter({ hasText: 'Svi statusi' })
    await statusSelect.selectOption('active')
    await page.waitForTimeout(1000)
  })

  test('create — navigacija na formu', async ({ page }) => {
    await page.goto('/products')
    await page.getByRole('button', { name: /Novi proizvod/ }).or(page.getByRole('link', { name: /Novi proizvod/ })).click()
    await expect(page).toHaveURL(/\/products\/create/)
  })

  test('create — forma se renderuje sa svim poljima', async ({ page }) => {
    await page.goto('/products/create')
    // Osnovna polja
    await expect(page.locator('div').filter({ hasText: /^Naziv/ }).first().locator('input')).toBeVisible()
    // Cena polje
    await expect(page.locator('div').filter({ hasText: /^Cena/ }).first().locator('input')).toBeVisible()
  })

  test('edit — klik na proizvod otvara edit formu', async ({ page }) => {
    await page.goto('/products')
    const editLink = page.getByRole('button', { name: 'Izmeni' }).first()
      .or(page.locator('a[href*="/products/"][href*="/edit"]').first())
    if (await editLink.isVisible()) {
      await editLink.click()
      await expect(page).toHaveURL(/\/products\/\d+\/edit/)
    }
  })

  test('delete — confirm modal se prikazuje', async ({ page }) => {
    await page.goto('/products')
    const deleteBtn = page.getByText('Obriši').first()
    if (await deleteBtn.isVisible()) {
      await deleteBtn.click()
      // Confirm dialog
      await expect(page.getByText('Brisanje proizvoda')).toBeVisible()
      await expect(page.getByText('Da li ste sigurni')).toBeVisible()
    }
  })

  test('listing — kolone tabele', async ({ page }) => {
    await page.goto('/products')
    // Proveravamo kolone
    await expect(page.getByText('Naziv')).toBeVisible()
    await expect(page.getByText('SKU')).toBeVisible()
    await expect(page.getByText('Cena')).toBeVisible()
    await expect(page.getByText('Stanje')).toBeVisible()
    await expect(page.getByText('Status')).toBeVisible()
  })
})
