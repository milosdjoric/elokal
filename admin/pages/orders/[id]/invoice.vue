<script setup lang="ts">
import type { Order } from '~/types'

definePageMeta({ layout: false })

const route = useRoute()
const { get } = useApi()
const order = ref<Order | null>(null)
const loading = ref(true)

async function fetchOrder() {
  try {
    const data = await get<{ data: Order }>(`/admin/orders/${route.params.id}`)
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

onMounted(fetchOrder)
</script>

<template>
  <div class="print-page">
    <div v-if="loading" class="p-8 text-gray-500">Učitavanje...</div>

    <div v-else-if="order" class="max-w-3xl mx-auto p-8">
      <!-- Header -->
      <div class="flex justify-between items-start mb-8 border-b pb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">FAKTURA</h1>
          <p class="text-sm text-gray-500 mt-1">{{ order.order_number }}</p>
          <p class="text-sm text-gray-500">Datum: {{ formatDate(order.created_at) }}</p>
        </div>
        <div class="text-right">
          <p class="font-bold text-lg">eLokal</p>
          <p class="text-sm text-gray-500">info@webshop.rs</p>
          <p class="text-sm text-gray-500">+381 11 123 4567</p>
        </div>
      </div>

      <!-- Kupac -->
      <div class="grid grid-cols-2 gap-8 mb-8">
        <div>
          <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Kupac</p>
          <p class="font-medium">{{ order.shipping.first_name }} {{ order.shipping.last_name }}</p>
          <p class="text-sm text-gray-600">{{ order.shipping.address_line_1 }}</p>
          <p class="text-sm text-gray-600">{{ order.shipping.postal_code }} {{ order.shipping.city }}</p>
          <p class="text-sm text-gray-600">{{ order.email }}</p>
          <p v-if="order.phone" class="text-sm text-gray-600">{{ order.phone }}</p>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Detalji</p>
          <p class="text-sm text-gray-600">Status: {{ order.status }}</p>
          <p v-if="order.tracking?.number" class="text-sm text-gray-600">Tracking: {{ order.tracking.number }}</p>
        </div>
      </div>

      <!-- Stavke -->
      <table class="w-full mb-8 text-sm">
        <thead>
          <tr class="border-b-2 border-gray-300">
            <th class="text-left py-2 font-semibold text-gray-700">Proizvod</th>
            <th class="text-left py-2 font-semibold text-gray-700">SKU</th>
            <th class="text-right py-2 font-semibold text-gray-700">Cena</th>
            <th class="text-right py-2 font-semibold text-gray-700">Kol.</th>
            <th class="text-right py-2 font-semibold text-gray-700">Ukupno</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in order.items" :key="item.id" class="border-b border-gray-100">
            <td class="py-2">{{ item.product_name }}</td>
            <td class="py-2 text-gray-500">{{ item.product_sku || '—' }}</td>
            <td class="py-2 text-right">{{ formatPrice(item.price) }}</td>
            <td class="py-2 text-right">{{ item.quantity }}</td>
            <td class="py-2 text-right font-medium">{{ formatPrice(item.line_total) }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Totali -->
      <div class="flex justify-end">
        <div class="w-64 space-y-1 text-sm">
          <div class="flex justify-between"><span class="text-gray-600">Međuzbir:</span><span>{{ formatPrice(order.subtotal) }} RSD</span></div>
          <div class="flex justify-between"><span class="text-gray-600">Dostava:</span><span>{{ formatPrice(order.shipping_cost) }} RSD</span></div>
          <div v-if="parseFloat(order.tax) > 0" class="flex justify-between"><span class="text-gray-600">PDV:</span><span>{{ formatPrice(order.tax) }} RSD</span></div>
          <div v-if="parseFloat(order.discount) > 0" class="flex justify-between"><span class="text-gray-600">Popust:</span><span>-{{ formatPrice(order.discount) }} RSD</span></div>
          <div class="flex justify-between border-t border-gray-300 pt-2 font-bold text-base">
            <span>Ukupno:</span><span>{{ formatPrice(order.total) }} RSD</span>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-12 pt-6 border-t border-gray-200 text-xs text-gray-400 text-center">
        <p>eLokal — Kvalitetni proizvodi za vaš dom</p>
      </div>

      <!-- Print dugme (sakriveno pri štampi) -->
      <div class="mt-8 text-center no-print">
        <button class="px-6 py-2 bg-primary-600 text-white text-sm font-medium hover:bg-primary-700" @click="printPage">
          Štampaj / Sačuvaj kao PDF
        </button>
      </div>
    </div>
  </div>
</template>

<style>
@media print {
  .no-print { display: none !important; }
  body { margin: 0; }
  .print-page { padding: 0; }
}
@media screen {
  .print-page { background: #f9fafb; min-height: 100vh; }
}
</style>
