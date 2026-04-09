<script setup lang="ts">
const route = useRoute()
const orderNumber = route.params.number

useHead({ title: `Narudžbina ${orderNumber} — eLokal` })
</script>

<template>
  <div class="max-w-lg mx-auto px-4 py-16 text-center">
    <svg class="mx-auto w-20 h-20 text-green-500 mb-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>

    <h1 class="text-3xl font-bold text-gray-800 mb-3">Hvala na narudžbini!</h1>
    <p class="text-gray-600 mb-2">Vaša narudžbina je uspešno kreirana.</p>
    <p class="text-lg font-semibold text-primary-600 mb-8">Broj narudžbine: {{ orderNumber }}</p>

    <p class="text-sm text-gray-500 mb-8">
      Potvrdu narudžbine ćete dobiti na email. Za praćenje statusa koristite nalog.
    </p>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
      <NuxtLink to="/proizvodi">
        <UiAtomsButton>Nastavi kupovinu</UiAtomsButton>
      </NuxtLink>
      <NuxtLink to="/nalog/orders">
        <UiAtomsButton variant="outline">Moje narudžbine</UiAtomsButton>
      </NuxtLink>
    </div>
  </div>
</template>
