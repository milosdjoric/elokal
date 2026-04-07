<script setup lang="ts">
const { get, post, put, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface AttributeValue { id: number; value: string; color_hex: string | null; image_path: string | null; sort_order: number }
interface Attribute { id: number; name: string; slug: string; type: string; is_filterable: boolean; is_visible_on_card: boolean; sort_order: number; values: AttributeValue[] }

const attributes = ref<Attribute[]>([])
const loading = ref(true)

// Attribute form
const showForm = ref(false)
const editId = ref<number | null>(null)
const form = reactive({ name: '', slug: '', type: 'select', is_filterable: true, is_visible_on_card: false, sort_order: 0 })

// Value form
const showValueForm = ref(false)
const valueAttrId = ref<number | null>(null)
const valueForm = reactive({ value: '', color_hex: '', image_path: '', sort_order: 0 })

async function fetchAttributes() {
  loading.value = true
  try {
    const data = await get<{ data: Attribute[] }>('/admin/attributes')
    attributes.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function openCreate() {
  editId.value = null
  Object.assign(form, { name: '', slug: '', type: 'select', is_filterable: true, is_visible_on_card: false, sort_order: 0 })
  showForm.value = true
}

function openEdit(attr: Attribute) {
  editId.value = attr.id
  Object.assign(form, attr)
  showForm.value = true
}

async function saveAttribute() {
  try {
    if (editId.value) {
      await put(`/admin/attributes/${editId.value}`, form)
      success('Atribut ažuriran.')
    } else {
      await post('/admin/attributes', form)
      success('Atribut kreiran.')
    }
    showForm.value = false
    fetchAttributes()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

async function deleteAttribute(id: number) {
  if (!confirm('Obrisati atribut?')) return
  try {
    await del(`/admin/attributes/${id}`)
    success('Atribut obrisan.')
    fetchAttributes()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

function openAddValue(attrId: number) {
  valueAttrId.value = attrId
  Object.assign(valueForm, { value: '', color_hex: '', image_path: '', sort_order: 0 })
  showValueForm.value = true
}

async function saveValue() {
  try {
    await post(`/admin/attributes/${valueAttrId.value}/values`, valueForm)
    success('Vrednost dodata.')
    showValueForm.value = false
    fetchAttributes()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

async function deleteValue(valueId: number) {
  try {
    await del(`/admin/attribute-values/${valueId}`)
    success('Vrednost obrisana.')
    fetchAttributes()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

const typeLabels: Record<string, string> = { select: 'Dropdown', color: 'Boja', image: 'Slika' }

onMounted(fetchAttributes)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Atributi' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Atributi</h1>
      <UiAtomsButton @click="openCreate">+ Novi atribut</UiAtomsButton>
    </div>

    <div v-if="loading" class="space-y-3">
      <UiAtomsSkeleton v-for="i in 3" :key="i" height="80px" />
    </div>

    <div v-else class="space-y-4">
      <div v-for="attr in attributes" :key="attr.id" class="bg-white border border-gray-200">
        <div class="flex items-center justify-between px-5 py-4">
          <div>
            <h3 class="font-semibold text-gray-800">{{ attr.name }}</h3>
            <div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
              <span>{{ typeLabels[attr.type] || attr.type }}</span>
              <span v-if="attr.is_filterable">Filtrabilno</span>
              <span v-if="attr.is_visible_on_card">Vidljivo na kartici</span>
            </div>
          </div>
          <div class="flex gap-2">
            <UiAtomsButton size="sm" variant="ghost" @click="openAddValue(attr.id)">+ Vrednost</UiAtomsButton>
            <UiAtomsButton size="sm" variant="ghost" @click="openEdit(attr)">Izmeni</UiAtomsButton>
            <UiAtomsButton size="sm" variant="ghost" @click="deleteAttribute(attr.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
          </div>
        </div>
        <div v-if="attr.values.length" class="border-t border-gray-100 px-5 py-3">
          <div class="flex flex-wrap gap-2">
            <div v-for="val in attr.values" :key="val.id" class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 px-2.5 py-1 text-sm">
              <span v-if="val.color_hex" class="w-4 h-4 rounded-full border border-gray-300" :style="{ backgroundColor: val.color_hex }" />
              <span>{{ val.value }}</span>
              <button class="text-gray-400 hover:text-red-500 ml-1" @click="deleteValue(val.id)">×</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Attribute form modal -->
    <UiMoleculesModal v-model="showForm" :title="editId ? 'Izmeni atribut' : 'Novi atribut'">
      <form @submit.prevent="saveAttribute" class="space-y-4">
        <UiAtomsInput v-model="form.name" label="Naziv" required />
        <UiAtomsInput v-model="form.slug" label="Slug" />
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tip</label>
          <select v-model="form.type" class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="select">Dropdown</option>
            <option value="color">Boja</option>
            <option value="image">Slika</option>
          </select>
        </div>
        <div class="flex gap-4">
          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input v-model="form.is_filterable" type="checkbox" class="w-4 h-4 text-primary-600" /> Filtrabilno
          </label>
          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input v-model="form.is_visible_on_card" type="checkbox" class="w-4 h-4 text-primary-600" /> Vidljivo na kartici
          </label>
        </div>
      </form>
      <template #footer>
        <UiAtomsButton variant="secondary" @click="showForm = false">Otkaži</UiAtomsButton>
        <UiAtomsButton @click="saveAttribute">Sačuvaj</UiAtomsButton>
      </template>
    </UiMoleculesModal>

    <!-- Value form modal -->
    <UiMoleculesModal v-model="showValueForm" title="Dodaj vrednost">
      <form @submit.prevent="saveValue" class="space-y-4">
        <UiAtomsInput v-model="valueForm.value" label="Vrednost" required placeholder="npr. Crvena, XL" />
        <UiAtomsInput v-model="valueForm.color_hex" label="Hex boja" placeholder="#FF0000" />
      </form>
      <template #footer>
        <UiAtomsButton variant="secondary" @click="showValueForm = false">Otkaži</UiAtomsButton>
        <UiAtomsButton @click="saveValue">Dodaj</UiAtomsButton>
      </template>
    </UiMoleculesModal>
  </div>
</template>
