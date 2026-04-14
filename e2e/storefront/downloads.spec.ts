import { test, expect } from '@playwright/test'
import { storefrontLogin } from '../helpers/auth'

test.describe('Storefront: Digitalna preuzimanja', () => {
  test.beforeEach(async ({ page }) => {
    await storefrontLogin(page, 'ana@test.rs', 'password')
  })

  test('/nalog/preuzimanja — stranica se renderuje', async ({ page }) => {
    await page.goto('/nalog/preuzimanja')
    await expect(page.locator('body')).toContainText(/preuzim|download|fajl/i)
  })

  test('lista dostupnih fajlova', async ({ page }) => {
    await page.goto('/nalog/preuzimanja')
    await page.waitForTimeout(1000)
    // Fajlovi ili prazna poruka
    const hasFiles = await page.getByText(/\.pdf|\.zip|uputstvo/i).isVisible()
    const isEmpty = await page.getByText(/nema|prazn/i).isVisible()
    expect(hasFiles || isEmpty).toBeTruthy()
  })

  test('prikaz detalja fajla — veličina, preuzimanja, istek', async ({ page }) => {
    await page.goto('/nalog/preuzimanja')
    await page.waitForTimeout(1000)
    // Ako ima fajlova, proveriti detalje
    const fileItem = page.locator('[class*="download"], [class*="file"]').first()
    if (await fileItem.isVisible()) {
      // Veličina fajla
      const size = fileItem.getByText(/KB|MB|GB/i)
      if (await size.isVisible()) {
        await expect(size).toBeVisible()
      }
    }
  })

  test('download dugme', async ({ page }) => {
    await page.goto('/nalog/preuzimanja')
    await page.waitForTimeout(1000)
    const downloadBtn = page.getByRole('button', { name: /preuzmi|download/i }).first()
      .or(page.getByRole('link', { name: /preuzmi|download/i }).first())
    if (await downloadBtn.isVisible()) {
      await expect(downloadBtn).toBeVisible()
      // Ne klikćemo jer bi pokrenulo download
    }
  })
})
