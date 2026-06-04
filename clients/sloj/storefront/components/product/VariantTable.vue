<script setup lang="ts">
import type { Product, ProductVariant } from '~/types'

const props = defineProps<{
  variants: ProductVariant[]
  product: Product
}>()

const emit = defineEmits<{
  select: [variant: ProductVariant | null]
}>()

const { addToCart } = useCart()

// Količina po varijanti
const quantities = ref<Map<number, number>>(new Map())

function getQty(variantId: number): number {
  return quantities.value.get(variantId) ?? 1
}

function setQty(variantId: number, val: number) {
  quantities.value.set(variantId, Math.max(1, val))
}

// Izvuci unikatne atribute
const attributeNames = computed(() => {
  const names: { id: number; name: string }[] = []
  const seen = new Set<number>()
  for (const v of props.variants) {
    for (const a of (v.attributes || [])) {
      if (!seen.has(a.attribute_id)) {
        seen.add(a.attribute_id)
        names.push({ id: a.attribute_id, name: a.attribute_name })
      }
    }
  }
  return names
})

// Samo aktivne varijante
const activeVariants = computed(() => props.variants.filter(v => v.is_active))

function getAttrValue(variant: ProductVariant, attrId: number): string {
  return variant.attributes?.find(a => a.attribute_id === attrId)?.value || '—'
}

function getColorHex(variant: ProductVariant, attrId: number): string | null {
  return variant.attributes?.find(a => a.attribute_id === attrId)?.color_hex || null
}

function isInStock(variant: ProductVariant): boolean {
  return variant.stock_quantity > 0
}

function handleAddToCart(variant: ProductVariant) {
  const qty = getQty(variant.id)
  // Emituj selekciju varijante za galeriju
  emit('select', variant)
  addToCart({
    ...props.product,
    effective_price: variant.effective_price,
    stock_quantity: variant.stock_quantity,
  }, qty)
}
</script>

<template>
  <div class="overflow-x-auto border border-gray-200 rounded-lg">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
          <th
            v-for="attr in attributeNames"
            :key="attr.id"
            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"
          >
            {{ attr.name }}
          </th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Cena</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-28">Količina</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-32" />
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="variant in activeVariants" :key="variant.id" class="hover:bg-gray-50">
          <td
            v-for="attr in attributeNames"
            :key="attr.id"
            class="px-4 py-3 text-gray-700"
          >
            <span class="flex items-center gap-1.5">
              <span
                v-if="getColorHex(variant, attr.id)"
                class="w-4 h-4 rounded-full border border-gray-300 inline-block flex-shrink-0"
                :style="{ backgroundColor: getColorHex(variant, attr.id)! }"
              />
              {{ getAttrValue(variant, attr.id) }}
            </span>
          </td>
          <td class="px-4 py-3 text-right font-medium text-gray-800 tabular-nums">
            {{ formatPrice(variant.effective_price) }}
          </td>
          <td class="px-4 py-3 text-center">
            <span
              class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full"
              :class="isInStock(variant) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            >
              {{ isInStock(variant) ? 'Na stanju' : 'Nema' }}
            </span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-center gap-1">
              <button
                class="w-7 h-7 flex items-center justify-center border border-gray-300 rounded text-gray-600 hover:bg-gray-100 disabled:opacity-30"
                :disabled="!isInStock(variant) || getQty(variant.id) <= 1"
                @click="setQty(variant.id, getQty(variant.id) - 1)"
              >
                −
              </button>
              <input
                :value="getQty(variant.id)"
                type="number"
                min="1"
                :max="variant.stock_quantity"
                class="w-10 text-center text-sm border border-gray-300 rounded py-1 focus:outline-none focus:ring-1 focus:ring-primary-500"
                :disabled="!isInStock(variant)"
                @input="setQty(variant.id, parseInt(($event.target as HTMLInputElement).value) || 1)"
              />
              <button
                class="w-7 h-7 flex items-center justify-center border border-gray-300 rounded text-gray-600 hover:bg-gray-100 disabled:opacity-30"
                :disabled="!isInStock(variant) || getQty(variant.id) >= variant.stock_quantity"
                @click="setQty(variant.id, getQty(variant.id) + 1)"
              >
                +
              </button>
            </div>
          </td>
          <td class="px-4 py-3 text-center">
            <button
              class="px-3 py-1.5 text-xs font-medium rounded bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
              :disabled="!isInStock(variant)"
              @click="handleAddToCart(variant)"
            >
              U korpu
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
