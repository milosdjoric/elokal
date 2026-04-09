<script setup lang="ts">
const { get, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface Coupon { id: number; code: string; type: string; value: string; times_used: number; max_uses: number | null; is_active: boolean; starts_at: string | null; expires_at: string | null }

const coupons = ref<Coupon[]>([])
const loading = ref(true)

async function fetchCoupons() {
  loading.value = true
  try {
    const data = await get<{ data: Coupon[] }>('/admin/coupons')
    coupons.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function deleteCoupon(id: number) {
  if (!confirm('Obrisati kupon?')) return
  try {
    await del(`/admin/coupons/${id}`)
    success('Kupon obrisan.')
    fetchCoupons()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

function typeLabel(type: string) {
  return { percentage: 'Procenat', fixed_amount: 'Fiksno', free_shipping: 'Bespl. dostava' }[type] || type
}

onMounted(fetchCoupons)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Kuponi' }]" />
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Kuponi</h1>
    </div>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 4" :key="i" height="50px" /></div>

    <div v-else-if="!coupons.length" class="text-center py-16 text-gray-500">Nema kupona.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="c in coupons" :key="c.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="font-mono font-bold text-gray-800">{{ c.code }}</p>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="text-xs text-gray-500">{{ typeLabel(c.type) }}: {{ c.value }}{{ c.type === 'percentage' ? '%' : ' RSD' }}</span>
            <span class="text-xs text-gray-400">Korišćen: {{ c.times_used }}{{ c.max_uses ? `/${c.max_uses}` : '' }}</span>
            <UiAtomsBadge :variant="c.is_active ? 'success' : 'neutral'" class="text-[10px]">{{ c.is_active ? 'Aktivan' : 'Neaktivan' }}</UiAtomsBadge>
          </div>
        </div>
        <UiAtomsButton variant="ghost" size="sm" @click="deleteCoupon(c.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
      </div>
    </div>
  </div>
</template>
