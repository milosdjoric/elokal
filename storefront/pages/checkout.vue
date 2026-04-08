<script setup lang="ts">
interface SavedAddress {
  id: number; label: string | null; first_name: string; last_name: string
  company: string | null; address_line_1: string; address_line_2: string | null
  city: string; postal_code: string; country: string; phone: string | null; is_default: boolean
}

const { items, total, isEmpty, clearCart } = useCart()
const authStore = useAuthStore()
const { apiBase } = useApi()
const router = useRouter()

const form = reactive({
  email: authStore.user?.email || '',
  phone: authStore.user?.phone || '',
  shipping_first_name: '',
  shipping_last_name: '',
  shipping_company: '',
  shipping_address_line_1: '',
  shipping_address_line_2: '',
  shipping_city: '',
  shipping_postal_code: '',
  shipping_country: 'RS',
  notes: '',
})

const billingSameAsShipping = ref(true)
const billing = reactive({
  first_name: '',
  last_name: '',
  address_line_1: '',
  city: '',
  postal_code: '',
  country: 'RS',
})

const loading = ref(false)
const error = ref('')
const errors = ref<Record<string, string[]>>({})
const touched = ref<Record<string, boolean>>({})

// Payment metode
interface PaymentMethodOption { id: number; code: string; name: string; description: string | null; instructions: string | null; additional_cost: string }
const paymentMethods = ref<PaymentMethodOption[]>([])
const selectedPaymentMethod = ref<number | null>(null)

async function fetchPaymentMethods() {
  try {
    const data = await $fetch<{ data: PaymentMethodOption[] }>(`${apiBase}/v1/payment-methods`, {
      headers: { Accept: 'application/json' },
    })
    paymentMethods.value = data.data
    if (data.data.length > 0) {
      selectedPaymentMethod.value = data.data[0].id
    }
  }
  catch { /* silent */ }
}

const selectedPaymentInfo = computed(() =>
  paymentMethods.value.find(m => m.id === selectedPaymentMethod.value),
)

// Kupon
const couponCode = ref('')
const couponLoading = ref(false)
const couponError = ref('')
const appliedCoupon = ref<{ code: string; type: string; value: string; discount: string } | null>(null)

// Shipping metode
interface ShippingOption { id: number; name: string; type: string; cost: string; estimated_days: string | null }
const shippingMethods = ref<ShippingOption[]>([])
const selectedShippingMethod = ref<number | null>(null)
const shippingCost = computed(() => {
  const method = shippingMethods.value.find(m => m.id === selectedShippingMethod.value)
  return method ? parseFloat(method.cost) : 0
})

// Totali
const cartStore = useCartStore()
const subtotal = computed(() => cartStore.total)
const discountAmount = computed(() => appliedCoupon.value ? parseFloat(appliedCoupon.value.discount) : 0)
const giftCardAmount = computed(() => appliedGiftCard.value ? Math.min(appliedGiftCard.value.amount, appliedGiftCard.value.balance) : 0)

// Porez
const taxAmount = ref(0)
const taxRateName = ref('')

