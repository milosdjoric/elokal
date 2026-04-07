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

    <form @submit.prevent="handleSubmit">
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
              <span class="text-gray-600">Subtotal</span>
              <span class="font-medium">{{ total }}</span>
            </div>
            <div class="flex justify-between text-sm mb-4">
              <span class="text-gray-600">Dostava</span>
              <span class="text-gray-400">Besplatno</span>
            </div>

            <hr class="my-4" />

            <div class="flex justify-between text-lg font-bold mb-6">
              <span>Ukupno</span>
              <span>{{ total }}</span>
            </div>

            <UiAtomsButton type="submit" :loading="loading" class="w-full" size="lg">
              Poruči
            </UiAtomsButton>

            <p class="text-xs text-gray-400 text-center mt-3">Plaćanje pouzećem</p>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
