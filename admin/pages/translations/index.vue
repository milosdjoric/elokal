<script setup lang="ts">
const { get, put, upload, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

const modelType = ref('Product')
const locale = ref('en')
const items = ref<{ id: number; name: string; completeness: number }[]>([])
const loading = ref(true)
const languages = ref<{ locales: string[]; default: string; stats: Record<string, { percentage: number }> }>({ locales: ['sr'], default: 'sr', stats: {} })

// Editing
const editingId = ref<number | null>(null)
const editFields = ref<string[]>([])
const editValues = ref<Record<string, string>>({})
const saving = ref(false)
const showEditor = computed({
  get: () => editingId.value !== null,
  set: (v: boolean) => { if (!v) editingId.value = null },
})

const modelTypes = [
  { value: 'Product', label: 'Proizvodi' },
  { value: 'Category', label: 'Kategorije' },
  { value: 'Post', label: 'Blog postovi' },
  { value: 'Page', label: 'Stranice' },
]

async function fetchLanguages() {
  try {
    const data = await get<typeof languages.value>('/admin/translations/languages')
    languages.value = data
    const nonDefault = data.locales.filter((l: string) => l !== data.default)
    if (nonDefault.length > 0) locale.value = nonDefault[0]
  }
  catch { /* silent */ }
}

async function fetchItems() {
  loading.value = true
  try {
    const endpoint = `/admin/${modelType.value.toLowerCase()}s`
    const data = await get<{ data: { id: number; name?: string; title?: string }[] }>(endpoint === '/admin/categorys' ? '/admin/categories' : endpoint)
    const list = data.data || []

    // Dohvati completeness za svaku stavku
    const enriched = await Promise.all(list.map(async (item) => {
      try {
        const t = await get<{ data: { completeness: number } }>('/admin/translations', {
          type: modelType.value,
          id: item.id,
          locale: locale.value,
        })
        return { id: item.id, name: item.name || item.title || `#${item.id}`, completeness: t.data.completeness }
      }
      catch {
        return { id: item.id, name: item.name || item.title || `#${item.id}`, completeness: 0 }
      }
    }))

    items.value = enriched
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function openEditor(id: number) {
  try {
    const data = await get<{ data: { translations: Record<string, string>; fields: string[] } }>('/admin/translations', {
      type: modelType.value,
      id,
      locale: locale.value,
    })
    editingId.value = id
    editFields.value = data.data.fields
    editValues.value = {}
    for (const f of data.data.fields) {
      editValues.value[f] = data.data.translations[f] || ''
    }
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

async function saveTranslations() {
  if (!editingId.value) return
  saving.value = true
  try {
    await put('/admin/translations', {
      type: modelType.value,
      id: editingId.value,
      locale: locale.value,
      translations: editValues.value,
    })
    success('Prevodi sačuvani.')
    editingId.value = null
    fetchItems()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { saving.value = false }
}

function exportCsv() {
  window.open(`http://localhost:8000/api/admin/translations/export?type=${modelType.value}&locale=${locale.value}`, '_blank')
}

const fieldLabels: Record<string, string> = {
  name: 'Naziv', slug: 'Slug', title: 'Naslov',
  short_description: 'Kratki opis', description: 'Opis',
  excerpt: 'Izvod', content: 'Sadržaj',
  meta_title: 'Meta naslov', meta_description: 'Meta opis',
}

watch([modelType, locale], fetchItems)
onMounted(() => { fetchLanguages(); fetchItems() })
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Prevodi' }]" />
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Prevodi</h1>

    <!-- Filteri -->
    <div class="flex gap-4 mb-6">
      <select v-model="modelType" class="px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
        <option v-for="mt in modelTypes" :key="mt.value" :value="mt.value">{{ mt.label }}</option>
      </select>
      <select v-model="locale" class="px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
        <option v-for="loc in languages.locales.filter(l => l !== languages.default)" :key="loc" :value="loc">{{ loc.toUpperCase() }}</option>
      </select>
      <UiAtomsButton variant="secondary" size="sm" @click="exportCsv">Export CSV</UiAtomsButton>
    </div>

    <!-- Globalna statistika -->
    <div v-if="languages.stats[locale]" class="mb-4 text-sm text-gray-500">
      Kompletnost za {{ locale.toUpperCase() }}: <span class="font-bold" :class="languages.stats[locale].percentage >= 80 ? 'text-green-600' : 'text-orange-500'">{{ languages.stats[locale].percentage }}%</span>
    </div>

    <!-- Lista -->
    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 5" :key="i" height="45px" /></div>
    <div v-else-if="!items.length" class="text-center py-16 text-gray-500">Nema stavki.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="item in items" :key="item.id" class="flex items-center justify-between px-5 py-3">
        <div class="flex items-center gap-3">
          <span class="text-sm font-medium text-gray-800">{{ item.name }}</span>
          <div class="w-20 bg-gray-200 h-1.5 rounded-full">
            <div class="h-1.5 rounded-full transition-all" :class="item.completeness >= 80 ? 'bg-green-500' : item.completeness > 0 ? 'bg-orange-400' : 'bg-gray-300'" :style="{ width: `${item.completeness}%` }" />
          </div>
          <span class="text-xs text-gray-400">{{ item.completeness }}%</span>
        </div>
        <UiAtomsButton variant="ghost" size="sm" @click="openEditor(item.id)">Prevedi</UiAtomsButton>
      </div>
    </div>

    <!-- Editor modal -->
    <UiMoleculesModal v-model="showEditor" :title="`Prevod na ${locale.toUpperCase()}`">
      <form v-if="editingId" @submit.prevent="saveTranslations" class="space-y-4">
        <div v-for="field in editFields" :key="field">
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ fieldLabels[field] || field }}</label>
          <textarea
            v-if="field === 'description' || field === 'content'"
            v-model="editValues[field]"
            rows="4"
            class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
          <input
            v-else
            v-model="editValues[field]"
            class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
        </div>
      </form>
      <template #footer>
        <UiAtomsButton variant="secondary" @click="editingId = null">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="saving" @click="saveTranslations">Sačuvaj</UiAtomsButton>
      </template>
    </UiMoleculesModal>
  </div>
</template>
