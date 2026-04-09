<script setup lang="ts">
import type { Product, ProductVariant } from '~/types'

const props = defineProps<{ modelValue: boolean; product: Product | null }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()
const { addToCart } = useCart()
const { get } = useApi()

const qty = ref(1)
const fullProduct = ref<Product | null>(null)
const loadingFull = ref(false)
const selectedVariant = ref<ProductVariant | null>(null)

const hasVariants = computed(() => (fullProduct.value?.variants?.length ?? 0) > 0)

const displayPrice = computed(() => {
  if (selectedVariant.value) return selectedVariant.value.effective_price
  return fullProduct.value?.effective_price ?? fullProduct.value?.price ?? '0'
})

const displayStock = computed(() => {
  if (selectedVariant.value) return selectedVariant.value.stock_quantity
  return fullProduct.value?.stock_quantity ?? 0
})

const canAdd = computed(() => {
  if (hasVariants.value && !selectedVariant.value) return false
  return displayStock.value > 0
})

function primaryImg(): string | null {
  if (selectedVariant.value?.images?.length) {
    return resolveImageUrl(selectedVariant.value.images[0].image_path)
  }
  const img = fullProduct.value?.images?.find(i => i.is_primary) || fullProduct.value?.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
}

function handleAdd() {
  if (fullProduct.value) {
    addToCart(fullProduct.value, qty.value)
    emit('update:modelValue', false)
    qty.value = 1
  }
}

function onVariantSelect(variant: ProductVariant | null) {
  selectedVariant.value = variant
}

// Kad se modal otvori — fetch pun proizvod sa varijantama
watch(() => props.modelValue, async (open) => {
  if (!open) {
    fullProduct.value = null
    selectedVariant.value = null
    qty.value = 1
    return
  }
  if (!props.product) return

  // Odmah prikaži basic podatke
  fullProduct.value = props.product

  // Fetch pun proizvod ako ima varijante ili ih nemamo
  if (!props.product.variants?.length) {
    loadingFull.value = true
    try {
      const data = await get<{ data: Product }>(`/v1/products/${props.product.slug}`)
      fullProduct.value = data.data
    }
    catch { /* koristi basic */ }
    finally { loadingFull.value = false }
  }
})
</script>

<template>
  <UiMoleculesModal :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
    <div v-if="fullProduct" class="flex flex-col md:flex-row md:items-start gap-6">
      <div class="md:w-1/2">
        <img v-if="primaryImg()" :src="primaryImg()!" :alt="fullProduct.name" loading="lazy" class="w-full aspect-square object-cover" />
      </div>
      <div class="md:w-1/2">
        <div class="flex items-start justify-between gap-2 mb-2">
          <h2 class="text-lg font-bold text-gray-800">{{ fullProduct.name }}</h2>
          <UiAtomsBadge v-if="fullProduct.is_on_sale && fullProduct.sale_percentage" variant="sale" class="text-xs flex-shrink-0">
            -{{ fullProduct.sale_percentage }}%
          </UiAtomsBadge>
        </div>

        <UiMoleculesPriceDisplay
          :price="displayPrice"
          :sale-price="selectedVariant?.sale_price || (!hasVariants ? fullProduct.sale_price : undefined)"
          :sale-percentage="!hasVariants ? fullProduct.sale_percentage : undefined"
          :is-on-sale="!hasVariants ? fullProduct.is_on_sale : false"
          size="md"
          class="mb-3"
        />

        <p v-if="fullProduct.short_description" class="text-sm text-gray-600 mb-4">{{ fullProduct.short_description }}</p>

        <!-- Varijante -->
        <div v-if="hasVariants && !loadingFull" class="mb-4">
          <ProductVariantSelector
            :variants="fullProduct.variants!"
            @select="onVariantSelect"
          />
        </div>
        <div v-else-if="loadingFull" class="mb-4">
          <UiAtomsSkeleton height="40px" />
        </div>

        <!-- Add to cart -->
        <div v-if="canAdd" class="space-y-3 mb-4">
          <UiMoleculesQuantitySelector v-model="qty" />
          <UiAtomsButton class="w-full" @click="handleAdd">Dodaj u korpu</UiAtomsButton>
        </div>
        <p v-else-if="hasVariants && !selectedVariant" class="text-sm text-gray-500 mb-4">Izaberite varijantu</p>
        <p v-else class="text-red-500 text-sm mb-4">Nema na stanju</p>

        <NuxtLink :to="`/proizvodi/${fullProduct.slug}`" class="text-sm text-primary-600 hover:underline" @click="emit('update:modelValue', false)">
          Pogledaj detalje →
        </NuxtLink>
      </div>
    </div>
  </UiMoleculesModal>
</template>
