<script setup lang="ts">
const { get, getErrorMessage } = useApi()
const { error: toastError } = useToast()

interface LogEntry { id: number; admin_name: string; action: string; model_type: string; model_id: number | null; changes: Record<string, unknown> | null; created_at: string }

const logs = ref<LogEntry[]>([])
const loading = ref(true)

async function fetchLogs() {
  loading.value = true
  try {
    const data = await get<{ data: LogEntry[] }>('/admin/activity-log')
    logs.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function formatDate(d: string) {
  return new Date(d).toLocaleString('sr-RS', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(fetchLogs)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Activity Log' }]" />
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Activity Log</h1>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 8" :key="i" height="40px" /></div>
    <div v-else-if="!logs.length" class="text-center py-16 text-gray-500">Nema aktivnosti.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="log in logs" :key="log.id" class="px-5 py-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-800">{{ log.admin_name }}</span>
            <span class="text-xs text-gray-500">{{ log.action }}</span>
            <span class="text-xs text-gray-400">{{ log.model_type }}{{ log.model_id ? ` #${log.model_id}` : '' }}</span>
          </div>
          <span class="text-xs text-gray-400">{{ formatDate(log.created_at) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
