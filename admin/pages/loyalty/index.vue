<script setup lang="ts">
const { get, getErrorMessage } = useApi()
const { error: toastError } = useToast()

interface LoyaltyAccount { id: number; user_id: number; points_balance: number; points_earned_total: number; tier: string; user?: { name: string; email: string } }

const accounts = ref<LoyaltyAccount[]>([])
const loading = ref(true)

async function fetchAccounts() {
  loading.value = true
  try {
    const data = await get<{ data: LoyaltyAccount[] }>('/admin/loyalty')
    accounts.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

const tierColors = { bronze: 'neutral', silver: 'info', gold: 'warning', platinum: 'success' } as const satisfies Record<string, 'neutral' | 'info' | 'warning' | 'success' | 'danger'>

onMounted(fetchAccounts)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Loyalty' }]" />
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Loyalty program</h1>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 4" :key="i" height="50px" /></div>
    <div v-else-if="!accounts.length" class="text-center py-16 text-gray-500">Nema loyalty naloga.</div>

    <table v-else class="w-full bg-white border border-gray-200 text-sm">
      <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
        <tr><th class="px-5 py-3">Korisnik</th><th class="px-5 py-3">Tier</th><th class="px-5 py-3 text-right">Balans</th><th class="px-5 py-3 text-right">Ukupno zarađeno</th></tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="a in accounts" :key="a.id">
          <td class="px-5 py-3"><span class="font-medium text-gray-800">{{ a.user?.name || '—' }}</span><br /><span class="text-xs text-gray-400">{{ a.user?.email }}</span></td>
          <td class="px-5 py-3"><UiAtomsBadge :variant="tierColors[a.tier as keyof typeof tierColors] ?? 'neutral'" class="text-[10px] capitalize">{{ a.tier }}</UiAtomsBadge></td>
          <td class="px-5 py-3 text-right font-medium">{{ a.points_balance }}</td>
          <td class="px-5 py-3 text-right text-gray-500">{{ a.points_earned_total }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
