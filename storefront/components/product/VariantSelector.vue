<script setup lang="ts">
import type { ProductVariant, VariantAttribute } from '~/types'

const props = defineProps<{
  variants: ProductVariant[]
  initialSelection?: Record<string, string>
}>()

const emit = defineEmits<{
  select: [variant: ProductVariant | null]
  selectionChange: [params: Record<string, string>]
}>()

// Izvuci unikatne atribute iz svih varijanti
interface AttributeOption {
  attribute_id: number
  attribute_name: string
  attribute_slug: string
  attribute_type: 'select' | 'color' | 'image'
  values: Array<{
    value_id: number
    value: string
    color_hex: string | null
    image_path: string | null
  }>
}

const attributes = computed<AttributeOption[]>(() => {
  const map = new Map<number, AttributeOption>()

  for (const variant of props.variants) {
    if (!variant.attributes) continue
    for (const attr of variant.attributes) {
      if (!map.has(attr.attribute_id)) {
        map.set(attr.attribute_id, {
          attribute_id: attr.attribute_id,
          attribute_name: attr.attribute_name,
          attribute_slug: attr.attribute_slug,
          attribute_type: attr.attribute_type,
          values: [],
        })
      }
      const existing = map.get(attr.attribute_id)!
      if (!existing.values.find(v => v.value_id === attr.value_id)) {
        existing.values.push({
          value_id: attr.value_id,
          value: attr.value,
          color_hex: attr.color_hex,
          image_path: attr.image_path,
        })
      }
    }
  }

  return Array.from(map.values())
})

// Selekcija po atributu: { attribute_id: value_id }
const selected = reactive<Record<number, number>>({})

// Auto-select: jedine dostupne varijante + deep link iz URL query params
onMounted(() => {
  for (const attr of attributes.value) {
    if (attr.values.length === 1) {
      selected[attr.attribute_id] = attr.values[0].value_id
    }
  }

  // Deep link — preselektuj iz initialSelection (slug → value name)
  if (props.initialSelection) {
    for (const attr of attributes.value) {
      const queryValue = props.initialSelection[attr.attribute_slug]
      if (queryValue) {
        const match = attr.values.find(v => v.value.toLowerCase() === queryValue.toLowerCase())
        if (match) {
          selected[attr.attribute_id] = match.value_id
        }
      }
    }
  }
})

// Nađi matching varijantu
const selectedVariant = computed<ProductVariant | null>(() => {
  if (attributes.value.length === 0) return null

  // Svi atributi moraju biti selektovani
  const allSelected = attributes.value.every(a => selected[a.attribute_id])
  if (!allSelected) return null

  return props.variants.find(v => {
    if (!v.attributes || !v.is_active) return false
    return v.attributes.every(attr => selected[attr.attribute_id] === attr.value_id)
  }) ?? null
})

// Da li je vrednost dostupna (postoji aktivna varijanta sa tom vrednošću i trenutnim ostalim izborima)
function isValueAvailable(attributeId: number, valueId: number): boolean {
  return props.variants.some(v => {
    if (!v.attributes || !v.is_active || v.stock_quantity <= 0) return false

    return v.attributes.every(attr => {
      if (attr.attribute_id === attributeId) {
        return attr.value_id === valueId
      }
      // Za ostale atribute — ili nije selektovan, ili se poklapa
      return !selected[attr.attribute_id] || selected[attr.attribute_id] === attr.value_id
    })
  })
}

function selectValue(attributeId: number, valueId: number) {
  selected[attributeId] = valueId
}

// Emit kad se varijanta promeni + ažuriraj URL parametre
watch(selectedVariant, (v) => emit('select', v), { immediate: true })

watch(selected, () => {
  const params: Record<string, string> = {}
  for (const attr of attributes.value) {
    const valueId = selected[attr.attribute_id]
    if (valueId) {
      const val = attr.values.find(v => v.value_id === valueId)
      if (val) params[attr.attribute_slug] = val.value
    }
  }
  emit('selectionChange', params)
}, { deep: true })
</script>

