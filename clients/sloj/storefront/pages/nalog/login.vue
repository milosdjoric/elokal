<script setup lang="ts">
const { login } = useAuth()

const form = reactive({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

async function handleSubmit() {
  loading.value = true
  error.value = ''
  try {
    await login(form.email, form.password)
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    error.value = err.data?.message || 'Došlo je do greške.'
  }
  finally {
    loading.value = false
  }
}

useHead({ title: 'Prijava — sloj kolektiv' })
</script>

<template>
  <div class="max-w-md mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Prijavite se</h1>

    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm mb-6">
      {{ error }}
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <UiAtomsInput v-model="form.email" label="Email" type="email" required placeholder="vas@email.com" />
      <UiAtomsInput v-model="form.password" label="Lozinka" type="password" required />

      <UiAtomsButton type="submit" :loading="loading" class="w-full" size="lg">Prijavi se</UiAtomsButton>
    </form>

    <div class="mt-6 text-center text-sm text-gray-500 space-y-2">
      <p>
        <NuxtLink to="/nalog/forgot-password" class="text-primary-600 hover:underline">Zaboravili ste lozinku?</NuxtLink>
      </p>
      <p>
        Nemate nalog?
        <NuxtLink to="/nalog/register" class="text-primary-600 font-medium hover:underline">Registrujte se</NuxtLink>
      </p>
    </div>
  </div>
</template>
