<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

const authStore = useAuthStore()
const { apiBase } = useApi()

const form = reactive({
  name: authStore.user?.name || '',
  email: authStore.user?.email || '',
  phone: authStore.user?.phone || '',
})

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const saving = ref(false)
const savingPassword = ref(false)
const success = ref('')
const error = ref('')

async function updateProfile() {
  saving.value = true
  error.value = ''
  success.value = ''
  try {
    const user = await $fetch(`${apiBase}/v1/me`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}`, 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(form),
    })
    authStore.user = user as typeof authStore.user
    success.value = 'Profil ažuriran.'
  }
  catch (e: unknown) {
    error.value = (e as { data?: { message?: string } }).data?.message || 'Greška.'
  }
  finally { saving.value = false }
}

async function updatePassword() {
  savingPassword.value = true
  error.value = ''
  success.value = ''
  try {
    await $fetch(`${apiBase}/v1/me/password`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${authStore.token}`, 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(passwordForm),
    })
    success.value = 'Lozinka promenjena.'
    Object.assign(passwordForm, { current_password: '', password: '', password_confirmation: '' })
  }
  catch (e: unknown) {
    error.value = (e as { data?: { message?: string } }).data?.message || 'Greška.'
  }
  finally { savingPassword.value = false }
}

useHead({ title: 'Profil — eLokal' })
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: 'Moj nalog', to: '/account' }, { label: 'Profil' }]" />

    <div class="flex flex-col lg:flex-row gap-8">
      <LayoutAccountSidebar />

      <div class="flex-1 space-y-8">
        <div v-if="success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm">{{ success }}</div>
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ error }}</div>

        <!-- Profile -->
        <div class="border border-gray-200 p-6">
          <h2 class="text-lg font-bold text-gray-800 mb-4">Lični podaci</h2>
          <form @submit.prevent="updateProfile" class="space-y-4 max-w-md">
            <UiAtomsInput v-model="form.name" label="Ime i prezime" required />
            <UiAtomsInput v-model="form.email" label="Email" type="email" required />
            <UiAtomsInput v-model="form.phone" label="Telefon" type="tel" />
            <UiAtomsButton type="submit" :loading="saving">Sačuvaj</UiAtomsButton>
          </form>
        </div>

        <!-- Password -->
        <div class="border border-gray-200 p-6">
          <h2 class="text-lg font-bold text-gray-800 mb-4">Promena lozinke</h2>
          <form @submit.prevent="updatePassword" class="space-y-4 max-w-md">
            <UiAtomsInput v-model="passwordForm.current_password" label="Trenutna lozinka" type="password" required />
            <UiAtomsInput v-model="passwordForm.password" label="Nova lozinka" type="password" required />
            <UiAtomsInput v-model="passwordForm.password_confirmation" label="Potvrdite novu lozinku" type="password" required />
            <UiAtomsButton type="submit" :loading="savingPassword">Promeni lozinku</UiAtomsButton>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
