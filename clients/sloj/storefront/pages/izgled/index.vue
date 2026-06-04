<script setup lang="ts">
const { get } = useApi()
const { isEnabled } = useFeature()

interface LookData {
  id: number
  title: string
  image_path: string
  hotspots: {
    product_id: number
    x_position: number
    y_position: number
    label: string
    product?: {
      id: number
      name: string
      slug: string
      price: string
      effective_price: string
      images?: { image_path: string; is_primary: boolean }[]
    }
  }[]
}

const looks = ref<LookData[]>([])
const loading = ref(true)
const featureActive = ref(true)

async function fetchLooks() {
  featureActive.value = await isEnabled('feature_shop_the_look', false)
  if (!featureActive.value) {
    loading.value = false
    return
  }

  try {
    const data = await get<{ data: LookData[] }>('/v1/looks')
    looks.value = data.data
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

onMounted(fetchLooks)

useHead({ title: 'Shop the Look — sloj kolektiv' })
useSeoMeta({
  description: 'Inspirišite se kompletnim outfit-ima i kupite sve proizvode sa jednog mesta.',
})
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: 'Shop the Look' }]" />

    <h1 class="text-2xl font-bold text-gray-800 mb-8">Shop the Look</h1>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <UiAtomsSkeleton v-for="i in 4" :key="i" height="20rem" />
    </div>

    <UiMoleculesEmptyState
      v-else-if="!featureActive || looks.length === 0"
      variant="looks"
      size="lg"
      title="Trenutno nema dostupnih look-ova"
      description="Pripremamo nove kompletne stylinge — vraćajte se uskoro."
    >
      <NuxtLink to="/proizvodi">
        <UiAtomsButton variant="outline">Pogledaj sve proizvode</UiAtomsButton>
      </NuxtLink>
    </UiMoleculesEmptyState>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <LookLookCard v-for="look in looks" :key="look.id" :look="look" />
    </div>
  </div>
</template>
