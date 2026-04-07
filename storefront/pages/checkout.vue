<script setup lang="ts">
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

const loading = ref(false)
const error = ref('')
const errors = ref<Record<string, string[]>>({})

async function handleSubmit() {
  loading.value = true
  error.value = ''
  errors.value = {}

  try {
    const body = {
      ...form,
      items: items.value.map(i => ({
        product_id: i.product.id,
        quantity: i.quantity,
      })),
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

function fieldError(field: string): string {
  return errors.value[field]?.[0] || ''
}

if (import.meta.client && isEmpty.value) {
  navigateTo('/cart')
}

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
              <UiAtomsInput v-model="form.email" label="Email" type="email" required :error="fieldError('email')" />
              <UiAtomsInput v-model="form.phone" label="Telefon" type="tel" :error="fieldError('phone')" />
            </div>
          </div>

          <!-- Shipping -->
          <div class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Adresa za dostavu</h2>
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <UiAtomsInput v-model="form.shipping_first_name" label="Ime" required :error="fieldError('shipping_first_name')" />
                <UiAtomsInput v-model="form.shipping_last_name" label="Prezime" required :error="fieldError('shipping_last_name')" />
              </div>
              <UiAtomsInput v-model="form.shipping_company" label="Firma" />
              <UiAtomsInput v-model="form.shipping_address_line_1" label="Adresa" required :error="fieldError('shipping_address_line_1')" />
              <UiAtomsInput v-model="form.shipping_address_line_2" label="Adresa 2" />
              <div class="grid grid-cols-2 gap-4">
                <UiAtomsInput v-model="form.shipping_city" label="Grad" required :error="fieldError('shipping_city')" />
                <UiAtomsInput v-model="form.shipping_postal_code" label="Poštanski broj" required :error="fieldError('shipping_postal_code')" />
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Napomena</h2>
            <textarea v-model="form.notes" rows="3" placeholder="Opciona napomena za narudžbinu..." class="w-full px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500" />
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
