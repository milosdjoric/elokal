<script setup lang="ts">
import type { Product, Category } from '~/types'

interface SizeGuide {
  headers: string[]
  rows: string[][]
}

interface Props {
  product?: Product | null
  loading?: boolean
  serverErrors?: Record<string, string[]>
}

const props = withDefaults(defineProps<Props>(), {
  product: null,
  loading: false,
  serverErrors: () => ({}),
})

const emit = defineEmits<{
  submit: [data: Record<string, unknown>]
}>()

const { get } = useApi()
const { generateSlug } = useSlug()

const slugManual = ref(false)
const categories = ref<Category[]>([])

const form = reactive({
  name: '',
  slug: '',
  short_description: '',
  description: '',
  price: '',
  sale_price: '',
  sale_price_from: '',
  sale_price_to: '',
  cost_price: '',
  unit_price: '',
  unit_label: '',
  sku: '',
  stock_quantity: 0,
  is_active: true,
  featured: false,
  sort_order: 0,
  meta_title: '',
  meta_description: '',
  categories: [] as number[],
  size_guide: null as SizeGuide | null,
  custom_tabs: [] as Array<{ title: string; content: string }>,
})

// Prefill za edit
if (props.product) {
  Object.assign(form, {
    name: props.product.name,
    slug: props.product.slug,
    short_description: props.product.short_description || '',
    description: props.product.description || '',
    price: props.product.price,
    sale_price: props.product.sale_price || '',
    sale_price_from: props.product.sale_price_from?.slice(0, 16) || '',
    sale_price_to: props.product.sale_price_to?.slice(0, 16) || '',
    cost_price: props.product.cost_price || '',
    unit_price: props.product.unit_price || '',
    unit_label: props.product.unit_label || '',
    sku: props.product.sku || '',
    stock_quantity: props.product.stock_quantity,
    is_active: props.product.is_active,
    featured: props.product.featured,
    sort_order: props.product.sort_order,
    meta_title: props.product.meta_title || '',
    meta_description: props.product.meta_description || '',
    categories: props.product.categories || [],
    size_guide: props.product.size_guide || null,
    custom_tabs: props.product.custom_tabs || [],
  })
  slugManual.value = true
}

// Auto slug
watch(() => form.name, (val) => {
  if (!slugManual.value) {
    form.slug = generateSlug(val)
  }
})

// Fetch categories
async function fetchCategories() {
  try {
    const data = await get<{ data: Category[] }>('/admin/categories')
    categories.value = data.data
  }
  catch { /* silent */ }
}

onMounted(fetchCategories)

function toggleCategory(id: number) {
  const idx = form.categories.indexOf(id)
  if (idx >= 0) form.categories.splice(idx, 1)
  else form.categories.push(id)
}

function fieldError(field: string): string {
  return props.serverErrors[field]?.[0] || ''
}

function handleSubmit() {
  const data: Record<string, unknown> = { ...form }
  if (!data.sale_price) {
    data.sale_price = null
    data.sale_price_from = null
    data.sale_price_to = null
  }
  if (!data.cost_price) data.cost_price = null
  if (!data.unit_price) data.unit_price = null
  if (!data.unit_label) data.unit_label = null
  if (!data.sku) data.sku = null
  if (!data.meta_title) data.meta_title = null
  if (!data.meta_description) data.meta_description = null
  if ((data.custom_tabs as Array<{ title: string; content: string }>).length === 0) data.custom_tabs = null

  emit('submit', data)
}

// Size guide helpers
function initSizeGuide() {
  form.size_guide = { headers: ['Veličina', 'Grudi (cm)', 'Struk (cm)'], rows: [['S', '', ''], ['M', '', ''], ['L', '', '']] }
}
function removeSizeGuide() {
  form.size_guide = null
}
function addSizeGuideColumn() {
  if (!form.size_guide) return
  form.size_guide.headers.push('')
  form.size_guide.rows.forEach(row => row.push(''))
}
function removeSizeGuideColumn(idx: number) {
  if (!form.size_guide || form.size_guide.headers.length <= 1) return
  form.size_guide.headers.splice(idx, 1)
  form.size_guide.rows.forEach(row => row.splice(idx, 1))
}
function addSizeGuideRow() {
  if (!form.size_guide) return
  form.size_guide.rows.push(new Array(form.size_guide.headers.length).fill(''))
}
function removeSizeGuideRow(idx: number) {
  if (!form.size_guide) return
  form.size_guide.rows.splice(idx, 1)
}

