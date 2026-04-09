<script setup lang="ts">
const { get, post, put, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface ShippingMethod { id: number; name: string; type: string; cost: string; free_above: string | null; estimated_days: number | null; is_active: boolean }
interface ShippingZone { id: number; name: string; countries: string[]; is_active: boolean; methods: ShippingMethod[] }

const zones = ref<ShippingZone[]>([])
const loading = ref(true)

async function fetchZones() {
  loading.value = true
  try {
    const data = await get<{ data: ShippingZone[] }>('/admin/shipping-zones')
    zones.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function deleteZone(id: number) {
  if (!confirm('Obrisati zonu?')) return
  try { await del(`/admin/shipping-zones/${id}`); success('Zona obrisana.'); fetchZones() }
  catch (e) { toastError(getErrorMessage(e)) }
}

async function deleteMethod(id: number) {
  if (!confirm('Obrisati metodu dostave?')) return
  try { await del(`/admin/shipping-methods/${id}`); success('Metoda obrisana.'); fetchZones() }
  catch (e) { toastError(getErrorMessage(e)) }
}

function typeLabel(type: string) {
  return { flat: 'Fiksna', weight_based: 'Po težini', price_based: 'Po ceni', free: 'Besplatna' }[type] || type
}

onMounted(fetchZones)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Shipping zone' }]" />
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Shipping zone i metode</h1>
    </div>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 3" :key="i" height="80px" /></div>
    <div v-else-if="!zones.length" class="text-center py-16 text-gray-500">Nema shipping zona.</div>

    <div v-else class="space-y-4">
      <div v-for="zone in zones" :key="zone.id" class="bg-white border border-gray-200">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
          <div>
            <p class="font-medium text-gray-800">{{ zone.name }}</p>
            <span class="text-xs text-gray-500">{{ zone.countries.join(', ') }}</span>
          </div>
          <UiAtomsButton variant="ghost" size="sm" @click="deleteZone(zone.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
        </div>
        <div v-if="zone.methods.length" class="divide-y divide-gray-50">
          <div v-for="m in zone.methods" :key="m.id" class="flex items-center justify-between px-5 py-2 pl-10">
            <div class="flex items-center gap-3">
              <span class="text-sm text-gray-700">{{ m.name }}</span>
              <span class="text-xs text-gray-400">{{ typeLabel(m.type) }} — {{ m.cost }} RSD</span>
              <span v-if="m.estimated_days" class="text-xs text-gray-400">~{{ m.estimated_days }}d</span>
              <UiAtomsBadge :variant="m.is_active ? 'success' : 'neutral'" class="text-[10px]">{{ m.is_active ? 'Aktivna' : 'Neaktivna' }}</UiAtomsBadge>
            </div>
            <UiAtomsButton variant="ghost" size="sm" @click="deleteMethod(m.id)"><span class="text-red-500">×</span></UiAtomsButton>
          </div>
        </div>
        <p v-else class="px-5 py-3 text-xs text-gray-400 pl-10">Nema metoda u ovoj zoni.</p>
      </div>
    </div>
  </div>
</template>
