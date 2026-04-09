<script setup lang="ts">
const { apiBase } = useApi()

const form = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
})

const loading = ref(false)
const success = ref(false)
const error = ref('')

async function handleSubmit() {
  loading.value = true
  error.value = ''
  try {
    await $fetch(`${apiBase}/v1/contact`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(form),
    })
    success.value = true
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string; errors?: Record<string, string[]> } }
    if (err.data?.errors) {
      error.value = Object.values(err.data.errors).flat().join(' ')
    } else {
      error.value = err.data?.message || 'Došlo je do greške.'
    }
  }
  finally { loading.value = false }
}
</script>

<template>
  <div class="max-w-xl">
    <div v-if="success" class="bg-green-50 border border-green-200 p-6 text-center">
      <h3 class="text-lg font-bold text-green-800 mb-2">Poruka poslata!</h3>
      <p class="text-sm text-green-600">Hvala na poruci. Odgovorićemo vam u najkraćem roku.</p>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="space-y-4">
      <UiAtomsInput v-model="form.name" label="Ime i prezime" required />
      <UiAtomsInput v-model="form.email" label="Email" type="email" required />
      <UiAtomsInput v-model="form.phone" label="Telefon (opciono)" />
      <UiAtomsInput v-model="form.subject" label="Tema (opciono)" />
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Poruka</label>
        <textarea
          v-model="form.message"
          rows="5"
          required
          maxlength="5000"
          class="w-full px-4 py-3 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
          placeholder="Napišite vašu poruku..."
        />
      </div>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <UiAtomsButton type="submit" :loading="loading">Pošalji poruku</UiAtomsButton>
    </form>
  </div>
</template>
