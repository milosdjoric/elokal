<script setup lang="ts">
import type { Category } from '~/types'

const { get, post, put, del, getErrorMessage, getValidationErrors } = useApi()
const { success, error: toastError } = useToast()
const { generateSlug } = useSlug()

const categories = ref<Category[]>([])
const loading = ref(true)

// Modal state
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
  sort_order: 0,
  is_active: true,
})

// Delete state
const deleteModal = ref(false)
const deleteTarget = ref<Category | null>(null)
const deleteLoading = ref(false)

async function fetchCategories() {
  loading.value = true
  try {
    const data = await get<{ data: Category[] }>('/admin/categories')
    categories.value = data.data
  }
  catch (e) {
    toastError(getErrorMessage(e))
  }
  finally {
    loading.value = false
  }
}

function openCreate(parentId: number | null = null) {
  editingCategory.value = null
  slugManual.value = false
  serverErrors.value = {}
  Object.assign(form, {
    parent_id: parentId,
    name: '',
    slug: '',
    description: '',
    sort_order: 0,
    is_active: true,
  })
  showModal.value = true
}

function openEdit(category: Category) {
  editingCategory.value = category
  slugManual.value = true
  serverErrors.value = {}
  Object.assign(form, {
    parent_id: category.parent_id,
    name: category.name,
    slug: category.slug,
    description: category.description || '',
    sort_order: category.sort_order,
    is_active: category.is_active,
  })
  showModal.value = true
}

watch(() => form.name, (val) => {
  if (!slugManual.value) {
    form.slug = generateSlug(val)
  }
})

async function handleSubmit() {
  saving.value = true
  serverErrors.value = {}

  try {
    if (editingCategory.value) {
      await put(`/admin/categories/${editingCategory.value.id}`, form)
      success('Kategorija ažurirana.')
    }
    else {
      await post('/admin/categories', form)
      success('Kategorija kreirana.')
    }
    showModal.value = false
    fetchCategories()
  }
  catch (e) {
    serverErrors.value = getValidationErrors(e)
    if (!Object.keys(serverErrors.value).length) {
      toastError(getErrorMessage(e))
    }
  }
  finally {
    saving.value = false
  }
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
  catch (e) {
    toastError(getErrorMessage(e))
  }
  finally {
    deleteLoading.value = false
  }
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

    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <UiAtomsSkeleton v-for="i in 4" :key="i" height="3rem" />
    </div>

    <!-- Empty -->
    <div v-else-if="categories.length === 0" class="text-center py-12 text-gray-500">
      Nema kategorija. Kreirajte prvu.
    </div>

    <!-- Tree -->
    <div v-else class="space-y-2">
      <div
        v-for="parent in categories"
        :key="parent.id"
        class="bg-white border border-gray-200"
      >
        <!-- Parent -->
        <div class="flex items-center justify-between px-4 py-3">
          <div class="flex items-center gap-3">
            <span class="font-semibold text-gray-800">{{ parent.name }}</span>
            <UiAtomsBadge v-if="!parent.is_active" variant="neutral">Neaktivna</UiAtomsBadge>
            <span class="text-xs text-gray-400">{{ parent.products_count }} proizvoda</span>
          </div>
          <div class="flex items-center gap-2">
            <UiAtomsButton variant="ghost" size="sm" @click="openCreate(parent.id)">
              + Pod
            </UiAtomsButton>
            <UiAtomsButton variant="ghost" size="sm" @click="openEdit(parent)">
              Izmeni
            </UiAtomsButton>
            <UiAtomsButton variant="ghost" size="sm" @click="confirmDelete(parent)">
              <span class="text-red-500">Obriši</span>
            </UiAtomsButton>
          </div>
        </div>

        <!-- Children (level 2) -->
        <div v-if="parent.children?.length" class="border-t border-gray-100">
          <div v-for="child in parent.children" :key="child.id">
            <div class="flex items-center justify-between px-4 py-2.5 pl-10 hover:bg-gray-50">
              <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700">{{ child.name }}</span>
                <UiAtomsBadge v-if="!child.is_active" variant="neutral">Neaktivna</UiAtomsBadge>
                <span class="text-xs text-gray-400">{{ child.products_count }} proizvoda</span>
              </div>
              <div class="flex items-center gap-2">
                <UiAtomsButton variant="ghost" size="sm" @click="openCreate(child.id)">
                  + Pod
                </UiAtomsButton>
                <UiAtomsButton variant="ghost" size="sm" @click="openEdit(child)">
                  Izmeni
                </UiAtomsButton>
                <UiAtomsButton variant="ghost" size="sm" @click="confirmDelete(child)">
                  <span class="text-red-500">Obriši</span>
                </UiAtomsButton>
              </div>
            </div>

            <!-- Grandchildren (level 3) -->
            <div v-if="child.children?.length">
              <div
                v-for="grandchild in child.children"
                :key="grandchild.id"
                class="flex items-center justify-between px-4 py-2 pl-16 hover:bg-gray-50 border-t border-gray-50"
              >
                <div class="flex items-center gap-3">
                  <span class="text-sm text-gray-600">{{ grandchild.name }}</span>
                  <UiAtomsBadge v-if="!grandchild.is_active" variant="neutral">Neaktivna</UiAtomsBadge>
                  <span class="text-xs text-gray-400">{{ grandchild.products_count }} proizvoda</span>
                </div>
                <div class="flex items-center gap-2">
                  <UiAtomsButton variant="ghost" size="sm" @click="openEdit(grandchild)">
                    Izmeni
                  </UiAtomsButton>
                  <UiAtomsButton variant="ghost" size="sm" @click="confirmDelete(grandchild)">
                    <span class="text-red-500">Obriši</span>
                  </UiAtomsButton>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit modal -->
    <UiMoleculesModal v-model="showModal" :title="editingCategory ? 'Izmeni kategoriju' : 'Nova kategorija'" size="md">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <UiAtomsInput
          v-model="form.name"
          label="Naziv"
          required
          :error="fieldError('name')"
        />

        <UiAtomsInput
          v-model="form.slug"
          label="Slug"
          required
          :error="fieldError('slug')"
          @focus="slugManual = true"
        />

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Opis</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <UiAtomsInput
            v-model.number="form.sort_order"
            label="Redosled"
            type="number"
            :error="fieldError('sort_order')"
          />
          <div class="flex items-end pb-2">
            <UiAtomsSwitch v-model="form.is_active" label="Aktivna" />
          </div>
        </div>
      </form>

      <template #footer>
        <UiAtomsButton variant="secondary" @click="showModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="saving" @click="handleSubmit">
          {{ editingCategory ? 'Sačuvaj' : 'Kreiraj' }}
        </UiAtomsButton>
      </template>
    </UiMoleculesModal>

    <!-- Delete confirm -->
    <UiMoleculesConfirmDialog
      v-model="deleteModal"
      title="Brisanje kategorije"
      :message="`Da li ste sigurni da želite da obrišete '${deleteTarget?.name}'? Podkategorije će ostati bez roditelja.`"
      confirm-text="Obriši"
      :loading="deleteLoading"
      @confirm="handleDelete"
    />
  </div>
</template>