async function fetchTax() {
  if (subtotal.value <= 0) { taxAmount.value = 0; return }
  try {
    const data = await $fetch<{ data: { tax: number; rate: number; name: string } }>(`${apiBase}/v1/tax/calculate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ subtotal: subtotal.value, country: form.shipping_country || 'RS' }),
    })
    taxAmount.value = data.data.tax
    taxRateName.value = data.data.name
  }
  catch { taxAmount.value = 0 }
}

watch([subtotal, () => form.shipping_country], fetchTax)

const orderTotal = computed(() => Math.max(0, subtotal.value + taxAmount.value - discountAmount.value + shippingCost.value - giftCardAmount.value - loyaltySpend.value - creditsSpend.value))

// Saved addresses
const savedAddresses = ref<SavedAddress[]>([])
const selectedAddressId = ref<number | null>(null)

async function fetchSavedAddresses() {
  if (!authStore.isLoggedIn) return
  try {
    const data = await $fetch<{ data: SavedAddress[] }>(`${apiBase}/v1/addresses`, {
      headers: { Authorization: `Bearer ${authStore.token}`, Accept: 'application/json' },
    })
    savedAddresses.value = data.data
    const defaultAddr = data.data.find(a => a.is_default)
    if (defaultAddr) applyAddress(defaultAddr)
  }
  catch { /* silent */ }
}

function applyAddress(addr: SavedAddress) {
  selectedAddressId.value = addr.id
  form.shipping_first_name = addr.first_name
  form.shipping_last_name = addr.last_name
  form.shipping_company = addr.company || ''
  form.shipping_address_line_1 = addr.address_line_1
  form.shipping_address_line_2 = addr.address_line_2 || ''
  form.shipping_city = addr.city
  form.shipping_postal_code = addr.postal_code
  form.shipping_country = addr.country
  if (addr.phone) form.phone = addr.phone
}

function onAddressSelect(event: Event) {
  const id = Number((event.target as HTMLSelectElement).value)
  if (!id) {
    selectedAddressId.value = null
    form.shipping_first_name = ''
    form.shipping_last_name = ''
    form.shipping_company = ''
    form.shipping_address_line_1 = ''
    form.shipping_address_line_2 = ''
    form.shipping_city = ''
    form.shipping_postal_code = ''
    form.shipping_country = 'RS'
    return
  }
  const addr = savedAddresses.value.find(a => a.id === id)
  if (addr) applyAddress(addr)
}

// Inline validacija
const requiredFields: Record<string, string> = {
  email: 'Email',
  shipping_first_name: 'Ime',
  shipping_last_name: 'Prezime',
  shipping_address_line_1: 'Adresa',
  shipping_city: 'Grad',
  shipping_postal_code: 'Poštanski broj',
}

function markTouched(field: string) {
  touched.value[field] = true
}

function inlineError(field: string): string {
  // Serverske greške imaju prioritet
  if (errors.value[field]?.[0]) return errors.value[field][0]
  // Klijentska validacija — samo za touched polja
  if (!touched.value[field]) return ''
  const val = (form as Record<string, string>)[field]
  if (requiredFields[field] && (!val || !val.trim())) {
    return `${requiredFields[field]} je obavezno polje.`
  }
  if (field === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
    return 'Unesite validan email.'
  }
  return ''
}

// Billing inline validacija
const billingRequiredFields: Record<string, string> = {
  'billing.first_name': 'Ime',
  'billing.last_name': 'Prezime',
  'billing.address_line_1': 'Adresa',
  'billing.city': 'Grad',
  'billing.postal_code': 'Poštanski broj',
}

function billingInlineError(field: string): string {
  const key = `billing.${field}`
  if (!touched.value[key]) return ''
  const val = (billing as Record<string, string>)[field]
  if (billingRequiredFields[key] && (!val || !val.trim())) {
    return `${billingRequiredFields[key]} je obavezno polje.`
  }
  return ''
}

function markBillingTouched(field: string) {
  touched.value[`billing.${field}`] = true
}

// Kupon validacija
async function applyCoupon() {
  if (!couponCode.value.trim()) return
  couponLoading.value = true
  couponError.value = ''
  try {
    const data = await $fetch<{ data: { code: string; type: string; value: string; discount: string } }>(`${apiBase}/v1/coupon/validate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ code: couponCode.value, subtotal: subtotal.value }),
    })
    appliedCoupon.value = data.data
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    couponError.value = err.data?.message || 'Nevažeći kupon.'
    appliedCoupon.value = null
  }
  finally { couponLoading.value = false }
}

function removeCoupon() {
  appliedCoupon.value = null
  couponCode.value = ''
  couponError.value = ''
}

// Gift card
const giftCardCode = ref('')
const giftCardLoading = ref(false)
const giftCardError = ref('')
const appliedGiftCard = ref<{ code: string; balance: number; amount: number } | null>(null)

