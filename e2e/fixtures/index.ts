import { test as base, type Page } from '@playwright/test'
import { storefrontLogin, adminLogin } from '../helpers/auth'

type Fixtures = {
  authenticatedStorefrontPage: Page
  authenticatedAdminPage: Page
}

export const test = base.extend<Fixtures>({
  authenticatedStorefrontPage: async ({ page }, use) => {
    await storefrontLogin(page, 'kupac@test.com', 'password')
    await use(page)
  },

  authenticatedAdminPage: async ({ page }, use) => {
    await adminLogin(page, 'admin@webshop.test', 'password')
    await use(page)
  },
})

export { expect } from '@playwright/test'
