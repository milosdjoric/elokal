<script setup lang="ts">
interface LookProduct {
  id: number
  name: string
  slug: string
  price: string
}

interface Hotspot {
  product_id: number
  x_position: number
  y_position: number
  label: string
  product?: LookProduct
}

interface Look {
  id: number
  title: string
  image_path: string
  is_active: boolean
  sort_order: number
  hotspots: Hotspot[]
}

const { get, post, put, del, getErrorMessage, getValidationErrors } = useApi()
const { success, error: toastError } = useToast()

const looks = ref<Look[]>([])
const loading = ref(true)

const showModal = ref(false)
const editing = ref<Look | null>(null)
const saving = ref(false)
const serverErrors = ref<Record<string, string[]>>({})

const form = reactive({
  title: '',
  image_path: '',
  is_active: true,
  sort_order: 0,
})

const hotspots = ref<Hotspot[]>([])

const deleteModal = ref(false)
const deleteTarget = ref<Look | null>(null)
const deleteLoading = ref(false)

const showMediaPicker = ref(false)

// Proizvodi za picker
const products = ref<LookProduct[]>([])
const productSearch = ref('')

// Hotspot dodavanje
const addingHotspot = ref(false)
const hotspotProductId = ref<number | null>(null)
const hotspotLabel = ref('')

