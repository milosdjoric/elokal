<script setup lang="ts">
const { setOrganizationSchema } = useSchemaOrg()
const { getValue, loadSettings } = useFeature()

// Ucitaj settings SSR-side da brending (naziv/logo) bude dostupan u prvom renderu —
// po-bazi -> po-klijentu (Flavor A). Jedan build sluzi bilo kog klijenta preko apiBase.
await loadSettings()

onMounted(() => {
  setOrganizationSchema({
    name: getValue('site_name', 'eLokal'),
    url: window.location.origin,
    logo: getValue('site_logo', '') || undefined,
    phone: getValue('site_phone', '') || undefined,
    email: getValue('site_email', '') || undefined,
  })
})
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <LayoutTopBar />
    <LayoutSiteHeader />

    <main class="flex-1 pb-16 lg:pb-0">
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
