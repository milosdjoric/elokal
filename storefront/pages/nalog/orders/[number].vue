<script setup lang="ts">
import type { Order, OrderStatus } from '~/types'

definePageMeta({ middleware: 'auth' })

const route = useRoute()
const authStore = useAuthStore()
const { apiBase } = useApi()
const orderNumber = route.params.number as string

const order = ref<Order | null>(null)
const loading = ref(true)
const error = ref(false)

useHead({ title: `Narudžbina ${orderNumber} — eLokal` })

const headers = computed(() => ({
  Authorization: `Bearer ${authStore.token}`,
  Accept: 'application/json',
}))

async function fetchOrder() {
  loading.value = true
  error.value = false
  try {
    const data = await $fetch<{ data: Order }>(`${apiBase}/v1/orders/${orderNumber}`, {
      headers: headers.value,
    })
    order.value = data.data
  }
  catch {
    error.value = true
  }
  finally { loading.value = false }
}

// Status konfiguracija
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

// Progress bar koraci (za aktivne narudžbine)
const progressSteps: OrderStatus[] = ['pending', 'confirmed', 'processing', 'shipped', 'delivered']

const activeStepIndex = computed(() => {
  if (!order.value) return -1
  if (order.value.status === 'completed') return progressSteps.length
  if (order.value.status === 'cancelled' || order.value.status === 'refunded') return -1
  return progressSteps.indexOf(order.value.status)
})

const isCancelledOrRefunded = computed(() =>
  order.value?.status === 'cancelled' || order.value?.status === 'refunded',
)

