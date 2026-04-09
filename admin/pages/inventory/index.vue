<script setup lang="ts">
const { get, getErrorMessage } = useApi()
const { error: toastError } = useToast()

interface InventoryItem { id: number; name: string; sku: string | null; stock_quantity: number; is_active: boolean }

const items = ref<InventoryItem[]>([])
const loading = ref(true)

async function fetchInventory() {
  loading.value = true
  try {
    const data = await get<{ data: InventoryItem[] }>('/admin/inventory')
    items.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function stockClass(qty: number) {
  if (qty === 0) return 'text-red-600 font-bold'
  if (qty <= 5) return 'text-orange-500 font-medium'
  return 'text-gray-700'
}

onMounted(fetchInventory)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Inventar' }]" />
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Inventar</h1>
    </div>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 5" :key="i" height="45px" /></div>
    <div v-else-if="!items.length" class="text-center py-16 text-gray-500">Nema proizvoda.</div>

    <table v-else class="w-full bg-white border border-gray-200 text-sm">
      <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
        <tr>
          <th class="px-5 py-3">Proizvod</th>
          <th class="px-5 py-3">SKU</th>
          <th class="px-5 py-3 text-right">Stock</th>
          <th class="px-5 py-3">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="item in items" :key="item.id">
          <td class="px-5 py-3 font-medium text-gray-800">{{ item.name }}</td>
          <td class="px-5 py-3 text-gray-500">{{ item.sku || '—' }}</td>
          <td class="px-5 py-3 text-right" :class="stockClass(item.stock_quantity)">{{ item.stock_quantity }}</td>
          <td class="px-5 py-3">
            <UiAtomsBadge :variant="item.stock_quantity > 0 ? 'success' : 'danger'" class="text-[10px]">
              {{ item.stock_quantity === 0 ? 'Nema' : item.stock_quantity <= 5 ? 'Malo' : 'OK' }}
            </UiAtomsBadge>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
