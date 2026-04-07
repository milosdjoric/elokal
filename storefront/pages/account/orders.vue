<script setup lang="ts">
import type { Order, PaginatedResponse } from '~/types'

definePageMeta({ middleware: 'auth' })
useHead({ title: 'Narudžbine — eLokal' })

const authStore = useAuthStore()
const { apiBase } = useApi()

const orders = ref<Order[]>([])
const loading = ref(true)
const currentPage = ref(1)
const lastPage = ref(1)

const headers = computed(() => ({
  Authorization: `Bearer ${authStore.token}`,
  Accept: 'application/json',
}))

const statusLabels: Record<string, string> = {
  pending: 'Na čekanju',
  confirmed: 'Potvrđena',
  processing: 'U obradi',
  shipped: 'Poslata',
  delivered: 'Isporučena',
  completed: 'Završena',
  cancelled: 'Otkazana',
  refunded: 'Refundirana',
}

const statusColors: Record<string, string> = {
  pending: 'bg-yellow-100 text-yellow-800',
  confirmed: 'bg-blue-100 text-blue-800',
  processing: 'bg-indigo-100 text-indigo-800',
  shipped: 'bg-purple-100 text-purple-800',
  delivered: 'bg-green-100 text-green-800',
  completed: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800',
  refunded: 'bg-gray-100 text-gray-800',
}

async function fetchOrders(page = 1) {
  loading.value = true
  try {
    const data = await $fetch<PaginatedResponse<Order>>(`${apiBase}/v1/orders?page=${page}`, {
      headers: headers.value,
    })
    orders.value = data.data
    currentPage.value = data.meta.current_page
    lastPage.value = data.meta.last_page
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('sr-Latn-RS', {
    day: '2-digit', month: '2-digit', year: 'numeric',
  })
}

function formatPrice(price: string): string {
  return `${parseFloat(price).toLocaleString('sr-RS', { minimumFractionDigits: 2 })} RSD`
}

onMounted(() => fetchOrders())
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: 'Moj nalog', to: '/account' }, { label: 'Narudžbine' }]" />

    <div class="flex flex-col lg:flex-row gap-8">
      <LayoutAccountSidebar />

      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Narudžbine</h1>

        <!-- Loading -->
        <div v-if="loading" class="space-y-3">
          <UiAtomsSkeleton v-for="i in 3" :key="i" height="80px" />
        </div>

        <!-- Empty -->
        <div v-else-if="orders.length === 0" class="text-center py-16 text-gray-500">
          <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
          </svg>
          <p>Nemate narudžbina. Narudžbine će se prikazati ovde nakon kupovine.</p>
          <NuxtLink to="/products" class="inline-block mt-4">
            <UiAtomsButton variant="outline">Pogledaj proizvode</UiAtomsButton>
          </NuxtLink>
        </div>

        <!-- Orders list -->
        <div v-else class="space-y-3">
          <NuxtLink
            v-for="order in orders"
            :key="order.id"
            :to="`/account/orders/${order.order_number}`"
            class="block border border-gray-200 p-4 hover:border-primary-300 hover:bg-gray-50 transition-colors"
          >
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <p class="font-semibold text-gray-800">{{ order.order_number }}</p>
                <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-800">{{ formatPrice(order.total) }}</span>
                <span class="inline-block px-2.5 py-0.5 text-xs font-medium rounded-full" :class="statusColors[order.status]">
                  {{ statusLabels[order.status] }}
                </span>
              </div>
            </div>
            <p v-if="order.items?.length" class="text-xs text-gray-400 mt-2">
              {{ order.items.length }} {{ order.items.length === 1 ? 'stavka' : order.items.length < 5 ? 'stavke' : 'stavki' }}
            </p>
          </NuxtLink>

          <!-- Pagination -->
          <div v-if="lastPage > 1" class="flex justify-center gap-2 pt-4">
            <UiAtomsButton
              variant="outline"
              size="sm"
              :disabled="currentPage <= 1"
              @click="fetchOrders(currentPage - 1)"
            >
              ← Prethodna
            </UiAtomsButton>
            <span class="flex items-center text-sm text-gray-500">
              {{ currentPage }} / {{ lastPage }}
            </span>
            <UiAtomsButton
              variant="outline"
              size="sm"
              :disabled="currentPage >= lastPage"
              @click="fetchOrders(currentPage + 1)"
            >
              Sledeća →
            </UiAtomsButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
