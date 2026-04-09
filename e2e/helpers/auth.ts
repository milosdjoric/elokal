import { type Page } from '@playwright/test'

/**
 * Helper za popunjavanje UiAtomsInput komponente.
 * Label nema for atribut, pa lociramo tekst labela i biramo input u istom kontejneru.
 */
async function fillInput(page: Page, labelText: string, value: string) {
  const container = page.locator('div').filter({ hasText: new RegExp(`^${labelText}`) }).last()
  await container.locator('input').fill(value)
}

export async function storefrontLogin(page: Page, email: string, password: string) {
  await page.goto('/account/login')
  await fillInput(page, 'Email', email)
  await fillInput(page, 'Lozinka', password)
  await page.getByRole('button', { name: 'Prijavi se' }).click()
  await page.waitForURL(/\/(account|$)/)
}

export async function storefrontRegister(
  page: Page,
  user: { name: string; email: string; password: string; phone?: string }
) {
  await page.goto('/account/register')
  await fillInput(page, 'Ime i prezime', user.name)
  await fillInput(page, 'Email', user.email)
  if (user.phone) {
    await fillInput(page, 'Telefon', user.phone)
  }
  await fillInput(page, 'Lozinka', user.password)
  await fillInput(page, 'Potvrdite lozinku', user.password)
  await page.getByRole('button', { name: 'Registruj se' }).click()
  await page.waitForURL(/\/account/)
}

export async function storefrontLogout(page: Page) {
  await page.goto('/account')
  await page.getByRole('link', { name: /odjav/i }).or(page.getByRole('button', { name: /odjav/i })).click()
  await page.waitForURL('/')
}

export async function adminLogin(page: Page, email: string, password: string) {
  await page.goto('/login')
  await page.locator('#email').fill(email)
  await page.locator('#password').fill(password)
  await page.getByRole('button', { name: 'Prijavi se' }).click()
  await page.waitForURL('/')
}

export async function adminLogout(page: Page) {
  await page.getByRole('button', { name: /odjav/i }).click()
  await page.waitForURL(/\/login/)
}
