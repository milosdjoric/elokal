<script setup lang="ts">
definePageMeta({ layout: false })

const { login } = useAuth()
const { getErrorMessage, getValidationErrors } = useApi()

const form = reactive({
  email: '',
  password: '',
})

const loading = ref(false)
const error = ref('')
const validationErrors = ref<Record<string, string[]>>({})

async function handleSubmit() {
  loading.value = true
  error.value = ''
  validationErrors.value = {}

  try {
    await login(form)
  }
  catch (e) {
    error.value = getErrorMessage(e)
    validationErrors.value = getValidationErrors(e)
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-8">
          eLokal Admin
        </h1>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
            {{ error }}
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
              Email
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autofocus
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :class="{ 'border-red-500': validationErrors.email }"
              placeholder="admin@webshop.test"
            />
            <p v-if="validationErrors.email" class="mt-1 text-sm text-red-600">
              {{ validationErrors.email[0] }}
            </p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
              Lozinka
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :class="{ 'border-red-500': validationErrors.password }"
              placeholder="••••••••"
            />
            <p v-if="validationErrors.password" class="mt-1 text-sm text-red-600">
              {{ validationErrors.password[0] }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg
              v-if="loading"
              class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ loading ? 'Prijavljivanje...' : 'Prijavi se' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
