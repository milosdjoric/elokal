<script setup lang="ts">
const route = useRoute()
const { get } = useApi()

interface PageData {
  id: number
  title: string
  slug: string
  content: string
  meta_title: string | null
  meta_description: string | null
}

const page = ref<PageData | null>(null)
const loading = ref(true)
const error = ref(false)

async function fetchPage() {
  loading.value = true
  try {
    const data = await get<{ data: PageData }>(`/v1/pages/${route.params.slug}`)
    page.value = data.data
  }
  catch { error.value = true }
  finally { loading.value = false }
}

onMounted(fetchPage)

useHead({
  title: computed(() => page.value?.meta_title || (page.value ? `${page.value.title} — eLokal` : 'eLokal')),
})

useSeoMeta({
  description: computed(() => page.value?.meta_description || ''),
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <div v-if="loading" class="py-20 flex justify-center">
      <UiAtomsSpinner size="lg" />
    </div>

    <div v-else-if="error" class="text-center py-16">
      <p class="text-gray-500 mb-4">Stranica nije pronađena.</p>
      <NuxtLink to="/">
        <UiAtomsButton variant="outline">Nazad na početnu</UiAtomsButton>
      </NuxtLink>
    </div>

    <template v-else-if="page">
      <LayoutBreadcrumbs :items="[{ label: page.title }]" />

      <h1 class="text-3xl font-bold text-gray-800 mb-8">{{ page.title }}</h1>

      <article class="prose prose-lg max-w-none text-gray-700" v-html="page.content" />
    </template>
  </div>
</template>
