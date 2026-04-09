<script setup lang="ts">
const { get, getErrorMessage } = useApi()
const { error: toastError } = useToast()

interface Currency { id: number; code: string; name: string; symbol: string; rate: string; is_active: boolean }

const currencies = ref<Currency[]>([])
const loading = ref(true)

async function fetchCurrencies() {
  loading.value = true
  try {
    const data = await get<{ data: Currency[] }>('/admin/currencies')
    currencies.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

onMounted(fetchCurrencies)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Valute' }]" />
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Valute</h1>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 3" :key="i" height="50px" /></div>
    <div v-else-if="!currencies.length" class="text-center py-16 text-gray-500">Nema valuta.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="c in currencies" :key="c.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="font-medium text-gray-800">{{ c.name }} <span class="text-gray-400">({{ c.symbol }})</span></p>
          <span class="text-xs text-gray-500">{{ c.code }} — Kurs: {{ c.rate }}</span>
        </div>
        <UiAtomsBadge :variant="c.is_active ? 'success' : 'neutral'" class="text-[10px]">{{ c.is_active ? 'Aktivna' : 'Neaktivna' }}</UiAtomsBadge>
      </div>
    </div>
  </div>
</template>
