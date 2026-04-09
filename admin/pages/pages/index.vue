<script setup lang="ts">
const { get, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

interface Page { id: number; title: string; slug: string; status: string; created_at: string }

const pages = ref<Page[]>([])
const loading = ref(true)

async function fetchPages() {
  loading.value = true
  try {
    const data = await get<{ data: Page[] }>('/admin/pages')
    pages.value = data.data
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function deletePage(id: number) {
  if (!confirm('Obrisati stranicu?')) return
  try { await del(`/admin/pages/${id}`); success('Stranica obrisana.'); fetchPages() }
  catch (e) { toastError(getErrorMessage(e)) }
}

onMounted(fetchPages)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Stranice' }]" />
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Statičke stranice</h1>
    </div>

    <div v-if="loading" class="space-y-3"><UiAtomsSkeleton v-for="i in 4" :key="i" height="50px" /></div>
    <div v-else-if="!pages.length" class="text-center py-16 text-gray-500">Nema stranica.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="p in pages" :key="p.id" class="flex items-center justify-between px-5 py-3">
        <div>
          <p class="font-medium text-gray-800">{{ p.title }}</p>
          <span class="text-xs text-gray-500">/{{ p.slug }}</span>
        </div>
        <div class="flex gap-2">
          <UiAtomsButton variant="ghost" size="sm" @click="deletePage(p.id)"><span class="text-red-500">Obriši</span></UiAtomsButton>
        </div>
      </div>
    </div>
  </div>
</template>
