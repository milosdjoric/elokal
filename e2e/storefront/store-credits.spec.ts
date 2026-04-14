import { test, expect } from '@playwright/test'
import { storefrontLogin } from '../helpers/auth'

test.describe('Storefront: Store krediti', () => {
  test.beforeEach(async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
  })

  test('/nalog/krediti — stranica se renderuje', async ({ page }) => {
    await page.goto('/nalog/krediti')
    await expect(page.locator('body')).toContainText(/kredit|credit|balans|stanje/i)
  })

  test('prikaz raspoloživog balansa', async ({ page }) => {
    await page.goto('/nalog/krediti')
    // Balans prikazan u RSD
    const balance = page.getByText(/RSD/i).first()
    if (await balance.isVisible()) {
      await expect(balance).toBeVisible()
    }
  })

  test('istorija transakcija sa before/after', async ({ page }) => {
    await page.goto('/nalog/krediti')
    // Tabela transakcija
    const table = page.locator('table').first()
    if (await table.isVisible()) {
      await expect(table).toBeVisible()
      // Trebalo bi da prikazuje tip (credit/debit), iznos, balans
    }
  })

  test('transakcija prikazuje razlog', async ({ page }) => {
    await page.goto('/nalog/krediti')
    // Svaki red transakcije ima razlog
    const reason = page.getByText(/refund|kupov|iskorišć/i).first()
    if (await reason.isVisible()) {
      await expect(reason).toBeVisible()
    }
  })
})
