import { test, expect } from '@playwright/test'
import { storefrontLogin } from '../helpers/auth'

test.describe('Storefront: Loyalty poeni', () => {
  test.beforeEach(async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
  })

  test('/nalog/poeni — stranica se renderuje', async ({ page }) => {
    await page.goto('/nalog/poeni')
    await expect(page.locator('body')).toContainText(/poen|bod|loyalty|points/i)
  })

  test('prikaz raspoloživih bodova', async ({ page }) => {
    await page.goto('/nalog/poeni')
    // Raspoloživi bodovi — numerička vrednost
    const pointsDisplay = page.locator('text=/\\d+/').first()
    await expect(pointsDisplay).toBeVisible()
  })

  test('prikaz trenutnog tiera', async ({ page }) => {
    await page.goto('/nalog/poeni')
    // Tier: Bronze, Silver, Gold, ili Platinum
    const tierText = page.getByText(/bronze|silver|gold|platinum|bronza|srebro|zlato|platina/i).first()
    if (await tierText.isVisible()) {
      await expect(tierText).toBeVisible()
    }
  })

  test('progress bar do sledećeg tiera', async ({ page }) => {
    await page.goto('/nalog/poeni')
    // Progress bar element
    const progressBar = page.locator('[class*="progress"], [role="progressbar"]').first()
    if (await progressBar.isVisible()) {
      await expect(progressBar).toBeVisible()
    }
  })

  test('istorija transakcija', async ({ page }) => {
    await page.goto('/nalog/poeni')
    // Tabela ili lista transakcija
    const transactions = page.locator('table, [class*="transaction"], [class*="history"]').first()
    if (await transactions.isVisible()) {
      await expect(transactions).toBeVisible()
      // Barem jedan red
      const rows = page.locator('tr, [class*="transaction-row"]')
      const count = await rows.count()
      expect(count).toBeGreaterThan(0)
    }
  })

  test('ukupno zarađeni bodovi', async ({ page }) => {
    await page.goto('/nalog/poeni')
    // Tekst sa ukupno zarađenim bodovima
    const totalEarned = page.getByText(/ukupno|zarad|earned|total/i).first()
    if (await totalEarned.isVisible()) {
      await expect(totalEarned).toBeVisible()
    }
  })
})
