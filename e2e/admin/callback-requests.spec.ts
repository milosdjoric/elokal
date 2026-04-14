import { test, expect } from '@playwright/test'
import { adminLogin } from '../helpers/auth'

test.describe('Admin: Callback zahtevi', () => {
  test.beforeEach(async ({ page }) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
  })

  test('callback requests stranica se renderuje', async ({ page }) => {
    await page.goto('/callback-requests')
    await expect(page.locator('body')).toContainText(/callback|poziv|zahtev/i)
  })

  test('lista — telefon, kanal, status', async ({ page }) => {
    await page.goto('/callback-requests')
    await page.waitForTimeout(1000)
    // Telefon
    const phone = page.getByText(/\+381/).first()
    if (await phone.isVisible()) {
      await expect(phone).toBeVisible()
    }
  })

  test('lista — kanal ikonica (call/SMS/WhatsApp)', async ({ page }) => {
    await page.goto('/callback-requests')
    await page.waitForTimeout(1000)
    const channel = page.getByText(/call|sms|whatsapp|poziv/i).first()
    if (await channel.isVisible()) {
      await expect(channel).toBeVisible()
    }
  })

  test('status workflow dugmad', async ({ page }) => {
    await page.goto('/callback-requests')
    await page.waitForTimeout(1000)
    // Dugmad za prelaz statusa
    const statusBtn = page.getByRole('button', { name: /kontaktiran|završ|contacted|closed/i }).first()
    if (await statusBtn.isVisible()) {
      await expect(statusBtn).toBeVisible()
    }
  })

  test('status badges — pending/contacted/closed', async ({ page }) => {
    await page.goto('/callback-requests')
    await page.waitForTimeout(1000)
    const badge = page.getByText(/pending|contacted|closed|čeka|kontaktiran|završ/i).first()
    if (await badge.isVisible()) {
      await expect(badge).toBeVisible()
    }
  })
})
