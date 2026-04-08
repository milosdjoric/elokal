<script setup lang="ts">
interface TaxRate {
  id: number
  name: string
  rate: number
  country: string
  is_default: boolean
  is_active: boolean
}

const { get, post, put, del, getErrorMessage, getValidationErrors } = useApi()
const { success, error: toastError } = useToast()

const taxRates = ref<TaxRate[]>([])
const loading = ref(true)

const showModal = ref(false)
const editing = ref<TaxRate | null>(null)
const saving = ref(false)
const serverErrors = ref<Record<string, string[]>>({})

const form = reactive({
  name: '',
  rate: 20,
  country: 'RS',
  is_default: false,
  is_active: true,
})

const deleteModal = ref(false)
const deleteTarget = ref<TaxRate | null>(null)
const deleteLoading = ref(false)

async function fetchData() {
  loading.value = true
  try {
    const data = await get<{ data: TaxRate[] }>('/admin/tax-rates')
    taxRates.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function openCreate() {
  editing.value = null
  serverErrors.value = {}
  Object.assign(form, { name: '', rate: 20, country: 'RS', is_default: false, is_active: true })
  showModal.value = true
}

function openEdit(taxRate: TaxRate) {
  editing.value = taxRate
  serverErrors.value = {}
  Object.assign(form, {
    name: taxRate.name,
    rate: taxRate.rate,
    country: taxRate.country,
    is_default: taxRate.is_default,
    is_active: taxRate.is_active,
  })
  showModal.value = true
}

async function handleSubmit() {
  saving.value = true
  serverErrors.value = {}
  try {
    if (editing.value) {
      await put(`/admin/tax-rates/${editing.value.id}`, form)
      success('Poreska stopa ažurirana.')
    } else {
      await post('/admin/tax-rates', form)
      success('Poreska stopa kreirana.')
    }
    showModal.value = false
    fetchData()
  }
  catch (e) {
    serverErrors.value = getValidationErrors(e)
    if (!Object.keys(serverErrors.value).length) toastError(getErrorMessage(e))
  }
  finally { saving.value = false }
}

function confirmDelete(taxRate: TaxRate) {
  deleteTarget.value = taxRate
  deleteModal.value = true
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await del(`/admin/tax-rates/${deleteTarget.value.id}`)
    success('Poreska stopa obrisana.')
    deleteModal.value = false
    fetchData()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { deleteLoading.value = false }
}

function fieldError(field: string): string {
  return serverErrors.value[field]?.[0] || ''
}

onMounted(fetchData)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Poreske stope' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Poreske stope</h1>
      <UiAtomsButton @click="openCreate()">+ Nova stopa</UiAtomsButton>
    </div>

    <div v-if="loading" class="space-y-3">
      <UiAtomsSkeleton v-for="i in 3" :key="i" height="3rem" />
    </div>

    <div v-else-if="taxRates.length === 0" class="text-center py-12 text-gray-500">
      Nema poreskih stopa. Kreirajte prvu.
    </div>

    <div v-else class="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Naziv</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Stopa (%)</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Država</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Podrazumevana</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600">Akcije</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="rate in taxRates" :key="rate.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ rate.name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ rate.rate }}%</td>
            <td class="px-4 py-3 text-gray-600">{{ rate.country }}</td>
            <td class="px-4 py-3 text-center">
              <UiAtomsBadge v-if="rate.is_default" variant="info">Da</UiAtomsBadge>
              <span v-else class="text-gray-400">—</span>
            </td>
            <td class="px-4 py-3 text-center">
              <UiAtomsBadge :variant="rate.is_active ? 'success' : 'neutral'">
                {{ rate.is_active ? 'Aktivna' : 'Neaktivna' }}
              </UiAtomsBadge>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                <UiAtomsButton variant="ghost" size="sm" @click="openEdit(rate)">Izmeni</UiAtomsButton>
                <UiAtomsButton variant="ghost" size="sm" @click="confirmDelete(rate)">
                  <span class="text-red-500">Obriši</span>
                </UiAtomsButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit modal -->
    <UiMoleculesModal v-model="showModal" :title="editing ? 'Izmeni poresku stopu' : 'Nova poreska stopa'" size="sm">
      <form class="space-y-4" @submit.prevent="handleSubmit">
        <UiAtomsInput v-model="form.name" label="Naziv" required placeholder="PDV 20%" :error="fieldError('name')" />
        <UiAtomsInput v-model.number="form.rate" label="Stopa (%)" type="number" required step="0.01" min="0" max="100" :error="fieldError('rate')" />
        <UiAtomsInput v-model="form.country" label="Država (ISO kod)" required placeholder="RS" maxlength="2" :error="fieldError('country')" />
        <div class="flex gap-6">
          <UiAtomsSwitch v-model="form.is_default" label="Podrazumevana" />
          <UiAtomsSwitch v-model="form.is_active" label="Aktivna" />
        </div>
      </form>

      <template #footer>
        <UiAtomsButton variant="secondary" @click="showModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="saving" @click="handleSubmit">{{ editing ? 'Sačuvaj' : 'Kreiraj' }}</UiAtomsButton>
      </template>
    </UiMoleculesModal>

    <!-- Delete confirm -->
    <UiMoleculesConfirmDialog
      v-model="deleteModal"
      title="Brisanje poreske stope"
      :message="`Da li ste sigurni da želite da obrišete '${deleteTarget?.name}'?`"
      confirm-text="Obriši"
      :loading="deleteLoading"
      @confirm="handleDelete"
    />
  </div>
</template>
