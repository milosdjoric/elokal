<script setup lang="ts">
const { apiBase } = useApi()
const email = ref('')
const loading = ref(false)
const message = ref('')
const isError = ref(false)

async function handleSubmit() {
  if (!email.value.trim()) return
  loading.value = true
  message.value = ''
  isError.value = false
  try {
    const data = await $fetch<{ message: string }>(`${apiBase}/v1/newsletter/subscribe`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email: email.value, source: 'footer' }),
    })
    message.value = data.message
    email.value = ''
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    message.value = err.data?.message || 'Došlo je do greške.'
    isError.value = true
  }
  finally { loading.value = false }
}
</script>

<template>
  <div class="bg-primary-50 py-12">
    <div class="max-w-xl mx-auto px-4 text-center">
      <h3 class="text-xl font-bold text-gray-800 mb-2">Prijavite se na newsletter</h3>
      <p class="text-sm text-gray-600 mb-4">Budite prvi koji saznaju o novim proizvodima i akcijama.</p>

      <form v-if="!message || isError" @submit.prevent="handleSubmit" class="flex gap-2">
        <input
          v-model="email"
          type="email"
          placeholder="Vaša email adresa"
          required
          class="flex-1 px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
        />
        <UiAtomsButton type="submit" :loading="loading">Prijavi se</UiAtomsButton>
      </form>

      <p v-if="message" class="mt-2 text-sm font-medium" :class="isError ? 'text-red-600' : 'text-green-600'">
        {{ message }}
      </p>
    </div>
  </div>
</template>
