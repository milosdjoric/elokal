<script setup lang="ts">
const { setOrganizationSchema } = useSchemaOrg()
const { getValue } = useFeature()

onMounted(async () => {
  const siteName = await getValue('general_site_name', 'sloj kolektiv')
  const siteUrl = window.location.origin
  const logo = await getValue('general_logo_url', '')
  const phone = await getValue('general_phone', '')
  const email = await getValue('general_email', '')

  setOrganizationSchema({
    name: siteName,
    url: siteUrl,
    logo: logo || undefined,
    phone: phone || undefined,
    email: email || undefined,
  })
})
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <LayoutSiteHeader />

    <main class="flex-1 pb-16 md:pb-0">
      <slot />
    </main>

    <LayoutSiteFooter />
    <LayoutMobileNav />
    <LayoutBackToTop />
    <LayoutCookieConsent />
    <ProductFloatingCompareBar />
    <CartExitIntentPopup />
    <NewsletterPopup />
  </div>
</template>
