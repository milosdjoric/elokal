<script setup lang="ts">
const { get, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface Subscriber { id: number; email: string; status: string; source: string; confirmed_at: string | null; created_at: string }
interface Stats { total: number; confirmed: number; pending: number; unsubscribed: number }

const subscribers = ref<Subscriber[]>([])
const stats = ref<Stats | null>(null)
const loading = ref(true)

async function fetchData() {
  loading.value = true
  try {
    const [subData, statsData] = await Promise.all([
      get<{ data: Subscriber[] }>('/admin/newsletter'),
      get<Stats>('/admin/newsletter/stats'),
    ])
    subscribers.value = subData.data
    stats.value = statsData
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function deleteSubscriber(id: number) {
  if (!confirm('Obrisati pretplatnika?')) return
  try { await del(`/admin/newsletter/${id}`); success('Obrisan.'); fetchData() }
  catch (e) { toastError(getErrorMessage(e)) }
}

function statusVariant(s: string) { return ({ confirmed: 'success', pending: 'warning', unsubscribed: 'neutral' } as const)[s] ?? 'neutral' as const }

onMounted(fetchData)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Newsletter' }]" />
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Newsletter pretplatnici</h1>

    <div v-if="stats" class="grid grid-cols-4 gap-4 mb-6">
      <div class="bg-white border border-gray-200 p-4 text-center"><p class="text-2xl font-bold">{{ stats.total }}</p><p class="text-xs text-gray-500">Ukupno</p></div>
      <div class="bg-white border border-gray-200 p-4 text-center"><p class="text-2xl font-bold text-green-600">{{ stats.confirmed }}</p><p class="text-xs text-gray-500">Potvrđeni</p></div>
      <div class="bg-white border border-gray-200 p-4 text-center"><p class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</p><p class="text-xs text-gray-500">Na čekanju</p></div>
      <div class="bg-white border border-gray-200 p-4 text-center"><p class="text-2xl font-bold text-gray-400">{{ stats.unsubscribed }}</p><p class="text-xs text-gray-500">Odjavljeni</p></div>
    </div>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 5" :key="i" height="45px" /></div>
    <div v-else-if="!subscribers.length" class="text-center py-16 text-gray-500">Nema pretplatnika.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="s in subscribers" :key="s.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="text-sm font-medium text-gray-800">{{ s.email }}</p>
          <div class="flex items-center gap-2 mt-0.5">
            <UiAtomsBadge :variant="statusVariant(s.status)" class="text-[10px]">{{ s.status }}</UiAtomsBadge>
            <span class="text-xs text-gray-400">{{ s.source }}</span>
          </div>
        </div>
        <UiAtomsButton variant="ghost" size="sm" @click="deleteSubscriber(s.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
      </div>
    </div>
  </div>
</template>