const activeTab = ref('general')
const tabs = [
  { key: 'general', label: 'Osnovno' },
  { key: 'pricing', label: 'Cene' },
  { key: 'categories', label: 'Kategorije' },
  { key: 'size_guide', label: 'Vodič za veličine' },
  { key: 'custom_tabs', label: 'Custom tabovi' },
  { key: 'seo', label: 'SEO' },
]
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <!-- Tab buttons -->
    <div class="border-b border-gray-200 mb-4">
      <nav class="flex gap-6 -mb-px">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          class="py-3 text-sm font-medium border-b-2 transition-colors"
          :class="activeTab === tab.key
            ? 'border-primary-600 text-primary-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- Tab content -->
    <div>
        <!-- General -->
        <div v-show="activeTab === 'general'" class="space-y-4">
          <UiAtomsInput
            v-model="form.name"
            label="Naziv proizvoda"
            required
            :error="fieldError('name')"
          />

          <div>
            <UiAtomsInput
              v-model="form.slug"
              label="Slug"
              required
              :error="fieldError('slug')"
              @focus="slugManual = true"
            />
            <p class="mt-1 text-xs text-gray-400">
              Auto-generiše se iz naziva. Klikni da edituješ ručno.
            </p>
          </div>

          <UiAtomsInput
            v-model="form.short_description"
            label="Kratak opis"
            :error="fieldError('short_description')"
          />

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Opis</label>
            <textarea
              v-model="form.description"
              rows="5"
              class="w-full px-3 py-2 border border-gray-300 shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :class="{ 'border-red-500': fieldError('description') }"
            />
            <p v-if="fieldError('description')" class="mt-1 text-sm text-red-600">{{ fieldError('description') }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <UiAtomsInput
              v-model="form.sku"
              label="SKU"
              :error="fieldError('sku')"
            />
            <UiAtomsInput
              v-model.number="form.stock_quantity"
              label="Količina na stanju"
              type="number"
              :error="fieldError('stock_quantity')"
            />
          </div>

          <div class="flex items-center gap-6">
            <UiAtomsSwitch v-model="form.is_active" label="Aktivan" />
            <UiAtomsSwitch v-model="form.featured" label="Istaknut" />
          </div>

          <UiAtomsInput
            v-model.number="form.sort_order"
            label="Redosled sortiranja"
            type="number"
            :error="fieldError('sort_order')"
          />
        </div>

        <!-- Pricing -->
        <div v-show="activeTab === 'pricing'" class="space-y-4">
          <UiAtomsInput
            v-model="form.price"
            label="Cena (RSD)"
            type="number"
            required
            :error="fieldError('price')"
          />

          <div class="p-4 bg-gray-50 border border-gray-200 space-y-4">
            <h3 class="text-sm font-semibold text-gray-700">Akcijska cena</h3>

            <UiAtomsInput
              v-model="form.sale_price"
              label="Akcijska cena (RSD)"
              type="number"
              :error="fieldError('sale_price')"
            />

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Od</label>
                <input
                  v-model="form.sale_price_from"
                  type="datetime-local"
                  class="w-full px-3 py-2 border border-gray-300 shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Do</label>
                <input
                  v-model="form.sale_price_to"
                  type="datetime-local"
                  class="w-full px-3 py-2 border border-gray-300 shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
            </div>
          </div>

          <UiAtomsInput
            v-model="form.cost_price"
            label="Nabavna cena (RSD)"
            type="number"
            :error="fieldError('cost_price')"
          />

          <div class="grid grid-cols-2 gap-4">
            <UiAtomsInput
              v-model="form.unit_price"
              label="Jedinična cena"
              type="number"
              :error="fieldError('unit_price')"
            />
            <UiAtomsInput
              v-model="form.unit_label"
              label="Jedinica (npr. kg, l, kom)"
              :error="fieldError('unit_label')"
            />
          </div>
        </div>

        <!-- Categories -->
        <div v-show="activeTab === 'categories'">
          <p class="text-sm text-gray-500 mb-4">Izaberite kategorije kojima pripada proizvod.</p>

          <div v-if="categories.length === 0" class="text-sm text-gray-400">
            Nema kategorija.
          </div>

          <div v-else class="space-y-3">
            <div v-for="parent in categories" :key="parent.id">
              <p class="text-sm font-semibold text-gray-700 mb-1">{{ parent.name }}</p>
              <div v-if="parent.children?.length" class="ml-4 space-y-1">
                <UiAtomsCheckbox
                  v-for="child in parent.children"
                  :key="child.id"
                  :model-value="form.categories.includes(child.id)"
                  :label="child.name"
                  @update:model-value="toggleCategory(child.id)"
                />
              </div>
              <div v-else class="ml-4">
                <UiAtomsCheckbox
                  :model-value="form.categories.includes(parent.id)"
                  :label="parent.name"
                  @update:model-value="toggleCategory(parent.id)"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Size guide -->
        <div v-show="activeTab === 'size_guide'">
          <div v-if="!form.size_guide" class="text-center py-8">
            <p class="text-sm text-gray-400 mb-3">Nema vodiča za veličine.</p>
            <UiAtomsButton size="sm" variant="secondary" @click="initSizeGuide">Dodaj vodič</UiAtomsButton>
          </div>

          <div v-else class="space-y-4">
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-500">Definišite tabelu sa dimenzijama.</p>
              <div class="flex gap-2">
                <UiAtomsButton size="sm" variant="secondary" @click="addSizeGuideColumn">+ Kolona</UiAtomsButton>
                <UiAtomsButton size="sm" variant="secondary" @click="addSizeGuideRow">+ Red</UiAtomsButton>
                <UiAtomsButton size="sm" variant="danger" @click="removeSizeGuide">Ukloni vodič</UiAtomsButton>
              </div>
            </div>

            <div class="overflow-x-auto border border-gray-200">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th v-for="(header, hi) in form.size_guide.headers" :key="hi" class="px-2 py-1.5">
                      <div class="flex items-center gap-1">
                        <input
                          v-model="form.size_guide.headers[hi]"
                          type="text"
                          class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-primary-500 font-semibold"
                          placeholder="Zaglavlje"
                        />
                        <button
                          v-if="form.size_guide.headers.length > 1"
                          type="button"
                          class="text-red-400 hover:text-red-600 text-xs flex-shrink-0"
                          @click="removeSizeGuideColumn(hi)"
                        >
                          ✕
                        </button>
                      </div>
                    </th>
                    <th class="w-8" />
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="(row, ri) in form.size_guide.rows" :key="ri">
                    <td v-for="(cell, ci) in row" :key="ci" class="px-2 py-1">
                      <input
                        v-model="form.size_guide.rows[ri][ci]"
                        type="text"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-primary-500"
                      />
                    </td>
                    <td class="px-2 py-1 text-center">
                      <button
                        type="button"
                        class="text-red-400 hover:text-red-600 text-xs"
                        @click="removeSizeGuideRow(ri)"
                      >
                        ✕
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Custom tabs -->
        <div v-show="activeTab === 'custom_tabs'" class="space-y-4">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Dodajte prilagođene tabove koji se prikazuju na stranici proizvoda.</p>
            <UiAtomsButton size="sm" variant="secondary" @click="form.custom_tabs.push({ title: '', content: '' })">+ Novi tab</UiAtomsButton>
          </div>

          <div v-if="form.custom_tabs.length === 0" class="text-center py-8 text-sm text-gray-400">
            Nema custom tabova.
          </div>

          <div v-for="(tab, idx) in form.custom_tabs" :key="idx" class="p-4 border border-gray-200 space-y-3">
            <div class="flex items-center justify-between">
              <UiAtomsInput v-model="tab.title" label="Naslov taba" class="flex-1 mr-3" />
              <button
                type="button"
                class="text-red-400 hover:text-red-600 text-sm mt-5"
                @click="form.custom_tabs.splice(idx, 1)"
              >
                Ukloni
              </button>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Sadržaj</label>
              <textarea
                v-model="tab.content"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              />
            </div>
          </div>
        </div>

        <!-- SEO -->
        <div v-show="activeTab === 'seo'" class="space-y-4">
          <UiAtomsInput
            v-model="form.meta_title"
            label="Meta Title"
            :error="fieldError('meta_title')"
          />
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
            <textarea
              v-model="form.meta_description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
      <NuxtLink to="/products">
        <UiAtomsButton variant="secondary">Otkaži</UiAtomsButton>
      </NuxtLink>
      <UiAtomsButton type="submit" :loading="loading">
        {{ product ? 'Sačuvaj izmene' : 'Kreiraj proizvod' }}
      </UiAtomsButton>
    </div>
  </form>
</template>
