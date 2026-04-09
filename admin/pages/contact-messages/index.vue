<script setup lang="ts">
const { get, patch, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface ContactMessage { id: number; name: string; email: string; phone: string | null; subject: string | null; message: string; status: string; created_at: string }

const messages = ref<ContactMessage[]>([])
const loading = ref(true)
const selectedMessage = ref<ContactMessage | null>(null)

async function fetchMessages() {
  loading.value = true
  try { const data = await get<{ data: ContactMessage[] }>('/admin/contact-messages'); messages.value = data.data }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function markAsRead(msg: ContactMessage) {
  try { await patch(`/admin/contact-messages/${msg.id}/status`, { status: 'read' }); msg.status = 'read' }
  catch (e) { toastError(getErrorMessage(e)) }
}

async function deleteMessage(id: number) {
  if (!confirm('Obrisati poruku?')) return
  try { await del(`/admin/contact-messages/${id}`); success('Obrisano.'); selectedMessage.value = null; fetchMessages() }
  catch (e) { toastError(getErrorMessage(e)) }
}

function openMessage(msg: ContactMessage) {
  selectedMessage.value = msg
  if (msg.status === 'new') markAsRead(msg)
}

function statusVariant(s: string) { return ({ new: 'warning', read: 'neutral', replied: 'success' } as const)[s] ?? 'neutral' as const }

function formatDate(d: string) {
  return new Date(d).toLocaleString('sr-RS', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(fetchMessages)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Kontakt poruke' }]" />
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Kontakt poruke</h1>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 5" :key="i" height="50px" /></div>
    <div v-else-if="!messages.length" class="text-center py-16 text-gray-500">Nema poruka.</div>

    <div v-else class="grid lg:grid-cols-3 gap-4">
      <!-- Lista -->
      <div class="lg:col-span-1 bg-white border border-gray-200 divide-y divide-gray-100 max-h-[600px] overflow-y-auto">
        <button
          v-for="msg in messages" :key="msg.id"
          class="w-full text-left px-4 py-3 hover:bg-gray-50 transition-colors"
          :class="selectedMessage?.id === msg.id ? 'bg-primary-50' : (msg.status === 'new' ? 'bg-yellow-50' : '')"
          @click="openMessage(msg)"
        >
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-800" :class="msg.status === 'new' ? 'font-bold' : ''">{{ msg.name }}</span>
            <UiAtomsBadge :variant="statusVariant(msg.status)" class="text-[10px]">{{ msg.status }}</UiAtomsBadge>
          </div>
          <p class="text-xs text-gray-500 mt-0.5 truncate">{{ msg.subject || msg.message.substring(0, 50) }}</p>
          <span class="text-[10px] text-gray-400">{{ formatDate(msg.created_at) }}</span>
        </button>
      </div>

      <!-- Detalj -->
      <div class="lg:col-span-2">
        <div v-if="selectedMessage" class="bg-white border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-lg font-bold text-gray-800">{{ selectedMessage.name }}</h2>
              <p class="text-sm text-gray-500">{{ selectedMessage.email }}{{ selectedMessage.phone ? ` • ${selectedMessage.phone}` : '' }}</p>
            </div>
            <UiAtomsButton variant="ghost" size="sm" @click="deleteMessage(selectedMessage.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
          </div>
          <p v-if="selectedMessage.subject" class="text-sm font-medium text-gray-700 mb-2">{{ selectedMessage.subject }}</p>
          <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ selectedMessage.message }}</p>
          <p class="text-xs text-gray-400 mt-4">{{ formatDate(selectedMessage.created_at) }}</p>
        </div>
        <div v-else class="bg-white border border-gray-200 p-16 text-center text-gray-400">
          Izaberite poruku iz liste.
        </div>
      </div>
    </div>
  </div>
</template>