async function applyGiftCard() {
  if (!giftCardCode.value.trim()) return
  giftCardLoading.value = true
  giftCardError.value = ''
  try {
    const data = await $fetch<{ data: { code: string; balance: string } }>(`${apiBase}/v1/gift-cards/${giftCardCode.value.toUpperCase()}/check`, {
      headers: { Accept: 'application/json' },
    })
    const balance = parseFloat(data.data.balance)
    if (balance <= 0) {
      giftCardError.value = 'Poklon kartica nema raspoloživ saldo.'
      return
    }
    appliedGiftCard.value = { code: data.data.code, balance, amount: balance }
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    giftCardError.value = err.data?.message || 'Nevažeća poklon kartica.'
  }
  finally { giftCardLoading.value = false }
}

function removeGiftCard() {
  appliedGiftCard.value = null
  giftCardCode.value = ''
  giftCardError.value = ''
}

// Loyalty points
const loyaltyBalance = ref(0)
const loyaltySpend = ref(0)

async function fetchLoyaltyBalance() {
  if (!authStore.isLoggedIn || !authStore.token) return
  try {
    const data = await $fetch<{ data: { points_balance: number } }>(`${apiBase}/v1/loyalty/balance`, {
      headers: { Authorization: `Bearer ${authStore.token}`, Accept: 'application/json' },
    })
    loyaltyBalance.value = data.data.points_balance
  }
  catch { /* silent */ }
}

// Store credits
const creditsBalance = ref(0)
const creditsSpend = ref(0)

async function fetchCreditsBalance() {
  if (!authStore.isLoggedIn || !authStore.token) return
  try {
    const data = await $fetch<{ data: { balance: string } }>(`${apiBase}/v1/store-credits/balance`, {
      headers: { Authorization: `Bearer ${authStore.token}`, Accept: 'application/json' },
    })
    creditsBalance.value = parseFloat(data.data.balance)
  }
  catch { /* silent */ }
}

// Shipping metode — učitaj kad se promeni država
async function fetchShippingMethods() {
  const country = form.shipping_country || 'RS'
  const postalCode = form.shipping_postal_code || undefined
  try {
    const data = await $fetch<{ data: ShippingOption[] }>(`${apiBase}/v1/shipping/methods`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ country, postal_code: postalCode, subtotal: subtotal.value }),
    })
    shippingMethods.value = data.data
    if (data.data.length > 0 && !selectedShippingMethod.value) {
      selectedShippingMethod.value = data.data[0].id
    }
  }
  catch { /* silent — fallback na besplatno */ }
}

// Persistent cart — čuvaj form pri napuštanju
function saveFormDraft() {
  if (import.meta.client) {
    localStorage.setItem('checkout_form', JSON.stringify({
      ...form,
      billingSameAsShipping: billingSameAsShipping.value,
      ...(!billingSameAsShipping.value ? { billing } : {}),
    }))
  }
}

function restoreFormDraft() {
  if (!import.meta.client) return
  const saved = localStorage.getItem('checkout_form')
  if (!saved) return
  try {
    const data = JSON.parse(saved)
    // Samo popuni polja koja su prazna (da ne pregazi saved address)
    for (const key of Object.keys(form) as Array<keyof typeof form>) {
      if (data[key] && !form[key]) {
        (form[key] as string) = data[key]
      }
    }
    if (data.billingSameAsShipping !== undefined) {
      billingSameAsShipping.value = data.billingSameAsShipping
    }
    if (data.billing) {
      Object.assign(billing, data.billing)
    }
  }
  catch { /* silent */ }
}

// Ponovo učitaj shipping metode kad se promeni adresa
watch(
  () => [form.shipping_country, form.shipping_postal_code],
  () => { if (form.shipping_country) fetchShippingMethods() },
)

// Watch form za auto-save draft
watch([() => ({ ...form }), billingSameAsShipping, () => ({ ...billing })], saveFormDraft, { deep: true })

