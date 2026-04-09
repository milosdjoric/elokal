import { test, expect } from '@playwright/test'

test.describe('Storefront: PLP (Product Listing Page)', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/products')
    await expect(page.getByRole('heading', { name: 'Proizvodi', exact: false })).toBeVisible({ timeout: 10000 })
  })

  test('stranica se renderuje sa proizvodima', async ({ page }) => {
    // Toolbar sa brojem proizvoda
    await expect(page.getByText(/\d+ proizvoda/)).toBeVisible()
    // Grid sa proizvodima
    await expect(page.locator('a[href*="/products/"]').first()).toBeVisible()
  })

  test('sortiranje — promena sort dropdown-a', async ({ page }) => {
    const sortSelect = page.locator('select').filter({ hasText: 'Najnovije' })
    await sortSelect.selectOption('price')
    // URL treba da sadrži sort=price
    await expect(page).toHaveURL(/sort=price/)
  })

  test('per page — promena broja po stranici', async ({ page }) => {
    const perPageSelect = page.locator('select').filter({ hasText: '12' })
    await perPageSelect.selectOption('24')
    await expect(page).toHaveURL(/per_page=24/)
  })

  test('layout switcher — grid/list/compact', async ({ page }) => {
    // Lista dugme
    const listBtn = page.locator('button[title="Lista"]')
    await listBtn.click()
    // Compact dugme
    const compactBtn = page.locator('button[title="Kompaktno"]')
    await compactBtn.click()
    // Grid dugme
    const gridBtn = page.locator('button[title="Grid"]')
    await gridBtn.click()
  })

  test('sidebar — kategorije su vidljive', async ({ page }) => {
    await expect(page.getByText('Kategorije')).toBeVisible()
    await expect(page.getByText('Sve kategorije')).toBeVisible()
  })

  test('sidebar — klik na kategoriju filtrira', async ({ page }) => {
    // Klikni prvu kategoriju (ne "Sve kategorije")
    const catButton = page.locator('aside button').nth(1)
    const catName = await catButton.innerText()
    await catButton.click()
    // URL treba da sadrži category param
    await expect(page).toHaveURL(/category=/)
  })

  test('sidebar — cena filter', async ({ page }) => {
    await expect(page.getByText('Cena')).toBeVisible()
    // Popuni min cenu
    const minInput = page.locator('aside input[type="number"]').first()
    await minInput.fill('1000')
    // Klikni "Primeni"
    await page.locator('aside').getByText('Primeni').click()
    await expect(page).toHaveURL(/min_price=1000/)
  })

  test('active filters — prikaz i uklanjanje', async ({ page }) => {
    // Selektuj kategoriju da imamo active filter
    const catButton = page.locator('aside button').nth(1)
    await catButton.click()
    await page.waitForTimeout(500)

    // Active filter chip treba da bude vidljiv
    const filterChip = page.locator('.rounded-full').first()
    if (await filterChip.isVisible()) {
      // Klikni X da ukloniš filter
      await filterChip.locator('button').click()
      // URL ne treba da ima category
      await expect(page).not.toHaveURL(/category=/)
    }
  })

  test('paginacija — klik na sledeću stranicu', async ({ page }) => {
    // Proveravamo da li postoji paginacija
    const pageButtons = page.locator('.flex.justify-center.gap-2 button')
    const count = await pageButtons.count()
    if (count > 1) {
      await pageButtons.nth(1).click()
      await expect(page).toHaveURL(/page=2/)
    }
  })

  test('klik na proizvod vodi na PDP', async ({ page }) => {
    const productLink = page.locator('a[href*="/products/"]').first()
    await productLink.click()
    await expect(page).toHaveURL(/\/products\/[^/]+$/)
  })
})
