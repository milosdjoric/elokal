<script setup lang="ts">
const { get, patch, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface CallbackRequest { id: number; product_name: string; phone: string; channel: string; status: string; created_at: string }

const requests = ref<CallbackRequest[]>([])
const loading = ref(true)

async function fetchRequests() {
  loading.value = true
  try { const data = await get<{ data: CallbackRequest[] }>('/admin/callback-requests'); requests.value = data.data }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function updateStatus(id: number, status: string) {
  try { await patch(`/admin/callback-requests/${id}`, { status }); success('Status ažuriran.'); fetchRequests() }
  catch (e) { toastError(getErrorMessage(e)) }
}

function statusVariant(s: string) { return ({ pending: 'warning', contacted: 'info', completed: 'success' } as const)[s] ?? 'neutral' as const }
function channelLabel(c: string) { return { call: 'Poziv', sms: 'SMS', whatsapp: 'WhatsApp' }[c] || c }

function formatDate(d: string) {
  return new Date(d).toLocaleString('sr-RS', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(fetchRequests)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Callback zahtevi' }]" />
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Callback zahtevi</h1>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 4" :key="i" height="50px" /></div>
    <div v-else-if="!requests.length" class="text-center py-16 text-gray-500">Nema zahteva.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="r in requests" :key="r.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="text-sm font-medium text-gray-800">{{ r.phone }} <span class="text-xs text-gray-400">({{ channelLabel(r.channel) }})</span></p>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="text-xs text-gray-500">{{ r.product_name }}</span>
            <UiAtomsBadge :variant="statusVariant(r.status)" class="text-[10px]">{{ r.status }}</UiAtomsBadge>
            <span class="text-xs text-gray-400">{{ formatDate(r.created_at) }}</span>
          </div>
        </div>
        <div class="flex gap-2">
          <UiAtomsButton v-if="r.status === 'pending'" variant="ghost" size="sm" @click="updateStatus(r.id, 'contacted')">Kontaktiran</UiAtomsButton>
          <UiAtomsButton v-if="r.status === 'contacted'" variant="ghost" size="sm" @click="updateStatus(r.id, 'completed')">Završeno</UiAtomsButton>
        </div>
      </div>
    </div>
  </div>
</template>