async function handleSubmit() {
  loading.value = true
  error.value = ''
  errors.value = {}

  try {
    const body: Record<string, unknown> = {
      ...form,
      items: items.value.map(i => ({
        product_id: i.product.id,
        quantity: i.quantity,
      })),
    }

    if (!billingSameAsShipping.value) {
      body.billing_first_name = billing.first_name
      body.billing_last_name = billing.last_name
      body.billing_address_line_1 = billing.address_line_1
      body.billing_city = billing.city
      body.billing_postal_code = billing.postal_code
      body.billing_country = billing.country
    }

    if (selectedShippingMethod.value) {
      body.shipping_method_id = selectedShippingMethod.value
    }

    if (appliedCoupon.value) {
      body.coupon_code = appliedCoupon.value.code
    }

    if (appliedGiftCard.value && giftCardAmount.value > 0) {
      body.gift_card_code = appliedGiftCard.value.code
      body.gift_card_amount = giftCardAmount.value
    }

    if (loyaltySpend.value > 0) {
      body.loyalty_points = loyaltySpend.value
    }

    if (creditsSpend.value > 0) {
      body.store_credits = creditsSpend.value
    }

    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    }
    if (authStore.token) {
      headers.Authorization = `Bearer ${authStore.token}`
    }

    const data = await $fetch<{ data: { order_number: string } }>(`${apiBase}/v1/checkout`, {
      method: 'POST',
      headers,
      body: JSON.stringify(body),
    })

    // Očisti draft i korpu
    if (import.meta.client) localStorage.removeItem('checkout_form')
    clearCart()
    await router.push(`/checkout/success/${data.data.order_number}`)
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string; errors?: Record<string, string[]> } }
    error.value = err.data?.message || 'Došlo je do greške.'
    errors.value = err.data?.errors || {}
  }
  finally {
    loading.value = false
  }
}

if (import.meta.client && isEmpty.value) {
  navigateTo('/cart')
}

onMounted(() => {
  restoreFormDraft()
  fetchSavedAddresses()
  fetchShippingMethods()
  fetchPaymentMethods()
  fetchLoyaltyBalance()
  fetchCreditsBalance()
  fetchTax()
})

