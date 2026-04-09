<script setup lang="ts">
const { get, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface Webhook { id: number; url: string; events: string[]; is_active: boolean; created_at: string }

const webhooks = ref<Webhook[]>([])
const loading = ref(true)

async function fetchWebhooks() {
  loading.value = true
  try { const data = await get<{ data: Webhook[] }>('/admin/webhooks'); webhooks.value = data.data }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function deleteWebhook(id: number) {
  if (!confirm('Obrisati webhook?')) return
  try { await del(`/admin/webhooks/${id}`); success('Obrisan.'); fetchWebhooks() }
  catch (e) { toastError(getErrorMessage(e)) }
}

onMounted(fetchWebhooks)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Webhooks' }]" />
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Webhooks</h1>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 3" :key="i" height="50px" /></div>
    <div v-else-if="!webhooks.length" class="text-center py-16 text-gray-500">Nema webhook-ova.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="w in webhooks" :key="w.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="text-sm font-medium text-gray-800 font-mono">{{ w.url }}</p>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="text-xs text-gray-500">{{ w.events.join(', ') }}</span>
            <UiAtomsBadge :variant="w.is_active ? 'success' : 'neutral'" class="text-[10px]">{{ w.is_active ? 'Aktivan' : 'Neaktivan' }}</UiAtomsBadge>
          </div>
        </div>
        <UiAtomsButton variant="ghost" size="sm" @click="deleteWebhook(w.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
      </div>
    </div>
  </div>
</template>
