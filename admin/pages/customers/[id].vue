<script setup lang="ts">
import type { Order, OrderStatus } from '~/types'

interface CustomerDetail {
  id: number
  name: string
  email: string
  phone: string | null
  newsletter_subscribed: boolean
  orders_count: number
  total_spent: string
  created_at: string
  addresses: Array<{
    id: number; label: string | null; first_name: string; last_name: string
    address_line_1: string; city: string; postal_code: string; is_default: boolean
  }>
  orders: Order[]
}

const route = useRoute()
const { get, getErrorMessage } = useApi()
const { error: toastError } = useToast()

const customerId = route.params.id
const customer = ref<CustomerDetail | null>(null)
const loading = ref(true)

const statusLabels: Record<OrderStatus, string> = {
  pending: 'Na čekanju', confirmed: 'Potvrđena', processing: 'U obradi',
  shipped: 'Poslata', delivered: 'Isporučena', completed: 'Završena',
  cancelled: 'Otkazana', refunded: 'Refundirana',
}

const statusVariants: Record<OrderStatus, string> = {
  pending: 'warning', confirmed: 'info', processing: 'info',
  shipped: 'info', delivered: 'success', completed: 'success',
  cancelled: 'danger', refunded: 'neutral',
}

async function fetchCustomer() {
  loading.value = true
  try {
    const data = await get<{ data: CustomerDetail }>(`/admin/customers/${customerId}`)
    customer.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
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

onMounted(fetchCustomer)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[
      { label: 'Kupci', to: '/customers' },
      { label: customer?.name || '...' },
    ]" />

    <div v-if="loading" class="space-y-4">
      <UiAtomsSkeleton height="48px" />
      <UiAtomsSkeleton height="200px" />
    </div>

    <template v-else-if="customer">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ customer.name }}</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: orders -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Narudžbine -->
          <div class="bg-white border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
              <h2 class="font-semibold text-gray-800">Narudžbine ({{ customer.orders_count }})</h2>
            </div>
            <div v-if="customer.orders.length === 0" class="px-5 py-8 text-center text-sm text-gray-400">
              Nema narudžbina.
            </div>
            <div v-else class="divide-y divide-gray-100">
              <NuxtLink
                v-for="order in customer.orders"
                :key="order.id"
                :to="`/orders/${order.id}`"
                class="flex items-center justify-between px-5 py-3 hover:bg-gray-50"
              >
                <div>
                  <p class="font-medium text-primary-600">{{ order.order_number }}</p>
                  <p class="text-xs text-gray-400">{{ formatDate(order.created_at) }}</p>
                </div>
                <div class="flex items-center gap-3">
                  <span class="font-medium text-sm">{{ formatPrice(order.total) }}</span>
                  <UiAtomsBadge :variant="statusVariants[order.status] as 'success' | 'warning' | 'danger' | 'info' | 'neutral'">
                    {{ statusLabels[order.status] }}
                  </UiAtomsBadge>
                </div>
              </NuxtLink>
            </div>
          </div>
        </div>

        <!-- Right: info -->
        <div class="space-y-6">
          <!-- Profil -->
          <div class="bg-white border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Informacije</h2>
            <div class="text-sm space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-500">Email</span>
                <span class="text-gray-800">{{ customer.email }}</span>
              </div>
              <div v-if="customer.phone" class="flex justify-between">
                <span class="text-gray-500">Telefon</span>
                <span class="text-gray-800">{{ customer.phone }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Registrovan</span>
                <span class="text-gray-800">{{ formatDate(customer.created_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Newsletter</span>
                <UiAtomsBadge :variant="customer.newsletter_subscribed ? 'success' : 'neutral'">
                  {{ customer.newsletter_subscribed ? 'Da' : 'Ne' }}
                </UiAtomsBadge>
              </div>
            </div>
          </div>

          <!-- Statistika -->
          <div class="bg-white border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Statistika</h2>
            <div class="text-sm space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-500">Narudžbine</span>
                <span class="font-medium text-gray-800">{{ customer.orders_count }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Ukupno potrošeno</span>
                <span class="font-medium text-gray-800">{{ formatPrice(customer.total_spent) }}</span>
              </div>
            </div>
          </div>

          <!-- Adrese -->
          <div v-if="customer.addresses.length" class="bg-white border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Adrese</h2>
            <div class="space-y-3">
              <div v-for="addr in customer.addresses" :key="addr.id" class="text-sm">
                <p class="font-medium text-gray-800">
                  {{ addr.first_name }} {{ addr.last_name }}
                  <span v-if="addr.is_default" class="text-xs text-primary-600 ml-1">(podrazumevana)</span>
                </p>
                <p class="text-gray-500">{{ addr.address_line_1 }}, {{ addr.postal_code }} {{ addr.city }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
