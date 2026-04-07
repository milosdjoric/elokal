<script setup lang="ts">
const props = defineProps<{
  productId?: number
  productName?: string
}>()

const { apiBase } = useApi()
const authStore = useAuthStore()

const show = ref(false)
const loading = ref(false)
const success = ref(false)
const error = ref('')

const form = reactive({
  name: authStore.user?.name || '',
  phone: '',
  channel: 'call' as 'call' | 'sms' | 'whatsapp',
  message: '',
})

const channelLabels: Record<string, string> = {
  call: 'Poziv',
  sms: 'SMS',
  whatsapp: 'WhatsApp',
}

async function handleSubmit() {
  loading.value = true
  error.value = ''
  try {
    await $fetch(`${apiBase}/v1/callback-request`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        product_id: props.productId || undefined,
        ...form,
      }),
    })
    success.value = true
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    error.value = err.data?.message || 'Došlo je do greške.'
  }
  finally { loading.value = false }
}

function open() {
  success.value = false
  error.value = ''
  if (authStore.user?.name) form.name = authStore.user.name
  show.value = true
}

function close() {
  show.value = false
}
</script>

<template>
  <div>
    <button
      type="button"
      class="flex items-center gap-2 text-sm text-primary-600 hover:text-primary-800 font-medium"
      @click="open"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
      </svg>
      Zatraži poziv
    </button>

    <UiMoleculesModal v-model="show" :title="productName ? `Poziv za: ${productName}` : 'Zatražite poziv'">
      <div v-if="success" class="text-center py-4">
        <svg class="mx-auto w-12 h-12 text-green-500 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-gray-800 font-medium">Zahtev primljen!</p>
        <p class="text-sm text-gray-500 mt-1">Kontaktiraćemo vas u najkraćem roku.</p>
        <UiAtomsButton class="mt-4" variant="secondary" @click="close">Zatvori</UiAtomsButton>
      </div>

      <form v-else @submit.prevent="handleSubmit" class="space-y-4">
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ error }}</div>

        <UiAtomsInput v-model="form.name" label="Ime" required />
        <UiAtomsInput v-model="form.phone" label="Telefon" type="tel" required placeholder="+381..." />

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Kanal komunikacije</label>
          <div class="flex gap-3">
            <label
              v-for="(label, key) in channelLabels"
              :key="key"
              class="flex items-center gap-2 cursor-pointer text-sm"
            >
              <input
                v-model="form.channel"
                type="radio"
                :value="key"
                class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500"
              />
              {{ label }}
            </label>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Poruka (opciono)</label>
          <textarea
            v-model="form.message"
            rows="2"
            placeholder="Dodatne napomene..."
            class="w-full px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
        </div>
      </form>

      <template v-if="!success" #footer>
        <UiAtomsButton variant="secondary" @click="close">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="loading" @click="handleSubmit">Pošalji zahtev</UiAtomsButton>
      </template>
    </UiMoleculesModal>
  </div>
</template>
