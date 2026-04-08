<script setup lang="ts">
interface AttributeValue {
  id: number
  attribute_id: number
  value: string
  color_hex: string | null
  image_path: string | null
  sort_order: number
}

interface Attribute {
  id: number
  name: string
  slug: string
  type: 'select' | 'color' | 'image'
  values: AttributeValue[]
}

interface VariantAttribute {
  attribute_id: number
  attribute_name: string
  attribute_slug: string
  value_id: number
  value: string
  color_hex: string | null
}

interface Variant {
  id: number
  sku: string | null
  price: string | null
  sale_price: string | null
  stock_quantity: number
  is_active: boolean
  attributes: VariantAttribute[]
}

const props = defineProps<{ productId: number }>()

const { get, post, patch, put, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

const attributes = ref<Attribute[]>([])
const variants = ref<Variant[]>([])
const loading = ref(true)
const generating = ref(false)
const bulkSaving = ref(false)

// Atributi selektovani za generisanje varijanti
const selectedAttributeIds = ref<Set<number>>(new Set())
// Selektovane vrednosti po atributu
const selectedValues = ref<Map<number, Set<number>>>(new Map())

async function fetchData() {
  loading.value = true
  try {
    const [attrRes, varRes] = await Promise.all([
      get<{ data: Attribute[] }>('/admin/attributes'),
      get<{ data: Variant[] }>(`/admin/products/${props.productId}/variants`),
    ])
    attributes.value = attrRes.data
    variants.value = varRes.data

    // Preselektuj atribute koji su već u upotrebi
    const usedAttrIds = new Set<number>()
    const usedValueIds = new Map<number, Set<number>>()
    for (const v of variants.value) {
      for (const a of v.attributes) {
        usedAttrIds.add(a.attribute_id)
        if (!usedValueIds.has(a.attribute_id)) usedValueIds.set(a.attribute_id, new Set())
        usedValueIds.get(a.attribute_id)!.add(a.value_id)
      }
    }
    selectedAttributeIds.value = usedAttrIds
    selectedValues.value = usedValueIds
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

// Matrica kolona — lista atribut naziva koje koristimo
const matrixAttributes = computed(() => {
  return attributes.value.filter(a => selectedAttributeIds.value.has(a.id))
})

function toggleAttribute(id: number) {
  if (selectedAttributeIds.value.has(id)) {
    selectedAttributeIds.value.delete(id)
    selectedValues.value.delete(id)
  } else {
    selectedAttributeIds.value.add(id)
    // Selektuj sve vrednosti po default-u
    const attr = attributes.value.find(a => a.id === id)
    if (attr) {
      selectedValues.value.set(id, new Set(attr.values.map(v => v.id)))
    }
  }
}

function toggleValue(attrId: number, valueId: number) {
  const set = selectedValues.value.get(attrId)
  if (!set) return
  if (set.has(valueId)) set.delete(valueId)
  else set.add(valueId)
}

function getVariantLabel(variant: Variant): string {
  return variant.attributes.map(a => a.value).join(' / ')
}

function getAttributeValueName(attrId: number, valueId: number): string {
  const attr = attributes.value.find(a => a.id === attrId)
  return attr?.values.find(v => v.id === valueId)?.value || ''
}

// Generisanje novih varijanti iz selektovanih atributa/vrednosti
async function generateVariants() {
  const attrIds = Array.from(selectedAttributeIds.value)
  if (attrIds.length === 0) return

  // Skupi selektovane vrednosti po atributu
  const valueSets: AttributeValue[][] = []
  for (const attrId of attrIds) {
    const valueIds = selectedValues.value.get(attrId)
    if (!valueIds || valueIds.size === 0) continue
    const attr = attributes.value.find(a => a.id === attrId)
    if (!attr) continue
    valueSets.push(attr.values.filter(v => valueIds.has(v.id)))
  }

  if (valueSets.length === 0) return

  // Kartezijanski proizvod
  const combinations = cartesian(valueSets)

  // Filtriraj one koje već postoje
  const existingKeys = new Set(
    variants.value.map(v =>
      v.attributes.map(a => a.value_id).sort().join('-')
    )
  )

  const newCombinations = combinations.filter(combo => {
    const key = combo.map(v => v.id).sort().join('-')
    return !existingKeys.has(key)
  })

  if (newCombinations.length === 0) {
    toastError('Sve kombinacije već postoje.')
    return
  }

  generating.value = true
  try {
    for (const combo of newCombinations) {
      await post(`/admin/products/${props.productId}/variants`, {
        attribute_value_ids: combo.map(v => v.id),
        stock_quantity: 0,
        is_active: true,
      })
    }
    success(`${newCombinations.length} varijanti kreirano.`)
    await fetchData()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { generating.value = false }
}

function cartesian(arrays: AttributeValue[][]): AttributeValue[][] {
  if (arrays.length === 0) return [[]]
  const [first, ...rest] = arrays
  const restCombos = cartesian(rest)
  const result: AttributeValue[][] = []
  for (const item of first) {
    for (const combo of restCombos) {
      result.push([item, ...combo])
    }
  }
  return result
}

// Bulk save — šalje sve izmenjene varijante odjednom
async function bulkSave() {
  bulkSaving.value = true
  try {
    const payload = variants.value.map(v => ({
      id: v.id,
      price: v.price ? parseFloat(v.price) : null,
      stock_quantity: v.stock_quantity,
      sku: v.sku || null,
      is_active: v.is_active,
    }))
    const res = await put<{ data: Variant[] }>(
      `/admin/products/${props.productId}/variants/bulk`,
      { variants: payload }
    )
    variants.value = res.data
    success('Varijante sačuvane.')
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { bulkSaving.value = false }
}

async function deleteVariant(id: number) {
  if (!confirm('Obrisati varijantu?')) return
  try {
    await del(`/admin/variants/${id}`)
    variants.value = variants.value.filter(v => v.id !== id)
    success('Varijanta obrisana.')
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

// Bulk set — postavi vrednost za sve varijante
const bulkField = ref<'price' | 'stock_quantity'>('price')
const bulkValue = ref('')

function applyBulkValue() {
  if (!bulkValue.value) return
  for (const v of variants.value) {
    if (bulkField.value === 'price') {
      v.price = bulkValue.value
    } else {
      v.stock_quantity = parseInt(bulkValue.value) || 0
    }
  }
  bulkValue.value = ''
}

onMounted(fetchData)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-800">Varijante</h2>
      <div class="flex items-center gap-2">
        <UiAtomsButton
          v-if="variants.length > 0"
          size="sm"
          :loading="bulkSaving"
          @click="bulkSave"
        >
          Sačuvaj sve
        </UiAtomsButton>
      </div>
    </div>

    <div v-if="loading" class="space-y-3">
      <UiAtomsSkeleton v-for="i in 3" :key="i" height="40px" />
    </div>

    <template v-else>
      <!-- Selekcija atributa -->
      <div class="mb-6 p-4 bg-gray-50 border border-gray-200">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Atributi za varijante</h3>

        <div v-if="attributes.length === 0" class="text-sm text-gray-400">
          Nema definisanih atributa. Prvo kreirajte atribute.
        </div>

        <div v-else class="space-y-3">
          <div v-for="attr in attributes" :key="attr.id">
            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer mb-1">
              <input
                type="checkbox"
                :checked="selectedAttributeIds.has(attr.id)"
                class="w-4 h-4 text-primary-600 border-gray-300 rounded"
                @change="toggleAttribute(attr.id)"
              />
              {{ attr.name }}
              <span class="text-xs text-gray-400">({{ attr.type }})</span>
            </label>

            <!-- Vrednosti atributa -->
            <div v-if="selectedAttributeIds.has(attr.id)" class="ml-6 flex flex-wrap gap-2 mt-1">
              <label
                v-for="val in attr.values"
                :key="val.id"
                class="flex items-center gap-1.5 text-sm cursor-pointer px-2 py-1 border rounded"
                :class="selectedValues.get(attr.id)?.has(val.id) ? 'border-primary-400 bg-primary-50' : 'border-gray-200'"
              >
                <input
                  type="checkbox"
                  :checked="selectedValues.get(attr.id)?.has(val.id)"
                  class="w-3.5 h-3.5 text-primary-600 border-gray-300 rounded"
                  @change="toggleValue(attr.id, val.id)"
                />
                <span
                  v-if="attr.type === 'color' && val.color_hex"
                  class="w-4 h-4 rounded-full border border-gray-300 inline-block"
                  :style="{ backgroundColor: val.color_hex }"
                />
                {{ val.value }}
              </label>
            </div>
          </div>

          <UiAtomsButton
            size="sm"
            variant="secondary"
            :loading="generating"
            :disabled="selectedAttributeIds.size === 0"
            @click="generateVariants"
          >
            Generiši varijante
          </UiAtomsButton>
        </div>
      </div>

      <!-- Bulk edit toolbar -->
      <div v-if="variants.length > 0" class="flex items-center gap-3 mb-3 p-3 bg-gray-50 border border-gray-200">
        <span class="text-sm text-gray-600">Postavi za sve:</span>
        <select v-model="bulkField" class="px-2 py-1 text-sm border border-gray-300 rounded">
          <option value="price">Cena</option>
          <option value="stock_quantity">Količina</option>
        </select>
        <input
          v-model="bulkValue"
          type="number"
          class="w-32 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-primary-500"
          placeholder="Vrednost"
          @keyup.enter="applyBulkValue"
        />
        <UiAtomsButton size="sm" variant="secondary" @click="applyBulkValue">Primeni</UiAtomsButton>
      </div>

      <!-- Matrica tabela -->
      <div v-if="variants.length > 0" class="overflow-x-auto border border-gray-200">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th
                v-for="attr in matrixAttributes"
                :key="attr.id"
                class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase"
              >
                {{ attr.name }}
              </th>
              <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
              <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Cena</th>
              <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Količina</th>
              <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Aktivan</th>
              <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase w-16" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="variant in variants" :key="variant.id" class="hover:bg-gray-50">
              <!-- Atribut vrednosti -->
              <td
                v-for="attr in matrixAttributes"
                :key="attr.id"
                class="px-3 py-2 text-gray-700"
              >
                <span class="flex items-center gap-1.5">
                  <span
                    v-if="attr.type === 'color'"
                    class="w-4 h-4 rounded-full border border-gray-300 inline-block flex-shrink-0"
                    :style="{ backgroundColor: variant.attributes.find(a => a.attribute_id === attr.id)?.color_hex || '' }"
                  />
                  {{ variant.attributes.find(a => a.attribute_id === attr.id)?.value || '—' }}
                </span>
              </td>
              <!-- SKU -->
              <td class="px-3 py-2">
                <input
                  v-model="variant.sku"
                  type="text"
                  class="w-28 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-primary-500"
                  placeholder="—"
                />
              </td>
              <!-- Cena -->
              <td class="px-3 py-2">
                <input
                  v-model="variant.price"
                  type="number"
                  class="w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-primary-500"
                  placeholder="Bazna"
                />
              </td>
              <!-- Količina -->
              <td class="px-3 py-2">
                <input
                  v-model.number="variant.stock_quantity"
                  type="number"
                  min="0"
                  class="w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-primary-500"
                />
              </td>
              <!-- Aktivan -->
              <td class="px-3 py-2 text-center">
                <input
                  v-model="variant.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-primary-600 border-gray-300 rounded"
                />
              </td>
              <!-- Obriši -->
              <td class="px-3 py-2 text-center">
                <button
                  type="button"
                  class="text-red-400 hover:text-red-600 text-xs"
                  title="Obriši"
                  @click="deleteVariant(variant.id)"
                >
                  ✕
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-8 text-gray-400 text-sm">
        Nema varijanti. Selektujte atribute i kliknite "Generiši varijante".
      </div>
    </template>
  </div>
</template>
