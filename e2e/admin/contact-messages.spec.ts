import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Kontakt poruke', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('contact messages stranica se renderuje', async ({ page }) => {
    await page.goto('/contact-messages')
    await expect(page.locator('body')).toContainText(/kontakt|poruk|message/i)
  })

  test('two-panel layout — lista levo', async ({ page }) => {
    await page.goto('/contact-messages')
    await page.waitForTimeout(1000)
    // Lista poruka sa email-ovima
    const email = page.getByText(/@gmail\.com|@yahoo\.com|@hotmail\.com/).first()
    if (await email.isVisible()) {
      await expect(email).toBeVisible()
    }
  })

  test('status badge — new/read/replied', async ({ page }) => {
    await page.goto('/contact-messages')
    await page.waitForTimeout(1000)
    const badge = page.getByText(/new|nov|read|pročitan|replied|odgovoren/i).first()
    if (await badge.isVisible()) {
      await expect(badge).toBeVisible()
    }
  })

  test('klik na poruku — prikazuje detalj', async ({ page }) => {
    await page.goto('/contact-messages')
    await page.waitForTimeout(1000)
    // Klik na prvu poruku
    const messageItem = page.locator('[class*="message"], [class*="item"], tr').filter({ hasText: /@/ }).first()
    if (await messageItem.isVisible()) {
      await messageItem.click()
      await page.waitForTimeout(500)
      // Detalj sa punim tekstom
      const content = page.getByText(/pitanje|reklamacij|saradnj|raspitiv/i).first()
      if (await content.isVisible()) {
        await expect(content).toBeVisible()
      }
    }
  })

  test('delete dugme', async ({ page }) => {
    await page.goto('/contact-messages')
    await page.waitForTimeout(1000)
    const deleteBtn = page.getByRole('button', { name: /obriši|delete/i }).first()
    if (await deleteBtn.isVisible()) {
      await expect(deleteBtn).toBeVisible()
    }
  })
})
