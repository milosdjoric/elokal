<script setup lang="ts">
const { get: _get, apiBase } = useApi()

const email = ref('')
const loading = ref(false)
const sent = ref(false)
const error = ref('')

async function handleSubmit() {
  loading.value = true
  error.value = ''
  try {
    await $fetch(`${apiBase}/v1/forgot-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email: email.value }),
    })
    sent.value = true
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    error.value = err.data?.message || 'Došlo je do greške.'
  }
  finally {
    loading.value = false
  }
}

useHead({ title: 'Zaboravljena lozinka — sloj kolektiv' })
</script>

<template>
  <div class="max-w-md mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center">Zaboravili ste lozinku?</h1>
    <p class="text-sm text-gray-500 text-center mb-6">Unesite email adresu i poslaćemo vam link za reset lozinke.</p>

    <template v-if="!sent">
      <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm mb-6">
        {{ error }}
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <UiAtomsInput v-model="email" label="Email" type="email" required placeholder="vas@email.com" />
        <UiAtomsButton type="submit" :loading="loading" class="w-full" size="lg">Pošalji link</UiAtomsButton>
      </form>
    </template>

    <div v-else class="text-center py-8">
      <Icon name="lucide:check-circle-2" class="mx-auto w-16 h-16 text-success mb-4" :stroke-width="1.5" />
      <p class="text-gray-700 font-medium mb-2">Email poslat!</p>
      <p class="text-sm text-gray-500">Proverite vaš inbox za link za reset lozinke.</p>
    </div>

    <p class="mt-6 text-center text-sm text-gray-500">
      <NuxtLink to="/nalog/login" class="text-primary-600 hover:underline">← Nazad na prijavu</NuxtLink>
    </p>
  </div>
</template>
