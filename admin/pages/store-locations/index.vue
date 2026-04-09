<script setup lang="ts">
const { get, post, put, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface StoreLocation { id: number; name: string; address: string; city: string; phone: string | null; latitude: string; longitude: string; is_active: boolean }

const locations = ref<StoreLocation[]>([])
const loading = ref(true)
const showModal = ref(false)
const editId = ref<number | null>(null)
const saving = ref(false)
const form = reactive({ name: '', address: '', city: '', phone: '', latitude: '', longitude: '', is_active: true })

async function fetchLocations() {
  loading.value = true
  try { const data = await get<{ data: StoreLocation[] }>('/admin/store-locations'); locations.value = data.data }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function openCreate() {
  editId.value = null
  Object.assign(form, { name: '', address: '', city: '', phone: '', latitude: '', longitude: '', is_active: true })
  showModal.value = true
}

function openEdit(loc: StoreLocation) {
  editId.value = loc.id
  Object.assign(form, { name: loc.name, address: loc.address, city: loc.city, phone: loc.phone || '', latitude: loc.latitude, longitude: loc.longitude, is_active: loc.is_active })
  showModal.value = true
}

async function handleSubmit() {
  saving.value = true
  try {
    if (editId.value) { await put(`/admin/store-locations/${editId.value}`, form); success('Lokacija ažurirana.') }
    else { await post('/admin/store-locations', form); success('Lokacija kreirana.') }
    showModal.value = false; fetchLocations()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { saving.value = false }
}

async function deleteLocation(id: number) {
  if (!confirm('Obrisati lokaciju?')) return
  try { await del(`/admin/store-locations/${id}`); success('Obrisano.'); fetchLocations() }
  catch (e) { toastError(getErrorMessage(e)) }
}

onMounted(fetchLocations)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Prodavnice' }]" />
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Prodavnice</h1>
      <UiAtomsButton @click="openCreate">+ Nova lokacija</UiAtomsButton>
    </div>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 3" :key="i" height="50px" /></div>
    <div v-else-if="!locations.length" class="text-center py-16 text-gray-500">Nema lokacija.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="loc in locations" :key="loc.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="font-medium text-gray-800">{{ loc.name }}</p>
          <span class="text-xs text-gray-500">{{ loc.address }}, {{ loc.city }}</span>
        </div>
        <div class="flex gap-2">
          <UiAtomsButton variant="ghost" size="sm" @click="openEdit(loc)">Izmeni</UiAtomsButton>
          <UiAtomsButton variant="ghost" size="sm" @click="deleteLocation(loc.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
        </div>
      </div>
    </div>

    <UiMoleculesModal v-model="showModal" :title="editId ? 'Izmeni lokaciju' : 'Nova prodavnica'">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <UiAtomsInput v-model="form.name" label="Naziv" required />
        <UiAtomsInput v-model="form.address" label="Adresa" required />
        <UiAtomsInput v-model="form.city" label="Grad" required />
        <UiAtomsInput v-model="form.phone" label="Telefon" />
        <div class="grid grid-cols-2 gap-4">
          <UiAtomsInput v-model="form.latitude" label="Latitude" required />
          <UiAtomsInput v-model="form.longitude" label="Longitude" required />
        </div>
        <UiAtomsSwitch v-model="form.is_active" label="Aktivna" />
      </form>
      <template #footer>
        <UiAtomsButton variant="secondary" @click="showModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="saving" @click="handleSubmit">{{ editId ? 'Sačuvaj' : 'Kreiraj' }}</UiAtomsButton>
      </template>
    </UiMoleculesModal>
  </div>
</template>
