<script setup lang="ts">
import type { Order } from '~/types'

const route = useRoute()
const authStore = useAuthStore()
const { apiBase } = useApi()

const order = ref<Order | null>(null)
const loading = ref(true)
const error = ref(false)
const orderNumber = route.params.number as string

async function fetchOrder() {
  if (!authStore.isLoggedIn) {
    error.value = true
    loading.value = false
    return
  }
  try {
    const data = await $fetch<{ data: Order }>(`${apiBase}/v1/orders/${orderNumber}`, {
      headers: { Authorization: `Bearer ${authStore.token}`, Accept: 'application/json' },
    })
    order.value = data.data
  }
  catch { error.value = true }
  finally { loading.value = false }
}

onMounted(fetchOrder)
useHead({ title: `Praćenje ${orderNumber} — eLokal` })
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Praćenje narudžbine</h1>

    <div v-if="loading" class="flex justify-center py-12">
      <UiAtomsSpinner size="lg" />
    </div>

    <div v-else-if="error || !order" class="text-center py-12">
      <p class="text-gray-500 mb-4">Narudžbina nije pronađena ili nemate pristup.</p>
      <NuxtLink to="/">
        <UiAtomsButton variant="outline">Nazad na početnu</UiAtomsButton>
      </NuxtLink>
    </div>

    <template v-else>
      <div class="border border-gray-200 p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
          <p class="text-lg font-semibold text-gray-800">{{ order.order_number }}</p>
          <span class="text-sm px-3 py-1 rounded-full bg-primary-100 text-primary-700 font-medium">{{ order.status }}</span>
        </div>

        <!-- Tracking info -->
        <div v-if="order.tracking?.number" class="bg-blue-50 border border-blue-200 p-4 mb-4">
          <p class="text-sm font-medium text-blue-800">{{ order.tracking.carrier || 'Kurir' }}: {{ order.tracking.number }}</p>
          <a
            v-if="order.tracking.url"
            :href="order.tracking.url"
            target="_blank"
            rel="noopener noreferrer"
            class="text-sm text-blue-600 hover:text-blue-800 underline mt-1 inline-block"
          >
            Prati pošiljku na sajtu kurira →
          </a>
        </div>

        <div v-else class="text-sm text-gray-500">
          Tracking informacije još nisu dostupne. Bićete obavešteni kada narudžbina bude poslata.
        </div>
      </div>

      <NuxtLink to="/account/orders" class="text-sm text-primary-600 hover:underline">
        ← Sve narudžbine
      </NuxtLink>
    </template>
  </div>
</template>