<template>
  <div v-if="attributes.length > 0" class="space-y-4">
    <div v-for="attr in attributes" :key="attr.attribute_id">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ attr.attribute_name }}
        <span v-if="selected[attr.attribute_id]" class="text-gray-400 font-normal ml-1">
          — {{ attr.values.find(v => v.value_id === selected[attr.attribute_id])?.value }}
        </span>
      </label>

      <!-- Color swatch -->
      <div v-if="attr.attribute_type === 'color'" class="flex flex-wrap gap-2">
        <button
          v-for="val in attr.values"
          :key="val.value_id"
          type="button"
          :title="val.value"
          :disabled="!isValueAvailable(attr.attribute_id, val.value_id)"
          class="w-9 h-9 rounded-full border-2 transition-all relative"
          :class="[
            selected[attr.attribute_id] === val.value_id
              ? 'border-primary-600 ring-2 ring-primary-200'
              : 'border-gray-300 hover:border-gray-400',
            !isValueAvailable(attr.attribute_id, val.value_id) && 'opacity-30 cursor-not-allowed',
          ]"
          :style="{ backgroundColor: val.color_hex || '#ccc' }"
          @click="selectValue(attr.attribute_id, val.value_id)"
        >
          <!-- Crossed out line for unavailable -->
          <span
            v-if="!isValueAvailable(attr.attribute_id, val.value_id)"
            class="absolute inset-0 flex items-center justify-center"
          >
            <span class="block w-full h-px bg-gray-400 rotate-45 transform" />
          </span>
        </button>
      </div>

      <!-- Dropdown -->
      <div v-else-if="attr.attribute_type === 'select'" >
        <select
          :value="selected[attr.attribute_id] || ''"
          class="w-full px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
          @change="selectValue(attr.attribute_id, Number(($event.target as HTMLSelectElement).value))"
        >
          <option value="" disabled>Izaberite {{ attr.attribute_name.toLowerCase() }}</option>
          <option
            v-for="val in attr.values"
            :key="val.value_id"
            :value="val.value_id"
            :disabled="!isValueAvailable(attr.attribute_id, val.value_id)"
          >
            {{ val.value }}{{ !isValueAvailable(attr.attribute_id, val.value_id) ? ' (nema na stanju)' : '' }}
          </option>
        </select>
      </div>

      <!-- Image swatch -->
      <div v-else-if="attr.attribute_type === 'image'" class="flex flex-wrap gap-2">
        <button
          v-for="val in attr.values"
          :key="val.value_id"
          type="button"
          :title="val.value"
          :disabled="!isValueAvailable(attr.attribute_id, val.value_id)"
          class="w-12 h-12 border-2 rounded overflow-hidden transition-all"
          :class="[
            selected[attr.attribute_id] === val.value_id
              ? 'border-primary-600 ring-2 ring-primary-200'
              : 'border-gray-300 hover:border-gray-400',
            !isValueAvailable(attr.attribute_id, val.value_id) && 'opacity-30 cursor-not-allowed',
          ]"
          @click="selectValue(attr.attribute_id, val.value_id)"
        >
          <img v-if="val.image_path" :src="val.image_path" :alt="val.value" class="w-full h-full object-cover" />
          <span v-else class="flex items-center justify-center text-xs text-gray-400 h-full">{{ val.value }}</span>
        </button>
      </div>
    </div>

    <!-- Stock status za selektovanu varijantu -->
    <div v-if="selectedVariant" class="text-sm">
      <span v-if="selectedVariant.stock_quantity > 0" class="text-green-600 font-medium">
        Na stanju ({{ selectedVariant.stock_quantity }} kom)
      </span>
      <span v-else class="text-red-500 font-medium">Nema na stanju</span>
      <span v-if="selectedVariant.sku" class="text-gray-400 ml-3">SKU: {{ selectedVariant.sku }}</span>
    </div>
  </div>
</template>
