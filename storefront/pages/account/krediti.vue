<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Krediti — eLokal' })

const authStore = useAuthStore()
const { apiBase } = useApi()

interface CreditData {
  balance: string
  transactions: Array<{ id: number; amount: string; type: string; reason: string | null; balance_after: string; created_at: string }>
}

const credits = ref<CreditData | null>(null)
const loading = ref(true)

async function fetchCredits() {
  try {
    const headers = { Authorization: `Bearer ${authStore.token}`, Accept: 'application/json' }
    const data = await $fetch<{ data: CreditData }>(`${apiBase}/v1/me/credits`, { headers }).catch(() => null)
    if (data) credits.value = data.data
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function formatDate(d: string): string {
  return new Date(d).toLocaleDateString('sr-Latn-RS', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(fetchCredits)
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: 'Moj nalog', to: '/account' }, { label: 'Krediti' }]" />

    <div class="flex flex-col lg:flex-row gap-8">
      <LayoutAccountSidebar />

      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Store krediti</h1>

        <div v-if="loading">
          <UiAtomsSkeleton height="120px" />
        </div>

        <template v-else-if="credits">
          <div class="border border-gray-200 p-6 text-center mb-8">
            <p class="text-4xl font-bold text-primary-600">{{ parseFloat(credits.balance).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</p>
            <p class="text-sm text-gray-500 mt-1">Dostupni krediti</p>
          </div>

          <h2 class="text-lg font-semibold text-gray-800 mb-3">Istorija</h2>
          <div v-if="credits.transactions?.length" class="border border-gray-200 divide-y divide-gray-100">
            <div v-for="tx in credits.transactions" :key="tx.id" class="flex justify-between px-4 py-3">
              <div>
                <p class="text-sm text-gray-800">{{ tx.reason || tx.type }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(tx.created_at) }}</p>
              </div>
              <div class="text-right">
                <span class="text-sm font-semibold" :class="parseFloat(tx.amount) > 0 ? 'text-green-600' : 'text-red-500'">
                  {{ parseFloat(tx.amount) > 0 ? '+' : '' }}{{ parseFloat(tx.amount).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD
                </span>
                <p class="text-xs text-gray-400">Stanje: {{ parseFloat(tx.balance_after).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }}</p>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400">Nema transakcija.</p>
        </template>

        <div v-else class="text-center py-12 text-gray-500">
          <p>Nemate store kredite.</p>
        </div>
      </div>
    </div>
  </div>
</template>
