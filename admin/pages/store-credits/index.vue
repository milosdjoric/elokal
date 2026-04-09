<script setup lang="ts">
const { get, getErrorMessage } = useApi()
const { error: toastError } = useToast()

interface StoreCreditAccount { id: number; user_id: number; balance: string; user?: { name: string; email: string } }

const accounts = ref<StoreCreditAccount[]>([])
const loading = ref(true)

async function fetchAccounts() {
  loading.value = true
  try {
    const data = await get<{ data: StoreCreditAccount[] }>('/admin/store-credits')
    accounts.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

onMounted(fetchAccounts)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Store krediti' }]" />
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Store krediti</h1>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 4" :key="i" height="50px" /></div>
    <div v-else-if="!accounts.length" class="text-center py-16 text-gray-500">Nema store credit naloga.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="a in accounts" :key="a.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="font-medium text-gray-800">{{ a.user?.name || '—' }}</p>
          <span class="text-xs text-gray-500">{{ a.user?.email }}</span>
        </div>
        <span class="font-bold text-gray-800">{{ parseFloat(a.balance).toLocaleString('sr-RS') }} RSD</span>
      </div>
    </div>
  </div>
</template>
