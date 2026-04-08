<script setup lang="ts">
const { get, post, put, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface AdminUser { id: number; name: string; email: string; role: string; permissions: string[] | null; is_active: boolean; created_at: string }

const admins = ref<AdminUser[]>([])
const loading = ref(true)

const showModal = ref(false)
const editId = ref<number | null>(null)
const saving = ref(false)

const form = reactive({
  name: '', email: '', password: '', role: 'admin' as string,
  permissions: [] as string[], is_active: true,
})

const allPermissions = [
  { key: 'products', label: 'Proizvodi' },
  { key: 'categories', label: 'Kategorije' },
  { key: 'orders', label: 'Narudžbine' },
  { key: 'customers', label: 'Kupci' },
  { key: 'reviews', label: 'Recenzije' },
  { key: 'blog', label: 'Blog' },
  { key: 'media', label: 'Media Library' },
  { key: 'settings', label: 'Podešavanja' },
  { key: 'coupons', label: 'Kuponi' },
  { key: 'reports', label: 'Izveštaji' },
]

async function fetchAdmins() {
  loading.value = true
  try {
    const data = await get<{ data: AdminUser[] }>('/admin/admins')
    admins.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function openCreate() {
  editId.value = null
  Object.assign(form, { name: '', email: '', password: '', role: 'admin', permissions: [], is_active: true })
  showModal.value = true
}

function openEdit(admin: AdminUser) {
  editId.value = admin.id
  Object.assign(form, { name: admin.name, email: admin.email, password: '', role: admin.role, permissions: admin.permissions || [], is_active: admin.is_active })
  showModal.value = true
}

async function handleSubmit() {
  saving.value = true
  try {
    const body = { ...form, permissions: form.permissions.length > 0 ? form.permissions : null }
    if (editId.value) {
      if (!body.password) delete (body as Record<string, unknown>).password
      await put(`/admin/admins/${editId.value}`, body)
      success('Admin ažuriran.')
    } else {
      await post('/admin/admins', body)
      success('Admin kreiran.')
    }
    showModal.value = false
    fetchAdmins()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { saving.value = false }
}

async function deleteAdmin(id: number) {
  if (!confirm('Obrisati admina?')) return
  try {
    await del(`/admin/admins/${id}`)
    success('Admin obrisan.')
    fetchAdmins()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

function togglePermission(key: string) {
  const i = form.permissions.indexOf(key)
  if (i >= 0) form.permissions.splice(i, 1)
  else form.permissions.push(key)
}

const roleLabels: Record<string, string> = { super_admin: 'Super Admin', admin: 'Admin', editor: 'Editor' }

onMounted(fetchAdmins)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Administratori' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Administratori</h1>
      <UiAtomsButton @click="openCreate">+ Novi admin</UiAtomsButton>
    </div>

    <div v-if="loading" class="space-y-3">
      <UiAtomsSkeleton v-for="i in 3" :key="i" height="60px" />
    </div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="admin in admins" :key="admin.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="font-medium text-gray-800">{{ admin.name }}</p>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="text-xs text-gray-500">{{ admin.email }}</span>
            <UiAtomsBadge :variant="admin.is_active ? 'success' : 'neutral'" class="text-[10px]">
              {{ admin.is_active ? roleLabels[admin.role] || admin.role : 'Neaktivan' }}
            </UiAtomsBadge>
          </div>
        </div>
        <div class="flex gap-2">
          <UiAtomsButton variant="ghost" size="sm" @click="openEdit(admin)">Izmeni</UiAtomsButton>
          <UiAtomsButton variant="ghost" size="sm" @click="deleteAdmin(admin.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
        </div>
      </div>
    </div>

    <UiMoleculesModal v-model="showModal" :title="editId ? 'Izmeni admina' : 'Novi admin'">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <UiAtomsInput v-model="form.name" label="Ime" required />
        <UiAtomsInput v-model="form.email" label="Email" type="email" required />
        <UiAtomsInput v-model="form.password" label="Lozinka" type="password" :required="!editId" :placeholder="editId ? 'Ostavite prazno da zadržite' : ''" />

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Uloga</label>
          <select v-model="form.role" class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="super_admin">Super Admin</option>
            <option value="admin">Admin</option>
            <option value="editor">Editor</option>
          </select>
        </div>

        <div v-if="form.role !== 'super_admin'">
          <label class="block text-sm font-medium text-gray-700 mb-2">Permisije</label>
          <div class="grid grid-cols-2 gap-2">
            <label v-for="perm in allPermissions" :key="perm.key" class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="checkbox" :checked="form.permissions.includes(perm.key)" class="w-4 h-4 text-primary-600 border-gray-300 rounded" @change="togglePermission(perm.key)" />
              {{ perm.label }}
            </label>
          </div>
          <p class="text-xs text-gray-400 mt-1">Super Admin ima pristup svemu.</p>
        </div>

        <div class="flex items-center gap-2">
          <UiAtomsSwitch v-model="form.is_active" label="Aktivan" />
        </div>
      </form>
      <template #footer>
        <UiAtomsButton variant="secondary" @click="showModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="saving" @click="handleSubmit">{{ editId ? 'Sačuvaj' : 'Kreiraj' }}</UiAtomsButton>
      </template>
    </UiMoleculesModal>
  </div>
</template>
