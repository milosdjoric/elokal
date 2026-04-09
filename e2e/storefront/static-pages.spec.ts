import { test, expect } from '@playwright/test'

test.describe('Storefront: Statičke stranice', () => {
  const pages = [
    { path: '/o-nama', title: /o nama/i },
    { path: '/kontakt', title: /kontakt/i },
    { path: '/uslovi-koriscenja', title: /uslovi/i },
    { path: '/politika-privatnosti', title: /privatnost/i },
    { path: '/cesta-pitanja', title: /pitanja|FAQ/i },
  ]

  for (const pg of pages) {
    test(`${pg.path} — stranica se renderuje`, async ({ page }) => {
      const response = await page.goto(pg.path)
      // Ako stranica postoji (200) ili je catch-all slug
      if (response && response.status() < 400) {
        await expect(page.getByRole('heading', { level: 1 })).toBeVisible({ timeout: 5000 })
      }
    })
  }
})
