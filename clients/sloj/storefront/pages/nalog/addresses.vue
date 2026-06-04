<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

const authStore = useAuthStore()
const { apiBase } = useApi()

interface Address {
  id: number; label: string | null; first_name: string; last_name: string
  company: string | null; address_line_1: string; address_line_2: string | null
  city: string; postal_code: string; country: string; phone: string | null; is_default: boolean
}

const addresses = ref<Address[]>([])
const loading = ref(true)
const showForm = ref(false)
const editId = ref<number | null>(null)

const form = reactive({
  label: '', first_name: '', last_name: '', company: '',
  address_line_1: '', address_line_2: '', city: '', postal_code: '',
  country: 'RS', phone: '', is_default: false,
})

const headers = computed(() => ({
  Authorization: `Bearer ${authStore.token}`,
  'Content-Type': 'application/json',
  Accept: 'application/json',
}))

async function fetchAddresses() {
  loading.value = true
  try {
    const data = await $fetch<{ data: Address[] }>(`${apiBase}/v1/addresses`, { headers: headers.value })
    addresses.value = data.data
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function openCreate() {
  editId.value = null
  Object.assign(form, { label: '', first_name: '', last_name: '', company: '', address_line_1: '', address_line_2: '', city: '', postal_code: '', country: 'RS', phone: '', is_default: false })
  showForm.value = true
}

function openEdit(addr: Address) {
  editId.value = addr.id
  Object.assign(form, addr)
  showForm.value = true
}

async function handleSubmit() {
  try {
    if (editId.value) {
      await $fetch(`${apiBase}/v1/addresses/${editId.value}`, { method: 'PUT', headers: headers.value, body: JSON.stringify(form) })
    } else {
      await $fetch(`${apiBase}/v1/addresses`, { method: 'POST', headers: headers.value, body: JSON.stringify(form) })
    }
    showForm.value = false
    fetchAddresses()
  }
  catch { /* silent */ }
}

async function deleteAddress(id: number) {
  if (!confirm('Da li ste sigurni?')) return
  await $fetch(`${apiBase}/v1/addresses/${id}`, { method: 'DELETE', headers: headers.value })
  fetchAddresses()
}

onMounted(fetchAddresses)
useHead({ title: 'Adrese — sloj kolektiv' })
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: 'Moj nalog', to: '/nalog' }, { label: 'Adrese' }]" />

    <div class="flex flex-col lg:flex-row gap-8">
      <LayoutAccountSidebar />

      <div class="flex-1">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-2xl font-bold text-gray-800">Adrese</h1>
          <UiAtomsButton @click="openCreate">+ Nova adresa</UiAtomsButton>
        </div>

        <div v-if="loading" class="space-y-3">
          <UiAtomsSkeleton v-for="i in 2" :key="i" height="80px" />
        </div>

        <UiMoleculesEmptyState
          v-else-if="addresses.length === 0"
          variant="addresses"
          size="md"
          title="Nemate sačuvanih adresa"
          description="Adresa za dostavu i naplatu će biti zapamćene posle prve kupovine — možete ih i ručno dodati."
        />

        <div v-else class="space-y-3">
          <div v-for="addr in addresses" :key="addr.id" class="border border-gray-200 p-4 flex justify-between items-start" :class="{ 'border-primary-300 bg-primary-50': addr.is_default }">
            <div>
              <p class="font-medium text-gray-800">
                {{ addr.first_name }} {{ addr.last_name }}
                <span v-if="addr.label" class="text-xs text-gray-400 ml-2">{{ addr.label }}</span>
                <span v-if="addr.is_default" class="text-xs text-primary-600 font-semibold ml-2">Podrazumevana</span>
              </p>
              <p class="text-sm text-gray-600">{{ addr.address_line_1 }}<span v-if="addr.address_line_2">, {{ addr.address_line_2 }}</span></p>
              <p class="text-sm text-gray-600">{{ addr.postal_code }} {{ addr.city }}</p>
              <p v-if="addr.phone" class="text-sm text-gray-400">{{ addr.phone }}</p>
            </div>
            <div class="flex gap-2 flex-shrink-0">
              <button class="text-xs text-primary-600 hover:underline" @click="openEdit(addr)">Izmeni</button>
              <button class="text-xs text-red-500 hover:underline" @click="deleteAddress(addr.id)">Obriši</button>
            </div>
          </div>
        </div>

        <!-- Form modal -->
        <UiMoleculesModal v-model="showForm" :title="editId ? 'Izmeni adresu' : 'Nova adresa'">
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <UiAtomsInput v-model="form.label" label="Naziv (npr. Kuća, Posao)" />
            <div class="grid grid-cols-2 gap-4">
              <UiAtomsInput v-model="form.first_name" label="Ime" required />
              <UiAtomsInput v-model="form.last_name" label="Prezime" required />
            </div>
            <UiAtomsInput v-model="form.company" label="Firma" />
            <UiAtomsInput v-model="form.address_line_1" label="Adresa" required />
            <UiAtomsInput v-model="form.address_line_2" label="Adresa 2" />
            <div class="grid grid-cols-2 gap-4">
              <UiAtomsInput v-model="form.city" label="Grad" required />
              <UiAtomsInput v-model="form.postal_code" label="Poštanski broj" required />
            </div>
            <UiAtomsInput v-model="form.phone" label="Telefon" type="tel" />
          </form>
          <template #footer>
            <UiAtomsButton variant="secondary" @click="showForm = false">Otkaži</UiAtomsButton>
            <UiAtomsButton @click="handleSubmit">{{ editId ? 'Sačuvaj' : 'Dodaj' }}</UiAtomsButton>
          </template>
        </UiMoleculesModal>
      </div>
    </div>
  </div>
</template>
