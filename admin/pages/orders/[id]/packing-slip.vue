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

function printPage() { window.print() }

onMounted(fetchOrder)
</script>

<template>
  <div class="print-page">
    <div v-if="loading" class="p-8 text-gray-500">Učitavanje...</div>

    <div v-else-if="order" class="max-w-3xl mx-auto p-8">
      <div class="flex justify-between items-start mb-8 border-b pb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">OTPREMNICA</h1>
          <p class="text-sm text-gray-500 mt-1">{{ order.order_number }}</p>
          <p class="text-sm text-gray-500">Datum: {{ formatDate(order.created_at) }}</p>
        </div>
        <div class="text-right">
          <p class="font-bold text-lg">eLokal</p>
        </div>
      </div>

      <!-- Adresa dostave -->
      <div class="mb-8 p-4 border border-gray-200">
        <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Dostava na adresu</p>
        <p class="font-medium text-lg">{{ order.shipping.first_name }} {{ order.shipping.last_name }}</p>
        <p v-if="order.shipping.company" class="text-gray-600">{{ order.shipping.company }}</p>
        <p class="text-gray-600">{{ order.shipping.address_line_1 }}</p>
        <p v-if="order.shipping.address_line_2" class="text-gray-600">{{ order.shipping.address_line_2 }}</p>
        <p class="text-gray-600">{{ order.shipping.postal_code }} {{ order.shipping.city }}</p>
        <p v-if="order.phone" class="text-gray-600 mt-1">Tel: {{ order.phone }}</p>
      </div>

      <!-- Stavke (bez cena) -->
      <table class="w-full mb-8 text-sm">
        <thead>
          <tr class="border-b-2 border-gray-300">
            <th class="text-left py-2 font-semibold text-gray-700">Proizvod</th>
            <th class="text-left py-2 font-semibold text-gray-700">SKU</th>
            <th class="text-right py-2 font-semibold text-gray-700">Količina</th>
            <th class="text-center py-2 font-semibold text-gray-700 w-16">✓</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in order.items" :key="item.id" class="border-b border-gray-100">
            <td class="py-3">{{ item.product_name }}</td>
            <td class="py-3 text-gray-500">{{ item.product_sku || '—' }}</td>
            <td class="py-3 text-right font-medium">{{ item.quantity }}</td>
            <td class="py-3 text-center"><span class="inline-block w-5 h-5 border-2 border-gray-300"></span></td>
          </tr>
        </tbody>
      </table>

      <div v-if="order.notes" class="mb-8 p-4 bg-gray-50 border border-gray-200">
        <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Napomena kupca</p>
        <p class="text-sm text-gray-700">{{ order.notes }}</p>
      </div>

      <div class="mt-8 text-center no-print">
        <button class="px-6 py-2 bg-primary-600 text-white text-sm font-medium hover:bg-primary-700" @click="printPage">
          Štampaj otpremnicu
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
