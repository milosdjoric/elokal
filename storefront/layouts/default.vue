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
  </div>
</template>
