<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

interface Download {
  id: number
  file_name: string
  file_size: number
  order_number: string
  download_count: number
  max_downloads: number | null
  expires_at: string | null
  can_download: boolean
  token: string
  created_at: string
}

const { get, apiBase } = useApi()
const downloads = ref<Download[]>([])
const loading = ref(true)

async function fetchDownloads() {
  loading.value = true
  try {
    const data = await get<{ data: Download[] }>('/v1/downloads')
    downloads.value = data.data
  }
  catch { /* ignorisano */ }
  finally { loading.value = false }
}

function formatSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1048576).toFixed(1)} MB`
}

function downloadFile(token: string) {
  window.open(`${apiBase}/v1/downloads/${token}`, '_blank')
}

onMounted(fetchDownloads)

useHead({ title: 'Preuzimanja — sloj kolektiv' })
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Moja preuzimanja</h1>

    <div v-if="loading" class="text-sm text-gray-500">Učitavanje...</div>

    <UiMoleculesEmptyState
      v-else-if="!downloads.length"
      variant="downloads"
      title="Nema dostupnih preuzimanja"
      description="Digitalni proizvodi koje kupite biće ovde dostupni za preuzimanje."
    >
      <NuxtLink to="/proizvodi">
        <UiAtomsButton variant="outline">Pogledaj proizvode</UiAtomsButton>
      </NuxtLink>
    </UiMoleculesEmptyState>

    <div v-else class="space-y-3">
      <div
        v-for="dl in downloads"
        :key="dl.id"
        class="border border-gray-200 p-4 flex items-center justify-between"
      >
        <div>
          <h3 class="font-medium text-gray-800">{{ dl.file_name }}</h3>
          <div class="flex gap-4 mt-1 text-xs text-gray-500">
            <span>Narudžbina: {{ dl.order_number }}</span>
            <span>{{ formatSize(dl.file_size) }}</span>
            <span>
              Preuzeto: {{ dl.download_count }}{{ dl.max_downloads ? `/${dl.max_downloads}` : '' }}
            </span>
            <span v-if="dl.expires_at">
              Ističe: {{ new Date(dl.expires_at).toLocaleDateString('sr-RS') }}
            </span>
          </div>
        </div>

        <UiAtomsButton
          v-if="dl.can_download"
          size="sm"
          @click="downloadFile(dl.token)"
        >
          Preuzmi
        </UiAtomsButton>
        <span v-else class="text-xs text-red-500">Nedostupno</span>
      </div>
    </div>
  </div>
</template>
