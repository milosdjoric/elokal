<script setup lang="ts">
import type { Category } from '~/types'

const { get, post, put, del, getErrorMessage, getValidationErrors } = useApi()
const { success, error: toastError } = useToast()
const { generateSlug } = useSlug()

const categories = ref<Category[]>([])
const loading = ref(true)

const showModal = ref(false)
const editingCategory = ref<Category | null>(null)
const saving = ref(false)
const serverErrors = ref<Record<string, string[]>>({})
const slugManual = ref(false)

const form = reactive({
  parent_id: null as number | null,
  name: '',
  slug: '',
  description: '',
  image_path: '',
  sort_order: 0,
  is_active: true,
})

const deleteModal = ref(false)
const deleteTarget = ref<Category | null>(null)
const deleteLoading = ref(false)

const showMediaPicker = ref(false)

async function fetchCategories() {
  loading.value = true
  try {
    const data = await get<{ data: Category[] }>('/admin/categories')
    categories.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function openCreate(parentId: number | null = null) {
  editingCategory.value = null
  slugManual.value = false
  serverErrors.value = {}
  Object.assign(form, { parent_id: parentId, name: '', slug: '', description: '', image_path: '', sort_order: 0, is_active: true })
  showModal.value = true
}

function openEdit(category: Category) {
  editingCategory.value = category
  slugManual.value = true
  serverErrors.value = {}
  Object.assign(form, {
    parent_id: category.parent_id, name: category.name, slug: category.slug,
    description: category.description || '', image_path: category.image_path || '',
    sort_order: category.sort_order, is_active: category.is_active,
  })
  showModal.value = true
}

watch(() => form.name, (val) => {
  if (!slugManual.value) form.slug = generateSlug(val)
})

async function handleSubmit() {
  saving.value = true
  serverErrors.value = {}
  try {
    if (editingCategory.value) {
      await put(`/admin/categories/${editingCategory.value.id}`, form)
      success('Kategorija ažurirana.')
    } else {
      await post('/admin/categories', form)
      success('Kategorija kreirana.')
    }
    showModal.value = false
    fetchCategories()
  }
  catch (e) {
    serverErrors.value = getValidationErrors(e)
    if (!Object.keys(serverErrors.value).length) toastError(getErrorMessage(e))
  }
  finally { saving.value = false }
}

function confirmDelete(category: Category) {
  deleteTarget.value = category
  deleteModal.value = true
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await del(`/admin/categories/${deleteTarget.value.id}`)
    success('Kategorija obrisana.')
    deleteModal.value = false
    fetchCategories()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { deleteLoading.value = false }
}

async function moveCategory(cat: Category, dir: 'up' | 'down', siblings: Category[]) {
  const index = siblings.findIndex(c => c.id === cat.id)
  if (dir === 'up' && index <= 0) return
  if (dir === 'down' && index >= siblings.length - 1) return

  const order = siblings.map((c, i) => ({ id: c.id, sort_order: i, parent_id: c.parent_id }))
  const swapIndex = dir === 'up' ? index - 1 : index + 1
  const temp = order[index].sort_order
  order[index].sort_order = order[swapIndex].sort_order
  order[swapIndex].sort_order = temp

  try {
    await post('/admin/categories/reorder', { order })
    fetchCategories()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

function onMediaSelect(imagePath: string) {
  form.image_path = imagePath
  showMediaPicker.value = false
}

function fieldError(field: string): string {
  return serverErrors.value[field]?.[0] || ''
}

onMounted(fetchCategories)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Kategorije' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Kategorije</h1>
      <UiAtomsButton @click="openCreate()">+ Nova kategorija</UiAtomsButton>
    </div>

    <div v-if="loading" class="space-y-3">
      <UiAtomsSkeleton v-for="i in 4" :key="i" height="3rem" />
    </div>

    <div v-else-if="categories.length === 0" class="text-center py-12 text-gray-500">
      Nema kategorija. Kreirajte prvu.
    </div>

    <div v-else class="space-y-2">
      <div v-for="(parent, pIdx) in categories" :key="parent.id" class="bg-white border border-gray-200">
        <div class="flex items-center justify-between px-4 py-3">
          <div class="flex items-center gap-3">
            <div class="flex flex-col gap-0.5">
              <button class="text-gray-400 hover:text-gray-700 disabled:opacity-20" :disabled="pIdx === 0" @click="moveCategory(parent, 'up', categories)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
              </button>
              <button class="text-gray-400 hover:text-gray-700 disabled:opacity-20" :disabled="pIdx === categories.length - 1" @click="moveCategory(parent, 'down', categories)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
              </button>
            </div>
            <img v-if="parent.image_path" :src="resolveImageUrl(parent.image_path)" class="w-8 h-8 object-cover rounded" alt="" />
            <span class="font-semibold text-gray-800">{{ parent.name }}</span>
            <UiAtomsBadge v-if="!parent.is_active" variant="neutral">Neaktivna</UiAtomsBadge>
            <span class="text-xs text-gray-400">{{ parent.products_count }} proizvoda</span>
          </div>
          <div class="flex items-center gap-2">
            <UiAtomsButton variant="ghost" size="sm" @click="openCreate(parent.id)">+ Pod</UiAtomsButton>
            <UiAtomsButton variant="ghost" size="sm" @click="openEdit(parent)">Izmeni</UiAtomsButton>
            <UiAtomsButton variant="ghost" size="sm" @click="confirmDelete(parent)"><span class="text-red-500">Obriši</span></UiAtomsButton>
          </div>
        </div>

        <div v-if="parent.children?.length" class="border-t border-gray-100">
          <div v-for="(child, cIdx) in parent.children" :key="child.id">
            <div class="flex items-center justify-between px-4 py-2.5 pl-10 hover:bg-gray-50">
              <div class="flex items-center gap-3">
                <div class="flex flex-col gap-0.5">
                  <button class="text-gray-400 hover:text-gray-700 disabled:opacity-20" :disabled="cIdx === 0" @click="moveCategory(child, 'up', parent.children!)">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                  </button>
                  <button class="text-gray-400 hover:text-gray-700 disabled:opacity-20" :disabled="cIdx === (parent.children?.length ?? 0) - 1" @click="moveCategory(child, 'down', parent.children!)">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                  </button>
                </div>
                <img v-if="child.image_path" :src="resolveImageUrl(child.image_path)" class="w-6 h-6 object-cover rounded" alt="" />
                <span class="text-sm font-medium text-gray-700">{{ child.name }}</span>
                <UiAtomsBadge v-if="!child.is_active" variant="neutral">Neaktivna</UiAtomsBadge>
                <span class="text-xs text-gray-400">{{ child.products_count }}</span>
              </div>
              <div class="flex items-center gap-2">
                <UiAtomsButton variant="ghost" size="sm" @click="openCreate(child.id)">+ Pod</UiAtomsButton>
                <UiAtomsButton variant="ghost" size="sm" @click="openEdit(child)">Izmeni</UiAtomsButton>
                <UiAtomsButton variant="ghost" size="sm" @click="confirmDelete(child)"><span class="text-red-500">Obriši</span></UiAtomsButton>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit modal -->
    <UiMoleculesModal v-model="showModal" :title="editingCategory ? 'Izmeni kategoriju' : 'Nova kategorija'" size="md">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <UiAtomsInput v-model="form.name" label="Naziv" required :error="fieldError('name')" />
        <UiAtomsInput v-model="form.slug" label="Slug" required :error="fieldError('slug')" @focus="slugManual = true" />

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Opis</label>
          <textarea v-model="form.description" rows="3" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
        </div>

        <!-- Image -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Slika</label>
          <div class="flex items-center gap-3">
            <img v-if="form.image_path" :src="resolveImageUrl(form.image_path)" class="w-16 h-16 object-cover border border-gray-200 rounded" alt="" />
            <div v-else class="w-16 h-16 bg-gray-100 border border-gray-200 rounded flex items-center justify-center text-gray-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" /></svg>
            </div>
            <div class="flex flex-col gap-1">
              <UiAtomsButton variant="secondary" size="sm" type="button" @click="showMediaPicker = true">Izaberi iz medija</UiAtomsButton>
              <button v-if="form.image_path" type="button" class="text-xs text-red-500 hover:text-red-700 text-left" @click="form.image_path = ''">Ukloni</button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <UiAtomsInput v-model.number="form.sort_order" label="Redosled" type="number" :error="fieldError('sort_order')" />
          <div class="flex items-end pb-2">
            <UiAtomsSwitch v-model="form.is_active" label="Aktivna" />
          </div>
        </div>
      </form>

      <template #footer>
        <UiAtomsButton variant="secondary" @click="showModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="saving" @click="handleSubmit">{{ editingCategory ? 'Sačuvaj' : 'Kreiraj' }}</UiAtomsButton>
      </template>
    </UiMoleculesModal>

    <!-- Media Picker -->
    <UiMoleculesMediaPicker v-model="showMediaPicker" @select="onMediaSelect" />

    <!-- Delete confirm -->
    <UiMoleculesConfirmDialog
      v-model="deleteModal"
      title="Brisanje kategorije"
      :message="`Da li ste sigurni da želite da obrišete '${deleteTarget?.name}'?`"
      confirm-text="Obriši"
      :loading="deleteLoading"
      @confirm="handleDelete"
    />
  </div>
</template>
