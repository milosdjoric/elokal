<script setup lang="ts">
import type { Order } from '~/types'

definePageMeta({ layout: false })

const route = useRoute()
const { get } = useApi()
const order = ref<Order | null>(null)
const loading = ref(true)

interface Refund { id: number; amount: string; method: string; reason: string | null; creator?: { name: string }; created_at: string }

async function fetchOrder() {
  try {
    const data = await get<{ data: Order & { refunds: Refund[] } }>(`/admin/orders/${route.params.id}`)
    order.value = data.data
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('sr-RS', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function formatPrice(v: string | number) {
  return parseFloat(String(v)).toLocaleString('sr-RS', { minimumFractionDigits: 2 })
}

function printPage() { window.print() }

const refunds = computed(() => (order.value as Order & { refunds?: Refund[] })?.refunds || [])

onMounted(fetchOrder)
</script>

<template>
  <div class="print-page">
    <div v-if="loading" class="p-8 text-gray-500">Učitavanje...</div>

    <div v-else-if="order" class="max-w-3xl mx-auto p-8">
      <div class="flex justify-between items-start mb-8 border-b pb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">KNJIŽNO ODOBRENJE</h1>
          <p class="text-sm text-gray-500 mt-1">Za narudžbinu: {{ order.order_number }}</p>
          <p class="text-sm text-gray-500">Datum: {{ formatDate(new Date().toISOString()) }}</p>
        </div>
        <div class="text-right">
          <p class="font-bold text-lg">eLokal</p>
          <p class="text-sm text-gray-500">info@webshop.rs</p>
        </div>
      </div>

      <!-- Kupac -->
      <div class="mb-8">
        <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Kupac</p>
        <p class="font-medium">{{ order.shipping.first_name }} {{ order.shipping.last_name }}</p>
        <p class="text-sm text-gray-600">{{ order.shipping.address_line_1 }}, {{ order.shipping.postal_code }} {{ order.shipping.city }}</p>
        <p class="text-sm text-gray-600">{{ order.email }}</p>
      </div>

      <!-- Refund-ovi -->
      <div v-if="refunds.length" class="mb-8">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b-2 border-gray-300">
              <th class="text-left py-2 font-semibold text-gray-700">Datum</th>
              <th class="text-left py-2 font-semibold text-gray-700">Metod</th>
              <th class="text-left py-2 font-semibold text-gray-700">Razlog</th>
              <th class="text-right py-2 font-semibold text-gray-700">Iznos</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in refunds" :key="r.id" class="border-b border-gray-100">
              <td class="py-2">{{ formatDate(r.created_at) }}</td>
              <td class="py-2">{{ r.method === 'store_credit' ? 'Store kredit' : 'Originalna metoda' }}</td>
              <td class="py-2 text-gray-600">{{ r.reason || '—' }}</td>
              <td class="py-2 text-right font-medium">{{ formatPrice(r.amount) }} RSD</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="mb-8 text-center text-gray-500 py-8">
        Nema refund-ova za ovu narudžbinu.
      </div>

      <!-- Total -->
      <div class="flex justify-end">
        <div class="w-64 space-y-1 text-sm">
          <div class="flex justify-between"><span class="text-gray-600">Originalna narudžbina:</span><span>{{ formatPrice(order.total) }} RSD</span></div>
          <div class="flex justify-between text-red-600 font-bold border-t border-gray-300 pt-2">
            <span>Ukupno vraćeno:</span><span>{{ formatPrice(order.refunded_amount) }} RSD</span>
          </div>
        </div>
      </div>

      <div class="mt-12 pt-6 border-t border-gray-200 text-xs text-gray-400 text-center">
        <p>eLokal — Kvalitetni proizvodi za vaš dom</p>
      </div>

      <div class="mt-8 text-center no-print">
        <button class="px-6 py-2 bg-primary-600 text-white text-sm font-medium hover:bg-primary-700" @click="printPage">
          Štampaj knjižno odobrenje
        </button>
      </div>
    </div>
  </div>
</template>

<style>
@media print {
  .no-print { display: none !important; }
  body { margin: 0; }
}
@media screen {
  .print-page { background: #f9fafb; min-height: 100vh; }
}
</style>
