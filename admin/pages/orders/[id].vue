<script setup lang="ts">
import type { Order, OrderStatus, OrderTimelineEntry } from '~/types'

const route = useRoute()
const { get, post, patch, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

const orderId = route.params.id
const order = ref<Order | null>(null)
const loading = ref(true)
const statusLoading = ref(false)

const newStatus = ref<OrderStatus>('pending')
const adminNotes = ref('')

const trackingNumber = ref('')
const trackingCarrier = ref('')
const trackingUrl = ref('')
const trackingLoading = ref(false)

const refundAmount = ref('')
const refundReason = ref('')
const refundLoading = ref(false)
const showRefundForm = ref(false)

const showEditModal = ref(false)
const editLoading = ref(false)
const editForm = reactive({
  email: '',
  phone: '',
  shipping_first_name: '',
  shipping_last_name: '',
  shipping_company: '',
  shipping_address_line_1: '',
  shipping_address_line_2: '',
  shipping_city: '',
  shipping_postal_code: '',
  shipping_country: 'RS',
})

const isEditable = computed(() =>
  order.value?.status === 'pending' || order.value?.status === 'processing',
)

const statusLabels: Record<OrderStatus, string> = {
  pending: 'Na čekanju',
  confirmed: 'Potvrđena',
  processing: 'U obradi',
  shipped: 'Poslata',
  delivered: 'Isporučena',
  completed: 'Završena',
  cancelled: 'Otkazana',
  refunded: 'Refundirana',
}

const statusVariants: Record<OrderStatus, string> = {
  pending: 'warning',
  confirmed: 'info',
  processing: 'info',
  shipped: 'info',
  delivered: 'success',
  completed: 'success',
  cancelled: 'danger',
  refunded: 'neutral',
}

async function fetchOrder() {
  loading.value = true
  try {
    const data = await get<{ data: Order }>(`/admin/orders/${orderId}`)
    order.value = data.data
    newStatus.value = data.data.status
    adminNotes.value = data.data.admin_notes || ''
    trackingNumber.value = data.data.tracking?.number || ''
    trackingCarrier.value = data.data.tracking?.carrier || ''
    trackingUrl.value = data.data.tracking?.url || ''
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function updateStatus() {
  if (!order.value || newStatus.value === order.value.status) return
  statusLoading.value = true
  try {
    const data = await patch<{ data: Order }>(`/admin/orders/${orderId}/status`, {
      status: newStatus.value,
      admin_notes: adminNotes.value || undefined,
    })
    order.value = data.data
    success(`Status promenjen u "${statusLabels[newStatus.value]}".`)
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { statusLoading.value = false }
}

const maxRefundable = computed(() => {
  if (!order.value) return 0
  return Math.max(0, parseFloat(order.value.total) - parseFloat(order.value.refunded_amount))
})

async function submitRefund() {
  if (!order.value) return
  refundLoading.value = true
  try {
    const data = await post<{ data: Order }>(`/admin/orders/${orderId}/refund`, {
      amount: parseFloat(refundAmount.value),
      reason: refundReason.value || undefined,
    })
    order.value = data.data
    newStatus.value = data.data.status
    showRefundForm.value = false
    refundAmount.value = ''
    refundReason.value = ''
    success('Refund uspešno evidentiran.')
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { refundLoading.value = false }
}

function setFullRefund() {
  refundAmount.value = maxRefundable.value.toFixed(2)
}

function openEditModal() {
  if (!order.value) return
  editForm.email = order.value.email
  editForm.phone = order.value.phone || ''
  editForm.shipping_first_name = order.value.shipping.first_name
  editForm.shipping_last_name = order.value.shipping.last_name
  editForm.shipping_company = order.value.shipping.company || ''
  editForm.shipping_address_line_1 = order.value.shipping.address_line_1
  editForm.shipping_address_line_2 = order.value.shipping.address_line_2 || ''
  editForm.shipping_city = order.value.shipping.city
  editForm.shipping_postal_code = order.value.shipping.postal_code
  editForm.shipping_country = order.value.shipping.country
  showEditModal.value = true
}

async function saveEdit() {
  editLoading.value = true
  try {
    const { put } = useApi()
    const data = await put<{ data: Order }>(`/admin/orders/${orderId}`, editForm)
    order.value = data.data
    showEditModal.value = false
    success('Narudžbina izmenjena.')
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { editLoading.value = false }
}

async function updateTracking() {
  if (!order.value) return
  trackingLoading.value = true
  try {
    const data = await patch<{ data: Order }>(`/admin/orders/${orderId}/tracking`, {
      tracking_number: trackingNumber.value || null,
      tracking_carrier: trackingCarrier.value || null,
      tracking_url: trackingUrl.value || null,
    })
    order.value = data.data
    success('Tracking informacije ažurirane.')
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { trackingLoading.value = false }
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
  if (!path) return ''
  return resolveImageUrl(path)
}

function timelineIcon(entry: OrderTimelineEntry): string {
  if (entry.status === 'cancelled') return 'text-red-500'
  if (entry.status === 'completed' || entry.status === 'delivered') return 'text-green-500'
  return 'text-primary-500'
}

onMounted(fetchOrder)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[
      { label: 'Narudžbine', to: '/orders' },
      { label: order?.order_number || '...' },
    ]" />

    <!-- Loading -->
    <div v-if="loading" class="space-y-4">
      <UiAtomsSkeleton height="48px" />
      <UiAtomsSkeleton height="200px" />
      <UiAtomsSkeleton height="200px" />
    </div>

    <template v-else-if="order">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ order.order_number }}</h1>
          <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
        </div>
        <div class="flex items-center gap-3">
          <NuxtLink :to="`/orders/${orderId}/invoice`" target="_blank">
            <UiAtomsButton variant="secondary" size="sm">Faktura</UiAtomsButton>
          </NuxtLink>
          <NuxtLink :to="`/orders/${orderId}/packing-slip`" target="_blank">
            <UiAtomsButton variant="secondary" size="sm">Otpremnica</UiAtomsButton>
          </NuxtLink>
          <NuxtLink v-if="parseFloat(order.refunded_amount) > 0" :to="`/orders/${orderId}/credit-note`" target="_blank">
            <UiAtomsButton variant="secondary" size="sm">Knjižno odobrenje</UiAtomsButton>
          </NuxtLink>
          <UiAtomsButton v-if="isEditable" variant="secondary" size="sm" @click="openEditModal">
            Izmeni
          </UiAtomsButton>
          <UiAtomsBadge
            :variant="statusVariants[order.status] as 'success' | 'warning' | 'danger' | 'info' | 'neutral'"
            class="text-sm px-3 py-1"
          >
            {{ statusLabels[order.status] }}
          </UiAtomsBadge>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: stavke + totali -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Stavke -->
          <div class="bg-white border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
              <h2 class="font-semibold text-gray-800">Stavke ({{ order.items.length }})</h2>
            </div>
            <div class="divide-y divide-gray-100">
              <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4 px-5 py-3">
                <img
                  v-if="item.product_image"
                  :src="imageUrl(item.product_image)"
                  :alt="item.product_name"
                  class="w-12 h-12 object-cover border border-gray-200"
                />
                <div v-else class="w-12 h-12 bg-gray-100 flex items-center justify-center">
                  <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-gray-800 truncate">{{ item.product_name }}</p>
                  <p v-if="item.product_sku" class="text-xs text-gray-400">SKU: {{ item.product_sku }}</p>
                </div>
                <p class="text-sm text-gray-500 flex-shrink-0">{{ formatPrice(item.price) }} × {{ item.quantity }}</p>
                <p class="font-semibold text-gray-800 flex-shrink-0 w-28 text-right">{{ formatPrice(item.line_total) }}</p>
              </div>
            </div>
            <!-- Totali -->
            <div class="px-5 py-4 border-t border-gray-200 bg-gray-50 space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">Međuzbir</span>
                <span>{{ formatPrice(order.subtotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Dostava</span>
                <span>{{ parseFloat(order.shipping_cost) > 0 ? formatPrice(order.shipping_cost) : 'Besplatno' }}</span>
              </div>
              <div v-if="parseFloat(order.tax) > 0" class="flex justify-between">
                <span class="text-gray-500">Porez</span>
                <span>{{ formatPrice(order.tax) }}</span>
              </div>
              <div v-if="parseFloat(order.discount) > 0" class="flex justify-between">
                <span class="text-gray-500">Popust</span>
                <span class="text-green-600">−{{ formatPrice(order.discount) }}</span>
              </div>
              <div class="flex justify-between font-bold text-base pt-2 border-t border-gray-200">
                <span>Ukupno</span>
                <span>{{ formatPrice(order.total) }}</span>
              </div>
            </div>
          </div>

          <!-- Timeline -->
          <div v-if="order.timeline && order.timeline.length" class="bg-white border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
              <h2 class="font-semibold text-gray-800">Istorija promena</h2>
            </div>
            <div class="px-5 py-4">
              <div class="space-y-4">
                <div v-for="entry in order.timeline" :key="entry.id" class="flex gap-3">
                  <div class="flex-shrink-0 mt-0.5">
                    <div class="w-2.5 h-2.5 rounded-full" :class="timelineIcon(entry).replace('text-', 'bg-')" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800">
                      <span class="font-medium">{{ statusLabels[entry.status as OrderStatus] || entry.status }}</span>
                      <span v-if="entry.old_status" class="text-gray-400"> ← {{ statusLabels[entry.old_status as OrderStatus] || entry.old_status }}</span>
                    </p>
                    <p v-if="entry.note" class="text-sm text-gray-500 mt-0.5">{{ entry.note }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ formatDate(entry.created_at) }}
                      <span v-if="entry.actor_type" class="ml-1">({{ entry.actor_type }})</span>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right sidebar -->
        <div class="space-y-6">
          <!-- Status promena -->
          <div class="bg-white border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-4">Promeni status</h2>
            <div class="space-y-3">
              <select
                v-model="newStatus"
                class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
              </select>
              <textarea
                v-model="adminNotes"
                rows="3"
                placeholder="Beleška (opciono)..."
                class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
              <UiAtomsButton
                :loading="statusLoading"
                :disabled="newStatus === order.status"
                class="w-full"
                @click="updateStatus"
              >
                Ažuriraj status
              </UiAtomsButton>
            </div>
          </div>

          <!-- Tracking -->
          <div class="bg-white border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-4">Tracking</h2>
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Kurir</label>
                <select
                  v-model="trackingCarrier"
                  class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Izaberite kurira</option>
                  <option value="Post Express">Post Express</option>
                  <option value="BEX">BEX</option>
                  <option value="AKS">AKS</option>
                  <option value="D Express">D Express</option>
                  <option value="City Express">City Express</option>
                  <option value="DHL">DHL</option>
                  <option value="FedEx">FedEx</option>
                  <option value="Drugo">Drugo</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Broj pošiljke</label>
                <input
                  v-model="trackingNumber"
                  type="text"
                  placeholder="npr. RS123456789YU"
                  class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Link za praćenje</label>
                <input
                  v-model="trackingUrl"
                  type="url"
                  placeholder="https://..."
                  class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
              <UiAtomsButton
                :loading="trackingLoading"
                class="w-full"
                variant="secondary"
                @click="updateTracking"
              >
                Sačuvaj tracking
              </UiAtomsButton>
            </div>
          </div>

          <!-- Refund -->
          <div v-if="maxRefundable > 0" class="bg-white border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
              <h2 class="font-semibold text-gray-800">Refund</h2>
              <button
                v-if="!showRefundForm"
                class="text-sm text-red-600 hover:text-red-800 font-medium"
                @click="showRefundForm = true"
              >
                Iniciiraj refund
              </button>
            </div>

            <div v-if="parseFloat(order.refunded_amount) > 0" class="text-sm mb-3 p-2 bg-red-50 border border-red-100">
              <p class="text-red-700 font-medium">
                Refundirano: {{ formatPrice(order.refunded_amount) }} / {{ formatPrice(order.total) }}
              </p>
              <p v-if="order.refund_reason" class="text-red-600 text-xs mt-1">{{ order.refund_reason }}</p>
            </div>

            <div v-if="showRefundForm" class="space-y-3">
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="text-xs font-medium text-gray-500">Iznos (max {{ maxRefundable.toFixed(2) }})</label>
                  <button class="text-xs text-primary-600 hover:underline" @click="setFullRefund">Full refund</button>
                </div>
                <input
                  v-model="refundAmount"
                  type="number"
                  step="0.01"
                  :max="maxRefundable"
                  min="0.01"
                  placeholder="0.00"
                  class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
              <textarea
                v-model="refundReason"
                rows="2"
                placeholder="Razlog (opciono)..."
                class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
              <div class="flex gap-2">
                <UiAtomsButton
                  variant="danger"
                  :loading="refundLoading"
                  :disabled="!refundAmount || parseFloat(refundAmount) <= 0"
                  class="flex-1"
                  @click="submitRefund"
                >
                  Potvrdi refund
                </UiAtomsButton>
                <UiAtomsButton variant="secondary" @click="showRefundForm = false">Otkaži</UiAtomsButton>
              </div>
            </div>
          </div>

          <!-- Kontakt -->
          <div class="bg-white border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Kontakt</h2>
            <div class="text-sm space-y-1">
              <p class="text-gray-800">{{ order.email }}</p>
              <p v-if="order.phone" class="text-gray-500">{{ order.phone }}</p>
            </div>
          </div>

          <!-- Adresa -->
          <div class="bg-white border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Adresa dostave</h2>
            <div class="text-sm text-gray-600 space-y-1">
              <p class="font-medium text-gray-800">{{ order.shipping.first_name }} {{ order.shipping.last_name }}</p>
              <p v-if="order.shipping.company">{{ order.shipping.company }}</p>
              <p>{{ order.shipping.address_line_1 }}</p>
              <p v-if="order.shipping.address_line_2">{{ order.shipping.address_line_2 }}</p>
              <p>{{ order.shipping.postal_code }} {{ order.shipping.city }}</p>
            </div>
          </div>

          <!-- Napomena kupca -->
          <div v-if="order.notes" class="bg-white border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Napomena kupca</h2>
            <p class="text-sm text-gray-600">{{ order.notes }}</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Edit modal -->
    <UiMoleculesModal v-model="showEditModal" title="Izmeni narudžbinu">
      <form @submit.prevent="saveEdit" class="space-y-4">
        <h3 class="text-sm font-semibold text-gray-700">Kontakt</h3>
        <div class="grid grid-cols-2 gap-4">
          <UiAtomsInput v-model="editForm.email" label="Email" type="email" required />
          <UiAtomsInput v-model="editForm.phone" label="Telefon" type="tel" />
        </div>
        <h3 class="text-sm font-semibold text-gray-700 pt-2">Adresa dostave</h3>
        <div class="grid grid-cols-2 gap-4">
          <UiAtomsInput v-model="editForm.shipping_first_name" label="Ime" required />
          <UiAtomsInput v-model="editForm.shipping_last_name" label="Prezime" required />
        </div>
        <UiAtomsInput v-model="editForm.shipping_company" label="Firma" />
        <UiAtomsInput v-model="editForm.shipping_address_line_1" label="Adresa" required />
        <UiAtomsInput v-model="editForm.shipping_address_line_2" label="Adresa 2" />
        <div class="grid grid-cols-2 gap-4">
          <UiAtomsInput v-model="editForm.shipping_city" label="Grad" required />
          <UiAtomsInput v-model="editForm.shipping_postal_code" label="Poštanski broj" required />
        </div>
      </form>
      <template #footer>
        <UiAtomsButton variant="secondary" @click="showEditModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="editLoading" @click="saveEdit">Sačuvaj</UiAtomsButton>
      </template>
    </UiMoleculesModal>
  </div>
</template>
