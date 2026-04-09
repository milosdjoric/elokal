<script setup lang="ts">
const { register } = useAuth()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  phone: '',
})

const loading = ref(false)
const errors = ref<Record<string, string[]>>({})

async function handleSubmit() {
  loading.value = true
  errors.value = {}
  try {
    await register(form.name, form.email, form.password, form.password_confirmation, form.phone || undefined)
  }
  catch (e: unknown) {
    const err = e as { data?: { errors?: Record<string, string[]>; message?: string } }
    errors.value = err.data?.errors || {}
  }
  finally {
    loading.value = false
  }
}

function fieldError(field: string): string {
  return errors.value[field]?.[0] || ''
}

useHead({ title: 'Registracija — eLokal' })
</script>

<template>
  <div class="max-w-md mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Kreirajte nalog</h1>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <UiAtomsInput v-model="form.name" label="Ime i prezime" required :error="fieldError('name')" />
      <UiAtomsInput v-model="form.email" label="Email" type="email" required :error="fieldError('email')" />
      <UiAtomsInput v-model="form.phone" label="Telefon" type="tel" placeholder="+381..." :error="fieldError('phone')" />
      <UiAtomsInput v-model="form.password" label="Lozinka" type="password" required :error="fieldError('password')" />
      <UiAtomsInput v-model="form.password_confirmation" label="Potvrdite lozinku" type="password" required />

      <UiAtomsButton type="submit" :loading="loading" class="w-full" size="lg">Registruj se</UiAtomsButton>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
      Već imate nalog?
      <NuxtLink to="/nalog/login" class="text-primary-600 font-medium hover:underline">Prijavite se</NuxtLink>
    </p>
  </div>
</template>
