<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useHead({ title: 'Poeni — eLokal' })

const authStore = useAuthStore()
const { apiBase } = useApi()

interface LoyaltyData {
  points_balance: number
  points_earned_total: number
  points_spent_total: number
  tier: string
  transactions: Array<{ id: number; points: number; type: string; description: string | null; created_at: string }>
}

const loyalty = ref<LoyaltyData | null>(null)
const nextTier = ref<{ name: string; points_needed: number; threshold: number } | null>(null)
const loading = ref(true)

const tierLabels: Record<string, string> = { bronze: 'Bronze', silver: 'Silver', gold: 'Gold', platinum: 'Platinum' }
const tierColors: Record<string, string> = { bronze: 'text-amber-700', silver: 'text-gray-500', gold: 'text-yellow-500', platinum: 'text-purple-600' }

async function fetchLoyalty() {
  try {
    // Koristi admin API sa user ID — ili kreiraj public endpoint
    // Za sad prikazujemo placeholder ako nema podataka
    const headers = { Authorization: `Bearer ${authStore.token}`, Accept: 'application/json' }
    const data = await $fetch<{ data: LoyaltyData; next_tier: typeof nextTier.value }>(`${apiBase}/v1/me/loyalty`, { headers }).catch(() => null)
    if (data) {
      loyalty.value = data.data
      nextTier.value = data.next_tier
    }
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function formatDate(d: string): string {
  return new Date(d).toLocaleDateString('sr-Latn-RS', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(fetchLoyalty)
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: 'Moj nalog', to: '/account' }, { label: 'Poeni' }]" />

    <div class="flex flex-col lg:flex-row gap-8">
      <LayoutAccountSidebar />

      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Loyalty poeni</h1>

        <div v-if="loading" class="space-y-4">
          <UiAtomsSkeleton height="120px" />
          <UiAtomsSkeleton height="200px" />
        </div>

        <template v-else-if="loyalty">
          <!-- Overview -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="border border-gray-200 p-5 text-center">
              <p class="text-3xl font-bold text-primary-600">{{ loyalty.points_balance }}</p>
              <p class="text-sm text-gray-500 mt-1">Dostupni poeni</p>
            </div>
            <div class="border border-gray-200 p-5 text-center">
              <p class="text-3xl font-bold" :class="tierColors[loyalty.tier]">{{ tierLabels[loyalty.tier] }}</p>
              <p class="text-sm text-gray-500 mt-1">Vaš nivo</p>
            </div>
            <div class="border border-gray-200 p-5 text-center">
              <p class="text-3xl font-bold text-gray-800">{{ loyalty.points_earned_total }}</p>
              <p class="text-sm text-gray-500 mt-1">Ukupno zarađeno</p>
            </div>
          </div>

          <!-- Next tier progress -->
          <div v-if="nextTier" class="border border-gray-200 p-5 mb-8">
            <div class="flex justify-between text-sm mb-2">
              <span class="text-gray-600">Do nivoa <strong>{{ tierLabels[nextTier.name] }}</strong></span>
              <span class="text-gray-400">{{ nextTier.points_needed }} poena</span>
            </div>
            <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full bg-primary-500 rounded-full" :style="{ width: `${Math.min(100, ((nextTier.threshold - nextTier.points_needed) / nextTier.threshold) * 100)}%` }" />
            </div>
          </div>

          <!-- Transactions -->
          <h2 class="text-lg font-semibold text-gray-800 mb-3">Istorija</h2>
          <div v-if="loyalty.transactions?.length" class="border border-gray-200 divide-y divide-gray-100">
            <div v-for="tx in loyalty.transactions" :key="tx.id" class="flex justify-between px-4 py-3">
              <div>
                <p class="text-sm text-gray-800">{{ tx.description || tx.type }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(tx.created_at) }}</p>
              </div>
              <span class="text-sm font-semibold" :class="tx.points > 0 ? 'text-green-600' : 'text-red-500'">
                {{ tx.points > 0 ? '+' : '' }}{{ tx.points }}
              </span>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400">Nema transakcija.</p>
        </template>

        <div v-else class="text-center py-12 text-gray-500">
          <p>Loyalty program još nije aktiviran za vaš nalog.</p>
        </div>
      </div>
    </div>
  </div>
</template>
