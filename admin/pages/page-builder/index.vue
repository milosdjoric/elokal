<script setup lang="ts">
const { get, post, put, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface PageSection {
  id: number
  page_key: string
  type: string
  data: Record<string, unknown> | null
  is_active: boolean
  sort_order: number
}

const sections = ref<PageSection[]>([])
const types = ref<string[]>([])
const loading = ref(true)
const pageKey = ref('homepage')
const showEditor = computed({
  get: () => editingSection.value !== null,
  set: (v: boolean) => { if (!v) editingSection.value = null },
})
const showAddModal = ref(false)
const editingSection = ref<PageSection | null>(null)
const saving = ref(false)

const newType = ref('')
const editData = ref('')

const typeLabels: Record<string, string> = {
  hero_slideshow: 'Hero Slideshow',
  category_grid: 'Kategorije Grid',
  featured_products: 'Istaknuti proizvodi',
  new_arrivals: 'Novo u ponudi',
  on_sale: 'Na akciji',
  banner_full: 'Baner (puni)',
  banner_split: 'Baner (podeljeni)',
  product_carousel: 'Karusel proizvoda',
  text_block: 'Tekst blok',
  newsletter: 'Newsletter',
  trust_badges: 'Trust Badges',
  blog_preview: 'Blog preview',
  recently_viewed: 'Nedavno pregledano',
  spacer: 'Razmak',
}

const typeHelp: Record<string, string> = {
  hero_slideshow: `{
  "slides": [
    {
      "title": "Naslov slajda",
      "subtitle": "Podnaslov",
      "cta_text": "Tekst dugmeta",
      "cta_link": "/proizvodi",
      "bg_color": "#001219",
      "image": "https://... (opciono, inače koristi featured proizvod)"
    }
  ]
}`,
  category_grid: `{
  "title": "Kupujte po kategorijama",
  "columns": 6
}
// columns: 3 | 4 | 5 | 6 (broj kolona na desktopu)`,
  featured_products: `{
  "title": "Istaknuti proizvodi",
  "limit": 10,
  "items_per_row": 5,
  "mobile_items_per_row": 2
}
// limit: broj proizvoda
// items_per_row: 3 | 4 | 5 | 6 (desktop)
// mobile_items_per_row: 1 | 2 | 3 (mobilni)`,
  new_arrivals: `{
  "title": "Novo u ponudi",
  "limit": 8,
  "items_per_row": 5,
  "mobile_items_per_row": 2
}
// Isti parametri kao featured_products`,
  on_sale: `{
  "title": "Na akciji",
  "limit": 8,
  "items_per_row": 5,
  "mobile_items_per_row": 2
}`,
  banner_full: `{
  "title": "Naslov banera",
  "text": "Tekst banera",
  "cta_text": "Pogledaj",
  "cta_link": "/proizvodi",
  "bg_color": "#001219",
  "text_color": "#ffffff",
  "image": "https://... (opciono)"
}`,
  banner_split: `{
  "left_title": "Levi naslov",
  "left_text": "Levi tekst",
  "right_title": "Desni naslov",
  "right_text": "Desni tekst"
}`,
  product_carousel: `{
  "title": "Naslov karusela",
  "product_ids": [1, 5, 12, 33],
  "items_per_row": 5,
  "mobile_items_per_row": 2
}
// product_ids: niz ID-jeva proizvoda za ručni izbor`,
  text_block: `{
  "content": "<h2>Naslov</h2><p>HTML sadržaj tekst bloka.</p>"
}
// Podržava HTML tagove`,
  blog_preview: `{
  "title": "Iz našeg bloga",
  "limit": 3
}
// limit: broj blog postova`,
  recently_viewed: `{
  "title": "Nedavno pregledano"
}
// Čita iz localStorage, nema server podešavanja`,
  newsletter: `null
// Nema podešavanja — koristi globalni Newsletter komponent`,
  trust_badges: `null
// Nema podešavanja — koristi globalni TrustBadges komponent`,
  spacer: `{
  "height": 40
}
// height: visina razmaka u pikselima`,
}

async function fetchSections() {
  loading.value = true
  try {
    const res = await get<{ data: PageSection[]; types: string[] }>('/admin/page-sections', { page_key: pageKey.value })
    sections.value = res.data
    types.value = res.types
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function addSection() {
  if (!newType.value) return
  saving.value = true
  try {
    await post('/admin/page-sections', {
      page_key: pageKey.value,
      type: newType.value,
      data: null,
      is_active: true,
      sort_order: sections.value.length,
    })
    success('Sekcija dodata.')
    showAddModal.value = false
    newType.value = ''
    fetchSections()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { saving.value = false }
}

function openEditor(section: PageSection) {
  editingSection.value = section
  editData.value = section.data ? JSON.stringify(section.data, null, 2) : '{}'
}

async function saveSection() {
  if (!editingSection.value) return
  saving.value = true
  try {
    let parsed = null
    try { parsed = JSON.parse(editData.value) }
    catch { toastError('Nevalidan JSON.'); saving.value = false; return }

    await put(`/admin/page-sections/${editingSection.value.id}`, {
      data: parsed,
      is_active: editingSection.value.is_active,
    })
    success('Sekcija sačuvana.')
    editingSection.value = null
    fetchSections()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { saving.value = false }
}

async function toggleActive(section: PageSection) {
  try {
    await put(`/admin/page-sections/${section.id}`, { is_active: !section.is_active })
    section.is_active = !section.is_active
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

async function deleteSection(id: number) {
  if (!confirm('Obrisati sekciju?')) return
  try {
    await del(`/admin/page-sections/${id}`)
    success('Obrisano.')
    fetchSections()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

async function moveSection(id: number, direction: 'up' | 'down') {
  const idx = sections.value.findIndex(s => s.id === id)
  if (idx < 0) return
  const swapIdx = direction === 'up' ? idx - 1 : idx + 1
  if (swapIdx < 0 || swapIdx >= sections.value.length) return

  const order = sections.value.map(s => s.id)
  ;[order[idx], order[swapIdx]] = [order[swapIdx], order[idx]]

  try {
    await post('/admin/page-sections/reorder', { order })
    fetchSections()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

onMounted(fetchSections)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Page Builder' }]" />
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Page Builder — {{ pageKey }}</h1>
      <UiAtomsButton @click="showAddModal = true">+ Dodaj sekciju</UiAtomsButton>
    </div>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 4" :key="i" height="60px" /></div>
    <div v-else-if="!sections.length" class="text-center py-16 text-gray-500">Nema sekcija. Dodajte prvu.</div>

    <div v-else class="space-y-2">
      <div
        v-for="(section, i) in sections"
        :key="section.id"
        class="bg-white border border-gray-200 px-5 py-3 flex items-center justify-between"
        :class="!section.is_active ? 'opacity-50' : ''"
      >
        <div class="flex items-center gap-4">
          <!-- Reorder -->
          <div class="flex flex-col gap-0.5">
            <button :disabled="i === 0" class="text-gray-400 hover:text-gray-600 disabled:opacity-20" @click="moveSection(section.id, 'up')">▲</button>
            <button :disabled="i === sections.length - 1" class="text-gray-400 hover:text-gray-600 disabled:opacity-20" @click="moveSection(section.id, 'down')">▼</button>
          </div>

          <div>
            <p class="font-medium text-gray-800">{{ typeLabels[section.type] || section.type }}</p>
            <span class="text-xs text-gray-400">{{ section.type }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <UiAtomsButton variant="ghost" size="sm" @click="toggleActive(section)">
            {{ section.is_active ? 'Sakrij' : 'Prikaži' }}
          </UiAtomsButton>
          <UiAtomsButton variant="ghost" size="sm" @click="openEditor(section)">Podesi</UiAtomsButton>
          <UiAtomsButton variant="ghost" size="sm" @click="deleteSection(section.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
        </div>
      </div>
    </div>

    <!-- Add modal -->
    <UiMoleculesModal v-model="showAddModal" title="Dodaj sekciju">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tip sekcije</label>
          <select v-model="newType" class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="" disabled>Izaberite tip...</option>
            <option v-for="t in types" :key="t" :value="t">{{ typeLabels[t] || t }}</option>
          </select>
        </div>
      </div>
      <template #footer>
        <UiAtomsButton variant="secondary" @click="showAddModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="saving" @click="addSection">Dodaj</UiAtomsButton>
      </template>
    </UiMoleculesModal>

    <!-- Edit modal -->
    <UiMoleculesModal v-model="showEditor" :title="`Podesi: ${editingSection ? typeLabels[editingSection.type] || editingSection.type : ''}`">
      <div v-if="editingSection" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Podaci (JSON)</label>
          <textarea
            v-model="editData"
            rows="12"
            class="w-full px-3 py-2 text-sm font-mono border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
        </div>

        <!-- Help: šablon za tip sekcije -->
        <div v-if="editingSection && typeHelp[editingSection.type]" class="bg-gray-50 border border-gray-200 p-3">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Šablon za {{ typeLabels[editingSection.type] || editingSection.type }}</p>
          <pre class="text-xs text-gray-600 font-mono whitespace-pre-wrap">{{ typeHelp[editingSection.type] }}</pre>
        </div>

        <UiAtomsSwitch v-model="editingSection.is_active" label="Aktivna" />
      </div>
      <template #footer>
        <UiAtomsButton variant="secondary" @click="editingSection = null">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="saving" @click="saveSection">Sačuvaj</UiAtomsButton>
      </template>
    </UiMoleculesModal>
  </div>
</template>
