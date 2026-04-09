<script setup lang="ts">
const { get, getErrorMessage } = useApi()
const { error: toastError } = useToast()

interface GiftCard { id: number; code: string; initial_amount: string; balance: string; recipient_email: string | null; recipient_name: string | null; is_active: boolean; expires_at: string | null }

const cards = ref<GiftCard[]>([])
const loading = ref(true)

async function fetchCards() {
  loading.value = true
  try {
    const data = await get<{ data: GiftCard[] }>('/admin/gift-cards')
    cards.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

onMounted(fetchCards)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Poklon kartice' }]" />
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Poklon kartice</h1>
    </div>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 4" :key="i" height="50px" /></div>
    <div v-else-if="!cards.length" class="text-center py-16 text-gray-500">Nema poklon kartica.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="c in cards" :key="c.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="font-mono font-bold text-gray-800">{{ c.code }}</p>
          <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-500">
            <span>{{ parseFloat(c.balance).toLocaleString('sr-RS') }} / {{ parseFloat(c.initial_amount).toLocaleString('sr-RS') }} RSD</span>
            <span v-if="c.recipient_name">→ {{ c.recipient_name }}</span>
            <UiAtomsBadge :variant="c.is_active ? 'success' : 'neutral'" class="text-[10px]">{{ c.is_active ? 'Aktivna' : 'Neaktivna' }}</UiAtomsBadge>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
