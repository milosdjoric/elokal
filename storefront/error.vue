<script setup lang="ts">
const props = defineProps<{ error: { statusCode: number; message: string } }>()

const is404 = computed(() => props.error.statusCode === 404)

useHead({ title: is404.value ? 'Stranica nije pronađena — eLokal' : 'Greška — eLokal' })

function handleError() {
  clearError({ redirect: '/' })
}
</script>

<template>
  <div class="min-h-screen bg-white flex flex-col">
    <!-- Header -->
    <header class="border-b border-gray-200 py-4">
      <div class="max-w-7xl mx-auto px-4">
        <NuxtLink to="/" class="text-2xl font-bold text-dark">eLokal</NuxtLink>
      </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-4">
      <div class="text-center max-w-lg">
        <!-- 404 -->
        <template v-if="is404">
          <p class="text-8xl font-bold text-primary-600 mb-4">404</p>
          <h1 class="text-2xl font-bold text-gray-800 mb-2">Stranica nije pronađena</h1>
          <p class="text-gray-500 mb-8">Stranica koju tražite ne postoji ili je premeštena.</p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <UiAtomsButton @click="handleError">Nazad na početnu</UiAtomsButton>
            <NuxtLink to="/products">
              <UiAtomsButton variant="outline">Pogledaj proizvode</UiAtomsButton>
            </NuxtLink>
          </div>
        </template>

        <!-- 500 / other -->
        <template v-else>
          <p class="text-8xl font-bold text-gray-300 mb-4">{{ error.statusCode }}</p>
          <h1 class="text-2xl font-bold text-gray-800 mb-2">Došlo je do greške</h1>
          <p class="text-gray-500 mb-4">Izvinjavamo se, nešto je pošlo naopako. Pokušajte ponovo.</p>
          <p class="text-sm text-gray-400 mb-8">Ako se problem nastavi, kontaktirajte nas na info@webshop.rs</p>
          <UiAtomsButton @click="handleError">Nazad na početnu</UiAtomsButton>
        </template>
      </div>
    </main>
  </div>
</template>