const progressLabels: Record<string, string> = {
  pending: 'Primljena',
  confirmed: 'Potvrđena',
  processing: 'U pripremi',
  shipped: 'Poslata',
  delivered: 'Isporučena',
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('sr-Latn-RS', {
    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

function formatPrice(price: string): string {
  return `${parseFloat(price).toLocaleString('sr-RS', { minimumFractionDigits: 2 })} RSD`
}

function imageUrl(path: string | null): string {
  return resolveImageUrl(path)
}

onMounted(fetchOrder)
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[
      { label: 'Moj nalog', to: '/nalog' },
      { label: 'Narudžbine', to: '/nalog/orders' },
      { label: orderNumber },
    ]" />

    <div class="flex flex-col lg:flex-row gap-8">
      <LayoutAccountSidebar />

      <div class="flex-1">
        <!-- Loading -->
        <div v-if="loading" class="space-y-4">
          <UiAtomsSkeleton height="40px" />
          <UiAtomsSkeleton height="80px" />
          <UiAtomsSkeleton height="200px" />
        </div>

        <!-- Error -->
        <div v-else-if="error" class="text-center py-16">
          <p class="text-gray-500 mb-4">Narudžbina nije pronađena.</p>
          <NuxtLink to="/nalog/orders">
            <UiAtomsButton variant="outline">Nazad na narudžbine</UiAtomsButton>
          </NuxtLink>
        </div>

        <!-- Order detail -->
        <template v-else-if="order">
          <!-- Header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
              <h1 class="text-2xl font-bold text-gray-800">Narudžbina {{ order.order_number }}</h1>
              <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
            </div>
            <span
              class="inline-block px-3 py-1 text-sm font-medium rounded-full self-start"
              :class="statusColors[order.status]"
            >
              {{ statusLabels[order.status] }}
            </span>
          </div>

          <!-- Progress bar -->
          <div v-if="!isCancelledOrRefunded" class="mb-8 bg-white border border-gray-200 p-6">
            <div class="flex items-center justify-between">
              <div
                v-for="(step, index) in progressSteps"
                :key="step"
                class="flex flex-col items-center flex-1"
              >
                <!-- Krug -->
                <div class="relative flex items-center justify-center w-full">
                  <!-- Linija levo -->
                  <div
                    v-if="index > 0"
                    class="absolute right-1/2 h-0.5 w-full"
                    :class="index <= activeStepIndex ? 'bg-primary-500' : 'bg-gray-200'"
                  />
                  <!-- Krug ikonica -->
                  <div
                    class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold"
                    :class="
                      index < activeStepIndex ? 'bg-primary-500 text-white' :
                      index === activeStepIndex ? 'bg-primary-500 text-white ring-4 ring-primary-100' :
                      'bg-gray-200 text-gray-500'
                    "
                  >
                    <svg v-if="index < activeStepIndex" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span v-else>{{ index + 1 }}</span>
                  </div>
                </div>
                <!-- Label -->
                <span
                  class="mt-2 text-xs font-medium text-center hidden sm:block"
                  :class="index <= activeStepIndex ? 'text-primary-700' : 'text-gray-400'"
                >
                  {{ progressLabels[step] }}
                </span>
              </div>
            </div>
          </div>

          <!-- Cancelled/Refunded banner -->
          <div v-if="isCancelledOrRefunded" class="mb-8 p-4 border" :class="order.status === 'cancelled' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200'">
            <p class="text-sm font-medium" :class="order.status === 'cancelled' ? 'text-red-700' : 'text-gray-700'">
              {{ order.status === 'cancelled' ? 'Ova narudžbina je otkazana.' : 'Ova narudžbina je refundirana.' }}
            </p>
          </div>

          <!-- Tracking info -->
          <div v-if="order.tracking?.number" class="mb-6 border border-blue-200 bg-blue-50 p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-blue-800">
                  {{ order.tracking.carrier || 'Kurir' }}: {{ order.tracking.number }}
                </p>
              </div>
              <a
                v-if="order.tracking.url"
                :href="order.tracking.url"
                target="_blank"
                rel="noopener noreferrer"
                class="text-sm font-medium text-blue-600 hover:text-blue-800 underline"
              >
                Prati pošiljku →
              </a>
            </div>
          </div>

          <!-- Stavke -->
          <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Stavke</h2>
            <div class="border border-gray-200 divide-y divide-gray-200">
              <div
                v-for="item in order.items"
                :key="item.id"
                class="flex items-center gap-4 p-4"
              >
                <!-- Slika -->
                <NuxtLink v-if="item.product_slug" :to="`/proizvodi/${item.product_slug}`" class="flex-shrink-0">
                  <img
                    v-if="item.product_image"
                    :src="imageUrl(item.product_image)"
                    :alt="item.product_name"
                    class="w-16 h-16 object-cover rounded"
                  />
                  <div v-else class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                    </svg>
                  </div>
                </NuxtLink>
                <div v-else class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded" />

                <!-- Info -->
                <div class="flex-1 min-w-0">
                  <NuxtLink v-if="item.product_slug" :to="`/proizvodi/${item.product_slug}`" class="font-medium text-gray-800 hover:text-primary-600 truncate block">
                    {{ item.product_name }}
                  </NuxtLink>
                  <p v-else class="font-medium text-gray-800 truncate">{{ item.product_name }}</p>
                  <p v-if="item.product_sku" class="text-xs text-gray-400">SKU: {{ item.product_sku }}</p>
                  <p class="text-sm text-gray-500">{{ formatPrice(item.price) }} × {{ item.quantity }}</p>
                </div>

                <!-- Line total -->
                <p class="font-semibold text-gray-800 flex-shrink-0">{{ formatPrice(item.line_total) }}</p>
              </div>
            </div>
          </div>

          <!-- Bottom grid: totali + adresa -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Totali -->
            <div class="border border-gray-200 p-5">
              <h2 class="text-lg font-semibold text-gray-800 mb-3">Ukupno</h2>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-500">Međuzbir</span>
                  <span class="text-gray-800">{{ formatPrice(order.subtotal) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Dostava</span>
                  <span class="text-gray-800">{{ parseFloat(order.shipping_cost) > 0 ? formatPrice(order.shipping_cost) : 'Besplatno' }}</span>
                </div>
                <div v-if="parseFloat(order.tax) > 0" class="flex justify-between">
                  <span class="text-gray-500">Porez</span>
                  <span class="text-gray-800">{{ formatPrice(order.tax) }}</span>
                </div>
                <div v-if="parseFloat(order.discount) > 0" class="flex justify-between">
                  <span class="text-gray-500">Popust</span>
                  <span class="text-green-600">−{{ formatPrice(order.discount) }}</span>
                </div>
                <div class="border-t border-gray-200 pt-2 flex justify-between font-semibold">
                  <span class="text-gray-800">Ukupno</span>
                  <span class="text-gray-800">{{ formatPrice(order.total) }}</span>
                </div>
              </div>
            </div>

            <!-- Adresa dostave -->
            <div class="border border-gray-200 p-5">
              <h2 class="text-lg font-semibold text-gray-800 mb-3">Adresa dostave</h2>
              <div class="text-sm text-gray-600 space-y-1">
                <p class="font-medium text-gray-800">{{ order.shipping.first_name }} {{ order.shipping.last_name }}</p>
                <p v-if="order.shipping.company">{{ order.shipping.company }}</p>
                <p>{{ order.shipping.address_line_1 }}</p>
                <p v-if="order.shipping.address_line_2">{{ order.shipping.address_line_2 }}</p>
                <p>{{ order.shipping.postal_code }} {{ order.shipping.city }}</p>
                <p v-if="order.email" class="pt-2 text-gray-400">{{ order.email }}</p>
                <p v-if="order.phone" class="text-gray-400">{{ order.phone }}</p>
              </div>
            </div>
          </div>

          <!-- Napomena kupca -->
          <div v-if="order.notes" class="mt-6 border border-gray-200 p-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Napomena</h2>
            <p class="text-sm text-gray-600">{{ order.notes }}</p>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
