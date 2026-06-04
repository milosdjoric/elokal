<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

const { user } = useAuth()

useHead({ title: 'Moj nalog — sloj kolektiv' })
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: 'Moj nalog' }]" />

    <div class="flex flex-col lg:flex-row gap-8">
      <LayoutAccountSidebar />

      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Dobrodošli, {{ user?.name }}</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <NuxtLink to="/nalog/orders" class="border border-gray-200 p-6 hover:border-primary-300 transition-colors">
            <h3 class="font-semibold text-gray-800 mb-1">Narudžbine</h3>
            <p class="text-sm text-gray-500">Pregledajte istoriju narudžbina</p>
          </NuxtLink>

          <NuxtLink to="/nalog/addresses" class="border border-gray-200 p-6 hover:border-primary-300 transition-colors">
            <h3 class="font-semibold text-gray-800 mb-1">Adrese</h3>
            <p class="text-sm text-gray-500">Upravljajte adresama za dostavu</p>
          </NuxtLink>

          <NuxtLink to="/nalog/profile" class="border border-gray-200 p-6 hover:border-primary-300 transition-colors">
            <h3 class="font-semibold text-gray-800 mb-1">Profil</h3>
            <p class="text-sm text-gray-500">Ažurirajte vaše podatke</p>
          </NuxtLink>

          <NuxtLink to="/proizvodi" class="border border-gray-200 p-6 hover:border-primary-300 transition-colors">
            <h3 class="font-semibold text-gray-800 mb-1">Nastavi kupovinu</h3>
            <p class="text-sm text-gray-500">Pogledajte naše proizvode</p>
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>
