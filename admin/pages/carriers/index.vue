<script setup lang="ts">
const { get, post, put, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface Carrier { id: number; code: string; name: string; tracking_url_pattern: string | null; is_active: boolean; sort_order: number }

const carriers = ref<Carrier[]>([])
const loading = ref(true)
const showModal = ref(false)
const editId = ref<number | null>(null)
const saving = ref(false)
const form = reactive({ code: '', name: '', tracking_url_pattern: '', is_active: true, sort_order: 0 })

async function fetchCarriers() {
  loading.value = true
  try { const data = await get<{ data: Carrier[] }>('/admin/carriers'); carriers.value = data.data }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function openCreate() {
  editId.value = null
  Object.assign(form, { code: '', name: '', tracking_url_pattern: '', is_active: true, sort_order: 0 })
  showModal.value = true
}

function openEdit(c: Carrier) {
  editId.value = c.id
  Object.assign(form, { code: c.code, name: c.name, tracking_url_pattern: c.tracking_url_pattern || '', is_active: c.is_active, sort_order: c.sort_order })
  showModal.value = true
}

async function handleSubmit() {
  saving.value = true
  try {
    if (editId.value) { await put(`/admin/carriers/${editId.value}`, form); success('Kurir ažuriran.') }
    else { await post('/admin/carriers', form); success('Kurir kreiran.') }
    showModal.value = false; fetchCarriers()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { saving.value = false }
}

async function deleteCarrier(id: number) {
  if (!confirm('Obrisati kurira?')) return
  try { await del(`/admin/carriers/${id}`); success('Obrisan.'); fetchCarriers() }
  catch (e) { toastError(getErrorMessage(e)) }
}

onMounted(fetchCarriers)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Kuriri' }]" />
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Kuriri</h1>
      <UiAtomsButton @click="openCreate">+ Novi kurir</UiAtomsButton>
    </div>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 3" :key="i" height="50px" /></div>
    <div v-else-if="!carriers.length" class="text-center py-16 text-gray-500">Nema kurira.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="c in carriers" :key="c.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="font-medium text-gray-800">{{ c.name }} <span class="text-xs text-gray-400">({{ c.code }})</span></p>
          <span v-if="c.tracking_url_pattern" class="text-xs text-gray-500">{{ c.tracking_url_pattern }}</span>
        </div>
        <div class="flex gap-2">
          <UiAtomsButton variant="ghost" size="sm" @click="openEdit(c)">Izmeni</UiAtomsButton>
          <UiAtomsButton variant="ghost" size="sm" @click="deleteCarrier(c.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
        </div>
      </div>
    </div>

    <UiMoleculesModal v-model="showModal" :title="editId ? 'Izmeni kurira' : 'Novi kurir'">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <UiAtomsInput v-model="form.code" label="Kod" placeholder="dexpress, post_srbije..." required />
        <UiAtomsInput v-model="form.name" label="Naziv" required />
        <UiAtomsInput v-model="form.tracking_url_pattern" label="Tracking URL pattern" placeholder="https://tracking.example.com/{tracking_number}" />
        <p class="text-xs text-gray-400">Koristite {tracking_number} kao placeholder.</p>
        <UiAtomsSwitch v-model="form.is_active" label="Aktivan" />
      </form>
      <template #footer>
        <UiAtomsButton variant="secondary" @click="showModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="saving" @click="handleSubmit">{{ editId ? 'Sačuvaj' : 'Kreiraj' }}</UiAtomsButton>
      </template>
    </UiMoleculesModal>
  </div>
</template>
