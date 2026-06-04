<script setup lang="ts">
const { apiBase } = useApi()

const code = ref('')
const loading = ref(false)
const result = ref<{ code: string; balance: string } | null>(null)
const error = ref('')

async function checkBalance() {
  if (!code.value.trim()) return
  loading.value = true
  error.value = ''
  result.value = null
  try {
    const data = await $fetch<{ data: { code: string; balance: string } }>(`${apiBase}/v1/gift-card/check`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ code: code.value }),
    })
    result.value = data.data
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    error.value = err.data?.message || 'Kartica nije pronađena.'
  }
  finally { loading.value = false }
}

useHead({ title: 'Provera poklon kartice — sloj kolektiv' })
</script>

<template>
  <div class="max-w-lg mx-auto px-4 py-16">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Provera poklon kartice</h1>

    <form @submit.prevent="checkBalance" class="space-y-4">
      <UiAtomsInput v-model="code" label="Kod kartice" placeholder="XXXX-XXXX-XXXX" required />
      <UiAtomsButton type="submit" :loading="loading" class="w-full">Proveri stanje</UiAtomsButton>
    </form>

    <div v-if="result" class="mt-6 bg-green-50 border border-green-200 p-6 text-center">
      <p class="text-sm text-green-600 mb-1">Kartica: {{ result.code }}</p>
      <p class="text-3xl font-bold text-green-800">{{ parseFloat(result.balance).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</p>
    </div>

    <div v-if="error" class="mt-6 bg-red-50 border border-red-200 p-4 text-center text-sm text-red-700">
      {{ error }}
    </div>
  </div>
</template>
