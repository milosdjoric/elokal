<script setup lang="ts">
const { get, getErrorMessage } = useApi()
const { error: toastError } = useToast()

interface AbandonedCart { id: number; email: string; total: string; status: string; emails_sent: number; created_at: string }
interface Stats { abandoned: number; recovered: number; recovery_rate: number; total_value_abandoned: number; total_value_recovered: number }

const carts = ref<AbandonedCart[]>([])
const stats = ref<Stats | null>(null)
const loading = ref(true)

async function fetchData() {
  loading.value = true
  try {
    const [cartData, statsData] = await Promise.all([
      get<{ data: AbandonedCart[] }>('/admin/abandoned-carts'),
      get<Stats>('/admin/abandoned-carts/stats'),
    ])
    carts.value = cartData.data
    stats.value = statsData
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function statusVariant(s: string) { return ({ abandoned: 'warning', recovered: 'success', expired: 'neutral' } as const)[s] ?? 'neutral' as const }

onMounted(fetchData)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Napuštene korpe' }]" />
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Napuštene korpe</h1>

    <div v-if="stats" class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-white border border-gray-200 p-4 text-center"><p class="text-2xl font-bold text-yellow-600">{{ stats.abandoned }}</p><p class="text-xs text-gray-500">Napuštene</p></div>
      <div class="bg-white border border-gray-200 p-4 text-center"><p class="text-2xl font-bold text-green-600">{{ stats.recovered }}</p><p class="text-xs text-gray-500">Oporavljene</p></div>
      <div class="bg-white border border-gray-200 p-4 text-center"><p class="text-2xl font-bold">{{ stats.recovery_rate }}%</p><p class="text-xs text-gray-500">Recovery rate</p></div>
    </div>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 5" :key="i" height="45px" /></div>
    <div v-else-if="!carts.length" class="text-center py-16 text-gray-500">Nema napuštenih korpi.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="c in carts" :key="c.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="text-sm font-medium text-gray-800">{{ c.email }}</p>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="text-xs text-gray-500">{{ parseFloat(c.total).toLocaleString('sr-RS') }} RSD</span>
            <span class="text-xs text-gray-400">Emailova: {{ c.emails_sent }}</span>
            <UiAtomsBadge :variant="statusVariant(c.status)" class="text-[10px]">{{ c.status }}</UiAtomsBadge>
          </div>
        </div>
        <span class="text-xs text-gray-400">{{ new Date(c.created_at).toLocaleDateString('sr-RS') }}</span>
      </div>
    </div>
  </div>
</template>
