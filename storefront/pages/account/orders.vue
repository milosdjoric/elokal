<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

useHead({ title: 'Narudžbine — eLokal' })
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: 'Moj nalog', to: '/account' }, { label: 'Narudžbine' }]" />

    <div class="flex flex-col lg:flex-row gap-8">
      <LayoutAccountSidebar />

      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Narudžbine</h1>

        <div class="text-center py-16 text-gray-500">
          <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
          </svg>
          <p>Nemate narudžbina. Narudžbine će se prikazati ovde nakon kupovine.</p>
          <NuxtLink to="/products" class="inline-block mt-4">
            <UiAtomsButton variant="outline">Pogledaj proizvode</UiAtomsButton>
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>