useHead({ title: 'Kasa — eLokal' })
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: 'Korpa', to: '/cart' }, { label: 'Kasa' }]" />

    <h1 class="text-2xl font-bold text-gray-800 mb-6">Kasa</h1>

    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm mb-6">
      {{ error }}
    </div>

    <form novalidate @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Contact -->
          <div class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Kontakt</h2>
            <div class="space-y-4">
              <UiAtomsInput
                v-model="form.email" label="Email" type="email" required
                :error="inlineError('email')"
                @blur="markTouched('email')"
              />
              <UiAtomsInput
                v-model="form.phone" label="Telefon" type="tel"
                :error="inlineError('phone')"
              />
            </div>
          </div>

          <!-- Saved addresses -->
          <div v-if="savedAddresses.length > 0" class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Sačuvane adrese</h2>
            <select
              :value="selectedAddressId || ''"
              class="w-full px-4 py-2.5 text-sm border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500"
              @change="onAddressSelect"
            >
              <option value="">Unesite novu adresu</option>
              <option v-for="addr in savedAddresses" :key="addr.id" :value="addr.id">
                {{ addr.label ? `${addr.label} — ` : '' }}{{ addr.first_name }} {{ addr.last_name }}, {{ addr.address_line_1 }}, {{ addr.city }}
              </option>
            </select>
          </div>

          <!-- Shipping -->
          <div class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Adresa za dostavu</h2>
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <UiAtomsInput
                  v-model="form.shipping_first_name" label="Ime" required
                  :error="inlineError('shipping_first_name')"
                  @blur="markTouched('shipping_first_name')"
                />
                <UiAtomsInput
                  v-model="form.shipping_last_name" label="Prezime" required
                  :error="inlineError('shipping_last_name')"
                  @blur="markTouched('shipping_last_name')"
                />
              </div>
              <UiAtomsInput v-model="form.shipping_company" label="Firma" />
              <UiAtomsInput
                v-model="form.shipping_address_line_1" label="Adresa" required
                :error="inlineError('shipping_address_line_1')"
                @blur="markTouched('shipping_address_line_1')"
              />
              <UiAtomsInput v-model="form.shipping_address_line_2" label="Adresa 2" />
              <div class="grid grid-cols-2 gap-4">
                <UiAtomsInput
                  v-model="form.shipping_city" label="Grad" required
                  :error="inlineError('shipping_city')"
                  @blur="markTouched('shipping_city')"
                />
                <UiAtomsInput
                  v-model="form.shipping_postal_code" label="Poštanski broj" required
                  :error="inlineError('shipping_postal_code')"
                  @blur="markTouched('shipping_postal_code')"
                />
              </div>
            </div>
          </div>

          <!-- Billing address -->
          <div class="border border-gray-200 p-6">
            <label class="flex items-center gap-3 cursor-pointer">
              <input
                v-model="billingSameAsShipping"
                type="checkbox"
                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
              />
              <span class="text-sm font-medium text-gray-700">Adresa za naplatu je ista kao za dostavu</span>
            </label>

            <div v-if="!billingSameAsShipping" class="mt-4 space-y-4">
              <h3 class="text-base font-bold text-gray-800">Adresa za naplatu</h3>
              <div class="grid grid-cols-2 gap-4">
                <UiAtomsInput
                  v-model="billing.first_name" label="Ime" required
                  :error="billingInlineError('first_name')"
                  @blur="markBillingTouched('first_name')"
                />
                <UiAtomsInput
                  v-model="billing.last_name" label="Prezime" required
                  :error="billingInlineError('last_name')"
                  @blur="markBillingTouched('last_name')"
                />
              </div>
              <UiAtomsInput
                v-model="billing.address_line_1" label="Adresa" required
                :error="billingInlineError('address_line_1')"
                @blur="markBillingTouched('address_line_1')"
              />
              <div class="grid grid-cols-2 gap-4">
                <UiAtomsInput
                  v-model="billing.city" label="Grad" required
                  :error="billingInlineError('city')"
                  @blur="markBillingTouched('city')"
                />
                <UiAtomsInput
                  v-model="billing.postal_code" label="Poštanski broj" required
                  :error="billingInlineError('postal_code')"
                  @blur="markBillingTouched('postal_code')"
                />
              </div>
            </div>
          </div>

          <!-- Shipping method -->
          <div v-if="shippingMethods.length > 0" class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Način dostave</h2>
            <div class="space-y-2">
              <label
                v-for="method in shippingMethods"
                :key="method.id"
                class="flex items-center justify-between border border-gray-200 p-3 cursor-pointer transition-colors"
                :class="selectedShippingMethod === method.id ? 'border-primary-500 bg-primary-50' : 'hover:border-gray-300'"
              >
                <div class="flex items-center gap-3">
                  <input
                    v-model="selectedShippingMethod"
                    type="radio"
                    :value="method.id"
                    class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                  />
                  <div>
                    <p class="text-sm font-medium text-gray-800">{{ method.name }}</p>
                    <p v-if="method.estimated_days" class="text-xs text-gray-400">{{ method.estimated_days }} radnih dana</p>
                  </div>
                </div>
                <span class="text-sm font-medium" :class="parseFloat(method.cost) === 0 ? 'text-green-600' : 'text-gray-800'">
                  {{ parseFloat(method.cost) === 0 ? 'Besplatno' : `${parseFloat(method.cost).toLocaleString('sr-RS', { minimumFractionDigits: 2 })} RSD` }}
                </span>
              </label>
            </div>
          </div>

          <!-- Coupon -->
          <div class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Kupon</h2>
            <div v-if="appliedCoupon" class="flex items-center justify-between bg-green-50 border border-green-200 px-4 py-3">
              <div>
                <p class="text-sm font-medium text-green-800">{{ appliedCoupon.code }}</p>
                <p class="text-xs text-green-600">−{{ parseFloat(appliedCoupon.discount).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</p>
              </div>
              <button class="text-sm text-red-500 hover:text-red-700" @click="removeCoupon">Ukloni</button>
            </div>
            <div v-else>
              <div class="flex gap-2">
                <input
                  v-model="couponCode"
                  type="text"
                  placeholder="Unesite kod kupona"
                  class="flex-1 px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  @keydown.enter.prevent="applyCoupon"
                />
                <UiAtomsButton variant="secondary" :loading="couponLoading" @click="applyCoupon">Primeni</UiAtomsButton>
              </div>
              <p v-if="couponError" class="mt-1 text-sm text-red-600">{{ couponError }}</p>
            </div>
          </div>

          <!-- Gift card -->
          <div class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Poklon kartica</h2>
            <div v-if="appliedGiftCard" class="space-y-3">
              <div class="flex items-center justify-between bg-green-50 border border-green-200 px-4 py-3">
                <div>
                  <p class="text-sm font-medium text-green-800">{{ appliedGiftCard.code }}</p>
                  <p class="text-xs text-green-600">Saldo: {{ appliedGiftCard.balance.toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</p>
                </div>
                <button class="text-sm text-red-500 hover:text-red-700" @click="removeGiftCard">Ukloni</button>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Iznos za korišćenje</label>
                <input
                  v-model.number="appliedGiftCard.amount"
                  type="number"
                  :max="appliedGiftCard.balance"
                  min="0"
                  step="0.01"
                  class="w-40 px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
            </div>
            <div v-else>
              <div class="flex gap-2">
                <input
                  v-model="giftCardCode"
                  type="text"
                  placeholder="Unesite kod poklon kartice"
                  class="flex-1 px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  @keydown.enter.prevent="applyGiftCard"
                />
                <UiAtomsButton variant="secondary" :loading="giftCardLoading" @click="applyGiftCard">Primeni</UiAtomsButton>
              </div>
              <p v-if="giftCardError" class="mt-1 text-sm text-red-600">{{ giftCardError }}</p>
            </div>
          </div>

          <!-- Loyalty points -->
          <div v-if="authStore.isLoggedIn && loyaltyBalance > 0" class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Poeni lojalnosti</h2>
            <p class="text-sm text-gray-600 mb-3">
              Raspoloživo: <span class="font-semibold text-primary-600">{{ loyaltyBalance }}</span> poena (1 poen = 1 RSD)
            </p>
            <div class="flex items-center gap-3">
              <input
                v-model.number="loyaltySpend"
                type="number"
                :max="loyaltyBalance"
                min="0"
                placeholder="Broj poena"
                class="w-40 px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
              <span class="text-sm text-gray-500">= {{ loyaltySpend.toLocaleString('sr-RS') }} RSD popusta</span>
            </div>
          </div>

          <!-- Store credits -->
          <div v-if="authStore.isLoggedIn && creditsBalance > 0" class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Krediti prodavnice</h2>
            <p class="text-sm text-gray-600 mb-3">
              Raspoloživo: <span class="font-semibold text-primary-600">{{ creditsBalance.toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span>
            </p>
            <div class="flex items-center gap-3">
              <input
                v-model.number="creditsSpend"
                type="number"
                :max="creditsBalance"
                min="0"
                step="0.01"
                placeholder="Iznos"
                class="w-40 px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
              <span class="text-sm text-gray-500">RSD</span>
            </div>
          </div>

          <!-- Payment method -->
          <div v-if="paymentMethods.length > 0" class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Način plaćanja</h2>
            <div class="space-y-2">
              <label
                v-for="method in paymentMethods"
                :key="method.id"
                class="flex items-start gap-3 border border-gray-200 p-3 cursor-pointer transition-colors"
                :class="selectedPaymentMethod === method.id ? 'border-primary-500 bg-primary-50' : 'hover:border-gray-300'"
              >
                <input
                  v-model="selectedPaymentMethod"
                  type="radio"
                  :value="method.id"
                  class="w-4 h-4 mt-0.5 text-primary-600 border-gray-300 focus:ring-primary-500"
                />
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-800">{{ method.name }}</p>
                    <span v-if="parseFloat(method.additional_cost) > 0" class="text-xs text-gray-500">
                      +{{ parseFloat(method.additional_cost).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD
                    </span>
                  </div>
                  <p v-if="method.description" class="text-xs text-gray-500 mt-0.5">{{ method.description }}</p>
                </div>
              </label>
            </div>
            <!-- Instrukcije za izabranu metodu -->
            <div v-if="selectedPaymentInfo?.instructions" class="mt-3 bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
              {{ selectedPaymentInfo.instructions }}
            </div>
          </div>

          <!-- Notes -->
          <div class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Napomena</h2>
            <textarea
              v-model="form.notes" rows="3"
              placeholder="Opciona napomena za narudžbinu..."
              class="w-full px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
        </div>

        <!-- Order summary -->
        <div>
          <div class="border border-gray-200 p-6 sticky top-20">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Vaša narudžbina</h2>

            <div class="space-y-3 mb-4">
              <div v-for="item in items" :key="item.product.id" class="flex justify-between text-sm">
                <span class="text-gray-600">{{ item.product.name }} × {{ item.quantity }}</span>
                <span class="font-medium">{{ (parseFloat(item.product.effective_price) * item.quantity).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span>
              </div>
            </div>

            <hr class="my-4" />

            <div class="flex justify-between text-sm mb-2">
              <span class="text-gray-600">Međuzbir</span>
              <span class="font-medium">{{ total }}</span>
            </div>
            <div v-if="appliedCoupon" class="flex justify-between text-sm mb-2">
              <span class="text-green-600">Kupon ({{ appliedCoupon.code }})</span>
              <span class="text-green-600 font-medium">−{{ parseFloat(appliedCoupon.discount).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span>
            </div>
            <div v-if="giftCardAmount > 0" class="flex justify-between text-sm mb-2">
              <span class="text-green-600">Poklon kartica</span>
              <span class="text-green-600 font-medium">−{{ giftCardAmount.toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span>
            </div>
            <div v-if="loyaltySpend > 0" class="flex justify-between text-sm mb-2">
              <span class="text-green-600">Poeni ({{ loyaltySpend }})</span>
              <span class="text-green-600 font-medium">−{{ loyaltySpend.toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span>
            </div>
            <div v-if="creditsSpend > 0" class="flex justify-between text-sm mb-2">
              <span class="text-green-600">Krediti</span>
              <span class="text-green-600 font-medium">−{{ creditsSpend.toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span>
            </div>
            <div class="flex justify-between text-sm mb-2">
              <span class="text-gray-600">Dostava</span>
              <span v-if="!selectedShippingMethod && shippingMethods.length === 0" class="text-gray-400">Unesite adresu za izračun</span>
              <span v-else-if="shippingCost === 0" class="text-green-600 font-medium">Besplatno</span>
              <span v-else class="font-medium">{{ shippingCost.toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span>
            </div>
            <div v-if="taxAmount > 0" class="flex justify-between text-sm mb-2">
              <span class="text-gray-600">{{ taxRateName || 'Porez' }}</span>
              <span class="font-medium">{{ taxAmount.toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span>
            </div>

            <hr class="my-4" />

            <div class="flex justify-between text-lg font-bold mb-6">
              <span>Ukupno</span>
              <span>{{ orderTotal.toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span>
            </div>

            <UiAtomsButton type="submit" :loading="loading" class="w-full" size="lg">
              Poruči
            </UiAtomsButton>

            <p v-if="selectedPaymentInfo" class="text-xs text-gray-400 text-center mt-3">{{ selectedPaymentInfo.name }}</p>

            <!-- Trust badges -->
            <div class="mt-6 pt-4 border-t border-gray-100 space-y-3">
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                </svg>
                Sigurna kupovina — vaši podaci su zaštićeni
              </div>
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Besplatna dostava na sve narudžbine
              </div>
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                14 dana za povrat bez pitanja
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
