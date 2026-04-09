<script setup lang="ts">
const { get, post, put, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface PaymentMethod { id: number; code: string; name: string; description: string | null; instructions: string | null; additional_cost: string; is_active: boolean; is_online: boolean; sort_order: number }

const methods = ref<PaymentMethod[]>([])
const loading = ref(true)
const showModal = ref(false)
const editId = ref<number | null>(null)
const saving = ref(false)
const form = reactive({ code: '', name: '', description: '', instructions: '', additional_cost: '0', is_active: true, is_online: false, sort_order: 0 })

async function fetchMethods() {
  loading.value = true
  try {
    const data = await get<{ data: PaymentMethod[] }>('/admin/payment-methods')
    methods.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function openCreate() {
  editId.value = null
  Object.assign(form, { code: '', name: '', description: '', instructions: '', additional_cost: '0', is_active: true, is_online: false, sort_order: 0 })
  showModal.value = true
}

function openEdit(m: PaymentMethod) {
  editId.value = m.id
  Object.assign(form, { code: m.code, name: m.name, description: m.description || '', instructions: m.instructions || '', additional_cost: m.additional_cost, is_active: m.is_active, is_online: m.is_online, sort_order: m.sort_order })
  showModal.value = true
}

async function handleSubmit() {
  saving.value = true
  try {
    if (editId.value) {
      await put(`/admin/payment-methods/${editId.value}`, form)
      success('Metoda ažurirana.')
    } else {
      await post('/admin/payment-methods', form)
      success('Metoda kreirana.')
    }
    showModal.value = false
    fetchMethods()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { saving.value = false }
}

async function deleteMethod(id: number) {
  if (!confirm('Obrisati metodu plaćanja?')) return
  try { await del(`/admin/payment-methods/${id}`); success('Obrisano.'); fetchMethods() }
  catch (e) { toastError(getErrorMessage(e)) }
}

onMounted(fetchMethods)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Payment metode' }]" />
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Metode plaćanja</h1>
      <UiAtomsButton @click="openCreate">+ Nova metoda</UiAtomsButton>
    </div>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 3" :key="i" height="50px" /></div>
    <div v-else-if="!methods.length" class="text-center py-16 text-gray-500">Nema metoda plaćanja.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="m in methods" :key="m.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="font-medium text-gray-800">{{ m.name }} <span class="text-xs text-gray-400">({{ m.code }})</span></p>
          <div class="flex items-center gap-2 mt-0.5">
            <UiAtomsBadge :variant="m.is_active ? 'success' : 'neutral'" class="text-[10px]">{{ m.is_active ? 'Aktivna' : 'Neaktivna' }}</UiAtomsBadge>
            <span v-if="parseFloat(m.additional_cost) > 0" class="text-xs text-gray-500">+{{ m.additional_cost }} RSD</span>
            <span class="text-xs text-gray-400">{{ m.is_online ? 'Online' : 'Offline' }}</span>
          </div>
        </div>
        <div class="flex gap-2">
          <UiAtomsButton variant="ghost" size="sm" @click="openEdit(m)">Izmeni</UiAtomsButton>
          <UiAtomsButton variant="ghost" size="sm" @click="deleteMethod(m.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
        </div>
      </div>
    </div>

    <UiMoleculesModal v-model="showModal" :title="editId ? 'Izmeni metodu' : 'Nova metoda plaćanja'">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <UiAtomsInput v-model="form.code" label="Kod" placeholder="cod, bank_transfer..." required />
        <UiAtomsInput v-model="form.name" label="Naziv" required />
        <UiAtomsInput v-model="form.description" label="Opis" />
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Instrukcije (za offline metode)</label>
          <textarea v-model="form.instructions" rows="3" class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500" />
        </div>
        <UiAtomsInput v-model="form.additional_cost" label="Dodatni trošak (RSD)" type="number" />
        <div class="flex gap-4">
          <UiAtomsSwitch v-model="form.is_active" label="Aktivna" />
          <UiAtomsSwitch v-model="form.is_online" label="Online metoda" />
        </div>
      </form>
      <template #footer>
        <UiAtomsButton variant="secondary" @click="showModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="saving" @click="handleSubmit">{{ editId ? 'Sačuvaj' : 'Kreiraj' }}</UiAtomsButton>
      </template>
    </UiMoleculesModal>
  </div>
</template>