async function fetchData() {
  loading.value = true
  try {
    const data = await get<{ data: Look[] }>('/admin/looks')
    looks.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function searchProducts(q: string) {
  if (q.length < 2) return
  try {
    const data = await get<{ data: LookProduct[] }>('/admin/products', { search: q, per_page: 10 })
    products.value = data.data
  }
  catch { /* silent */ }
}

function openCreate() {
  editing.value = null
  serverErrors.value = {}
  Object.assign(form, { title: '', image_path: '', is_active: true, sort_order: 0 })
  hotspots.value = []
  showModal.value = true
}

function openEdit(look: Look) {
  editing.value = look
  serverErrors.value = {}
  Object.assign(form, {
    title: look.title,
    image_path: look.image_path,
    is_active: look.is_active,
    sort_order: look.sort_order,
  })
  hotspots.value = look.hotspots.map(h => ({ ...h }))
  showModal.value = true
}

function onMediaSelect(imagePath: string) {
  form.image_path = imagePath
  showMediaPicker.value = false
}

function onImageClick(event: MouseEvent) {
  if (!addingHotspot.value || !hotspotProductId.value) return

  const target = event.currentTarget as HTMLElement
  const rect = target.getBoundingClientRect()
  const x = ((event.clientX - rect.left) / rect.width) * 100
  const y = ((event.clientY - rect.top) / rect.height) * 100

  const product = products.value.find(p => p.id === hotspotProductId.value)

  hotspots.value.push({
    product_id: hotspotProductId.value,
    x_position: Math.round(x * 100) / 100,
    y_position: Math.round(y * 100) / 100,
    label: hotspotLabel.value || product?.name || '',
    product: product || undefined,
  })

  addingHotspot.value = false
  hotspotProductId.value = null
  hotspotLabel.value = ''
}

function removeHotspot(index: number) {
  hotspots.value.splice(index, 1)
}

async function handleSubmit() {
  saving.value = true
  serverErrors.value = {}
  try {
    const payload = {
      ...form,
      hotspots: hotspots.value.map(h => ({
        product_id: h.product_id,
        x_position: h.x_position,
        y_position: h.y_position,
        label: h.label || null,
      })),
    }

    if (editing.value) {
      await put(`/admin/looks/${editing.value.id}`, payload)
      success('Look ažuriran.')
    } else {
      await post('/admin/looks', payload)
      success('Look kreiran.')
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

function confirmDelete(look: Look) {
  deleteTarget.value = look
  deleteModal.value = true
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await del(`/admin/looks/${deleteTarget.value.id}`)
    success('Look obrisan.')
    deleteModal.value = false
    fetchData()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { deleteLoading.value = false }
}

onMounted(fetchData)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Shop the Look' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Shop the Look</h1>
      <UiAtomsButton @click="openCreate()">+ Novi look</UiAtomsButton>
    </div>

    <div v-if="loading" class="space-y-3">
      <UiAtomsSkeleton v-for="i in 3" :key="i" height="6rem" />
    </div>

    <div v-else-if="looks.length === 0" class="text-center py-12 text-gray-500">
      Nema kreiranih look-ova. Kreirajte prvi.
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="look in looks" :key="look.id" class="border border-gray-200 rounded-lg overflow-hidden bg-white">
        <div class="relative aspect-[4/3]">
          <img :src="resolveImageUrl(look.image_path)" class="w-full h-full object-cover" :alt="look.title" />
          <!-- Hotspot markeri -->
          <div
            v-for="(hs, i) in look.hotspots"
            :key="i"
            class="absolute w-6 h-6 -translate-x-1/2 -translate-y-1/2 bg-white border-2 border-primary-600 rounded-full flex items-center justify-center text-[10px] font-bold text-primary-600 shadow"
            :style="{ left: `${hs.x_position}%`, top: `${hs.y_position}%` }"
          >
            {{ i + 1 }}
          </div>
          <UiAtomsBadge v-if="!look.is_active" variant="neutral" class="absolute top-2 left-2">Neaktivan</UiAtomsBadge>
        </div>
        <div class="p-4">
          <h3 class="font-semibold text-gray-800">{{ look.title }}</h3>
          <p class="text-xs text-gray-500 mt-1">{{ look.hotspots.length }} proizvoda</p>
          <div class="flex gap-2 mt-3">
            <UiAtomsButton variant="ghost" size="sm" @click="openEdit(look)">Izmeni</UiAtomsButton>
            <UiAtomsButton variant="ghost" size="sm" @click="confirmDelete(look)"><span class="text-red-500">Obriši</span></UiAtomsButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit modal -->
    <UiMoleculesModal v-model="showModal" :title="editing ? 'Izmeni look' : 'Novi look'" size="lg">
      <form class="space-y-4" @submit.prevent="handleSubmit">
        <UiAtomsInput v-model="form.title" label="Naziv" required />

        <!-- Slika -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Slika</label>
          <div class="flex items-start gap-3">
            <div
              v-if="form.image_path"
              class="relative w-full max-w-md aspect-[4/3] border border-gray-200 cursor-crosshair"
              :class="{ 'ring-2 ring-primary-500': addingHotspot }"
              @click="onImageClick"
            >
              <img :src="resolveImageUrl(form.image_path)" class="w-full h-full object-cover" alt="" />
              <!-- Hotspot markeri -->
              <div
                v-for="(hs, i) in hotspots"
                :key="i"
                class="absolute w-6 h-6 -translate-x-1/2 -translate-y-1/2 bg-white border-2 border-primary-600 rounded-full flex items-center justify-center text-[10px] font-bold text-primary-600 shadow cursor-pointer"
                :style="{ left: `${hs.x_position}%`, top: `${hs.y_position}%` }"
                :title="hs.label || hs.product?.name || ''"
              >
                {{ i + 1 }}
              </div>
              <p v-if="addingHotspot" class="absolute bottom-0 left-0 right-0 bg-primary-600 text-white text-xs text-center py-1">
                Kliknite na sliku za poziciju hotspota
              </p>
            </div>
            <div v-else class="w-full max-w-md aspect-[4/3] bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-300">
              Nema slike
            </div>
          </div>
          <UiAtomsButton variant="secondary" size="sm" type="button" class="mt-2" @click="showMediaPicker = true">
            {{ form.image_path ? 'Promeni sliku' : 'Izaberi sliku' }}
          </UiAtomsButton>
        </div>

        <!-- Hotspot dodavanje -->
        <div v-if="form.image_path" class="border border-gray-200 rounded-lg p-4">
          <h4 class="text-sm font-semibold text-gray-700 mb-3">Hotspotovi ({{ hotspots.length }})</h4>

          <!-- Lista hotspotova -->
          <div v-if="hotspots.length > 0" class="space-y-2 mb-4">
            <div v-for="(hs, i) in hotspots" :key="i" class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded text-sm">
              <div class="flex items-center gap-2">
                <span class="w-5 h-5 bg-primary-600 text-white rounded-full flex items-center justify-center text-[10px] font-bold">{{ i + 1 }}</span>
                <span class="text-gray-800">{{ hs.product?.name || `Proizvod #${hs.product_id}` }}</span>
                <span class="text-gray-400">({{ hs.x_position }}%, {{ hs.y_position }}%)</span>
              </div>
              <button type="button" class="text-red-400 hover:text-red-600 text-xs" @click="removeHotspot(i)">Ukloni</button>
            </div>
          </div>

          <!-- Dodaj hotspot -->
          <div v-if="!addingHotspot" class="space-y-2">
            <div class="flex gap-2">
              <input
                v-model="productSearch"
                type="text"
                placeholder="Pretraži proizvod..."
                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-primary-500"
                @input="searchProducts(productSearch)"
              />
            </div>
            <div v-if="products.length > 0 && productSearch.length >= 2" class="border border-gray-200 rounded max-h-32 overflow-y-auto">
              <button
                v-for="p in products"
                :key="p.id"
                type="button"
                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50"
                @click="hotspotProductId = p.id; hotspotLabel = p.name; addingHotspot = true; productSearch = ''; products = []"
              >
                {{ p.name }}
              </button>
            </div>
          </div>
          <p v-else class="text-xs text-primary-600">Odabrali ste: {{ hotspotLabel }}. Kliknite na sliku iznad.</p>
        </div>

        <div class="flex gap-4">
          <UiAtomsSwitch v-model="form.is_active" label="Aktivan" />
          <UiAtomsInput v-model.number="form.sort_order" label="Redosled" type="number" class="w-24" />
        </div>
      </form>

      <template #footer>
        <UiAtomsButton variant="secondary" @click="showModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="saving" @click="handleSubmit">{{ editing ? 'Sačuvaj' : 'Kreiraj' }}</UiAtomsButton>
      </template>
    </UiMoleculesModal>

    <!-- Media Picker -->
    <UiMoleculesMediaPicker v-model="showMediaPicker" @select="onMediaSelect" />

    <!-- Delete confirm -->
    <UiMoleculesConfirmDialog
      v-model="deleteModal"
      title="Brisanje look-a"
      :message="`Da li ste sigurni da želite da obrišete '${deleteTarget?.title}'?`"
      confirm-text="Obriši"
      :loading="deleteLoading"
      @confirm="handleDelete"
    />
  </div>
</template>
