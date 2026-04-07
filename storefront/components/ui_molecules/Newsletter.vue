<script setup lang="ts">
const email = ref('')
const submitted = ref(false)

function handleSubmit() {
  if (email.value.trim()) {
    submitted.value = true
    email.value = ''
    setTimeout(() => { submitted.value = false }, 3000)
  }
}
</script>

<template>
  <div class="bg-primary-50 py-12">
    <div class="max-w-xl mx-auto px-4 text-center">
      <h3 class="text-xl font-bold text-gray-800 mb-2">Prijavite se na newsletter</h3>
      <p class="text-sm text-gray-600 mb-4">Budite prvi koji saznaju o novim proizvodima i akcijama.</p>

      <form v-if="!submitted" @submit.prevent="handleSubmit" class="flex gap-2">
        <input
          v-model="email"
          type="email"
          placeholder="Vaša email adresa"
          required
          class="flex-1 px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
        />
        <UiAtomsButton type="submit">Prijavi se</UiAtomsButton>
      </form>

      <p v-else class="text-green-600 font-medium">Hvala na prijavi!</p>
    </div>
  </div>
</template>
