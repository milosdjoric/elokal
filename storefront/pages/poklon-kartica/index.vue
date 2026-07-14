<script setup lang="ts">
const { apiBase } = useApi()
const { isFeatureEnabled } = useFeature()
if (!isFeatureEnabled(FEATURES.giftCards)) {
  throw createError({ statusCode: 404, statusMessage: 'Stranica nije pronađena' })
}

const amounts = [1000, 2000, 3000, 5000, 10000]
const selectedAmount = ref<number | null>(null)
const customAmount = ref('')
const useCustom = ref(false)

const recipientEmail = ref('')
const recipientName = ref('')
const message = ref('')

const loading = ref(false)
const success = ref(false)
const result = ref<{ code: string; amount: string; recipient_email: string } | null>(null)
const error = ref('')

const finalAmount = computed(() => {
  if (useCustom.value) return parseInt(customAmount.value) || 0
  return selectedAmount.value ?? 0
})

function selectAmount(amount: number) {
  selectedAmount.value = amount
  useCustom.value = false
  customAmount.value = ''
}

function selectCustom() {
  useCustom.value = true
  selectedAmount.value = null
}

async function handleSubmit() {
  if (finalAmount.value < 500) {
    error.value = 'Minimalni iznos je 500 RSD.'
    return
  }
  loading.value = true
  error.value = ''
  try {
    const data = await $fetch<{ data: { code: string; amount: string; recipient_email: string }; message: string }>(`${apiBase}/v1/gift-card/purchase`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        amount: finalAmount.value,
        recipient_email: recipientEmail.value,
        recipient_name: recipientName.value,
        message: message.value || undefined,
      }),
    })
    result.value = data.data
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

useHead({ title: 'Poklon kartica — eLokal' })
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 py-16">
    <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center">Poklon kartica</h1>
    <p class="text-sm text-gray-500 mb-8 text-center">Izaberite iznos i obradujte nekoga posebnom porukom.</p>

    <!-- Success -->
    <div v-if="success && result" class="bg-green-50 border border-green-200 p-8 text-center">
      <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
      </div>
      <h2 class="text-xl font-bold text-gray-800 mb-2">Poklon kartica je kreirana!</h2>
      <p class="text-sm text-gray-500 mb-4">Kod kartice je poslat na {{ result.recipient_email }}.</p>
      <div class="bg-white border border-gray-200 p-4 inline-block">
        <p class="text-xs text-gray-400 mb-1">Kod kartice</p>
        <p class="text-2xl font-mono font-bold text-gray-800 tracking-widest">{{ result.code }}</p>
      </div>
      <div class="mt-6">
        <NuxtLink to="/" class="text-sm text-primary-600 hover:text-primary-700">← Nastavi sa kupovinom</NuxtLink>
      </div>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="handleSubmit" class="space-y-8">
      <!-- Iznos -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">Izaberite iznos</label>
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
          <button
            v-for="amount in amounts"
            :key="amount"
            type="button"
            class="py-3 text-sm font-medium border transition-colors"
            :class="selectedAmount === amount && !useCustom
              ? 'border-primary-600 bg-primary-50 text-primary-700'
              : 'border-gray-300 text-gray-700 hover:border-gray-400'"
            @click="selectAmount(amount)"
          >
            {{ amount.toLocaleString('sr-RS') }}
          </button>
        </div>
        <div class="mt-3">
          <button
            type="button"
            class="text-sm text-primary-600 hover:text-primary-700"
            @click="selectCustom"
          >
            Drugi iznos
          </button>
          <input
            v-if="useCustom"
            v-model="customAmount"
            type="number"
            min="500"
            max="50000"
            placeholder="Unesite iznos (min. 500 RSD)"
            class="mt-2 w-full px-4 py-3 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
        </div>
      </div>

      <!-- Primalac -->
      <div class="space-y-4">
        <h3 class="text-sm font-medium text-gray-700">Podaci o primaocu</h3>
        <UiAtomsInput v-model="recipientName" label="Ime primaoca" required />
        <UiAtomsInput v-model="recipientEmail" label="Email primaoca" type="email" required />
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Poruka (opciono)</label>
          <textarea
            v-model="message"
            rows="3"
            maxlength="1000"
            placeholder="Napišite ličnu poruku..."
            class="w-full px-4 py-3 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
          />
        </div>
      </div>

      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

      <!-- Rezime -->
      <div v-if="finalAmount >= 500" class="bg-gray-50 border border-gray-200 p-4 flex items-center justify-between">
        <span class="text-sm text-gray-600">Iznos poklon kartice:</span>
        <span class="text-lg font-bold text-gray-800">{{ finalAmount.toLocaleString('sr-RS') }} RSD</span>
      </div>

      <UiAtomsButton type="submit" :loading="loading" :disabled="finalAmount < 500" class="w-full">
        Kupi poklon karticu
      </UiAtomsButton>
    </form>

    <div class="mt-8 text-center">
      <NuxtLink to="/poklon-kartica/provera" class="text-sm text-gray-500 hover:text-gray-700">
        Imate poklon karticu? Proverite stanje →
      </NuxtLink>
    </div>
  </div>
</template>
