<script setup lang="ts">
import type { Product } from '~/types'

const { total, count, items } = useCart()
const cartStore = useCartStore()
const { get } = useApi()
const { config: shippingConfig, isFreeShipping, remainingForFree, freeProgress, estimatedCost } = useShippingConfig()

const crossSellProducts = ref<Product[]>([])

// Porez
const taxAmount = ref(0)
const taxRate = ref(0)

async function fetchTaxEstimate() {
  if (cartStore.total <= 0) { taxAmount.value = 0; return }
  try {
    const data = await $fetch<{ data: { tax: number; rate: number } }>(`${apiBase}/v1/tax/calculate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ subtotal: cartStore.total, country: 'RS' }),
    })
    taxAmount.value = data.data.tax
    taxRate.value = data.data.rate
  }
  catch { taxAmount.value = 0 }
}

watch(() => cartStore.total, fetchTaxEstimate)

// Coupon
const { apiBase } = useApi()
const couponCode = ref('')
const couponLoading = ref(false)
const couponError = ref('')
const appliedCoupon = ref<{ code: string; discount: string } | null>(null)

async function applyCoupon() {
  if (!couponCode.value.trim()) return
  couponLoading.value = true
  couponError.value = ''
  try {
    const data = await $fetch<{ data: { code: string; discount: string } }>(`${apiBase}/v1/coupon/validate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ code: couponCode.value, subtotal: cartStore.total }),
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

async function fetchCrossSell() {
  try {
    // Probaj cross-sell iz prvog proizvoda u korpi
    if (items.value.length > 0) {
      const firstSlug = items.value[0].product.slug
      const data = await get<{ data: Product }>(`/v1/products/${firstSlug}`)
      if (data.data.cross_sell_products?.length) {
        const cartIds = new Set(items.value.map(i => i.product.id))
        crossSellProducts.value = data.data.cross_sell_products.filter((p: Product) => !cartIds.has(p.id)).slice(0, 4)
        if (crossSellProducts.value.length > 0) return
      }
    }
    // Fallback na featured
    const data = await get<{ data: Product[] }>('/v1/products', { featured: 1, per_page: 4 })
    const cartIds = new Set(items.value.map(i => i.product.id))
    crossSellProducts.value = data.data.filter((p: Product) => !cartIds.has(p.id)).slice(0, 4)
  }
  catch { /* silent */ }
}

function formatPrice(price: string): string {
  return `${parseFloat(price).toLocaleString('sr-RS', { minimumFractionDigits: 2 })} RSD`
}

function primaryImage(product: Product): string | null {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
}

onMounted(() => {
  fetchCrossSell()
  fetchTaxEstimate()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Free shipping progress -->
    <div class="border border-gray-200 p-4">
      <div v-if="isFreeShipping(cartStore.total)" class="flex items-center gap-2 text-sm text-green-600 font-medium">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        Ostvarili ste besplatnu dostavu!
      </div>
      <div v-else>
        <p class="text-sm text-gray-600 mb-2">
          Još <span class="font-semibold text-primary-600">{{ remainingForFree(cartStore.total).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span> do besplatne dostave
        </p>
        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full bg-primary-500 rounded-full transition-all" :style="{ width: `${freeProgress(cartStore.total)}%` }" />
        </div>
      </div>
    </div>

    <!-- Totals -->
    <div class="border border-gray-200 p-6 space-y-3">
      <h3 class="text-lg font-bold mb-4">Pregled narudžbine</h3>

      <div class="flex justify-between text-sm">
        <span class="text-gray-600">Stavke ({{ count }})</span>
        <span class="font-medium">{{ total }}</span>
      </div>
      <div class="flex justify-between text-sm">
        <span class="text-gray-600">Dostava</span>
        <span v-if="isFreeShipping(cartStore.total)" class="text-green-600 font-medium">Besplatno</span>
        <span v-else-if="estimatedCost(cartStore.total) !== null" class="font-medium">
          {{ estimatedCost(cartStore.total)!.toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD
        </span>
        <span v-else class="text-gray-400">Izračunava se u sledećem koraku</span>
      </div>
      <div class="flex justify-between text-sm">
        <span class="text-gray-600">Porez (PDV {{ taxRate }}%)</span>
        <span v-if="taxAmount > 0" class="font-medium">~{{ taxAmount.toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span>
        <span v-else class="text-gray-400">—</span>
      </div>
      <hr class="my-2" />
      <div class="flex justify-between text-lg font-bold">
        <span>Ukupno</span>
        <span>{{ (cartStore.total + taxAmount + (isFreeShipping(cartStore.total) ? 0 : (estimatedCost(cartStore.total) ?? 0))).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</span>
      </div>

      <!-- Coupon -->
      <div class="mt-3 pt-3 border-t border-gray-100">
        <div v-if="appliedCoupon" class="flex items-center justify-between bg-green-50 border border-green-200 px-3 py-2 mb-3">
          <div>
            <p class="text-sm font-medium text-green-800">{{ appliedCoupon.code }}</p>
            <p class="text-xs text-green-600">−{{ parseFloat(appliedCoupon.discount).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD</p>
          </div>
          <button class="text-xs text-red-500 hover:text-red-700" @click="removeCoupon">×</button>
        </div>
        <div v-else>
          <div class="flex gap-1.5">
            <input
              v-model="couponCode"
              type="text"
              placeholder="Kod kupona"
              class="flex-1 px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
              @keydown.enter.prevent="applyCoupon"
            />
            <UiAtomsButton variant="secondary" size="sm" :loading="couponLoading" @click="applyCoupon">Primeni</UiAtomsButton>
          </div>
          <p v-if="couponError" class="mt-1 text-xs text-red-600">{{ couponError }}</p>
        </div>
      </div>

      <NuxtLink to="/porudzbina">
        <UiAtomsButton class="w-full mt-4" size="lg">Nastavi ka plaćanju</UiAtomsButton>
      </NuxtLink>

      <NuxtLink to="/proizvodi" class="block text-center text-sm text-primary-600 hover:text-primary-800 mt-2">
        ← Nastavi kupovinu
      </NuxtLink>
    </div>

    <!-- Cross-sell -->
    <div v-if="crossSellProducts.length > 0" class="border border-gray-200 p-6">
      <h3 class="text-sm font-semibold text-gray-800 mb-4">Često se kupuje uz...</h3>
      <div class="space-y-3">
        <NuxtLink
          v-for="product in crossSellProducts"
          :key="product.id"
          :to="`/proizvodi/${product.slug}`"
          class="flex items-center gap-3 hover:bg-gray-50 -mx-2 px-2 py-1 rounded transition-colors"
        >
          <img
            v-if="primaryImage(product)"
            :src="primaryImage(product)!"
            :alt="product.name"
            class="w-12 h-12 object-cover rounded"
          />
          <div v-else class="w-12 h-12 bg-gray-100 rounded flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate">{{ product.name }}</p>
            <p class="text-sm text-primary-600">{{ formatPrice(product.effective_price) }}</p>
          </div>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>
