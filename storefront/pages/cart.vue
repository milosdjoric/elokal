<script setup lang="ts">
const { items, isEmpty } = useCart()

useHead({ title: 'Korpa — eLokal' })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <LayoutBreadcrumbs :items="[{ label: 'Korpa' }]" />

    <h1 class="text-2xl font-bold text-gray-800 mb-6">Korpa</h1>

    <div v-if="isEmpty" class="text-center py-16">
      <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
      </svg>
      <p class="text-gray-500 mb-4">Vaša korpa je prazna.</p>
      <NuxtLink to="/products">
        <UiAtomsButton>Nastavi kupovinu</UiAtomsButton>
      </NuxtLink>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-4">
        <CartItem v-for="item in items" :key="item.product.id" :item="item" />
      </div>
      <div>
        <CartTotals />
      </div>
    </div>
  </div>
</template>
