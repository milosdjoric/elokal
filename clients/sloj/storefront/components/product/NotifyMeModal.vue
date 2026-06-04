<script setup lang="ts">
import type { Product } from '~/types'

const props = defineProps<{
  modelValue: boolean
  product: Product | null
}>()

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()

const { apiBase } = useApi()
const authStore = useAuthStore()

const email = ref('')
const loading = ref(false)
const success = ref(false)
const error = ref('')

watch(() => props.modelValue, (open) => {
  if (open) {
    email.value = authStore.user?.email || ''
    success.value = false
    error.value = ''
  }
})

async function handleSubmit() {
  if (!props.product) return
  if (!email.value || !email.value.includes('@')) {
    error.value = 'Unesite ispravnu email adresu.'
    return
  }
  loading.value = true
  error.value = ''
  try {
    await $fetch(`${apiBase}/v1/products/${props.product.id}/notify-me`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email: email.value }),
    })
    success.value = true
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    error.value = err.data?.message || 'Greška. Pokušajte ponovo.'
  }
  finally { loading.value = false }
}

function close() {
  emit('update:modelValue', false)
}
</script>

<template>
  <UiMoleculesModal
    :model-value="modelValue"
    :title="product ? `Obaveštenje o dostupnosti` : ''"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <div v-if="success" class="text-center py-4">
      <Icon name="lucide:check-circle-2" class="mx-auto w-12 h-12 text-success mb-3" :stroke-width="1.5" />
      <p class="text-gray-900 font-semibold">Hvala!</p>
      <p class="text-sm text-gray-600 mt-1">Poslaćemo vam email čim proizvod ponovo bude na stanju.</p>
      <UiAtomsButton class="mt-4" variant="secondary" @click="close">Zatvori</UiAtomsButton>
    </div>

    <form v-else class="space-y-4" @submit.prevent="handleSubmit">
      <p v-if="product" class="text-sm text-gray-600 leading-relaxed">
        Obavestićemo vas čim <span class="font-semibold text-gray-900">{{ product.name }}</span> ponovo bude na stanju.
      </p>

      <div v-if="error" class="bg-danger-soft border border-danger text-danger px-4 py-3 text-sm">{{ error }}</div>

      <UiAtomsInput
        v-model="email"
        label="Vaša email adresa"
        type="email"
        required
        placeholder="ime@primer.rs"
      />

      <p class="text-[11px] text-gray-500 leading-relaxed">
        Email koristimo isključivo za jednokratno obaveštenje. Možete se odjaviti u svakom trenutku.
      </p>
    </form>

    <template v-if="!success" #footer>
      <UiAtomsButton variant="secondary" @click="close">Otkaži</UiAtomsButton>
      <UiAtomsButton :loading="loading" @click="handleSubmit">Obavesti me</UiAtomsButton>
    </template>
  </UiMoleculesModal>
</template>
