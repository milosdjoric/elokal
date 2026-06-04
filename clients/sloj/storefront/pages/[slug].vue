<script setup lang="ts">
const route = useRoute()
const { apiBase } = useApi()

interface PageData {
  id: number
  title: string
  slug: string
  content: string
  meta_title: string | null
  meta_description: string | null
}

const { data, error: fetchError } = await useAsyncData(
  () => `cms-page-${route.params.slug}`,
  () => $fetch<{ data: PageData }>(`${apiBase}/v1/pages/${route.params.slug}`, {
    headers: { Accept: 'application/json' },
  }),
  { watch: [() => route.params.slug] },
)

if (fetchError.value || !data.value?.data) {
  throw createError({ statusCode: 404, statusMessage: 'Stranica nije pronađena', fatal: true })
}

const page = computed(() => data.value!.data)

useHead({
  title: computed(() => page.value.meta_title || `${page.value.title} — sloj kolektiv`),
})

useSeoMeta({
  description: computed(() => page.value.meta_description || ''),
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: page.title }]" />
    <h1 class="text-3xl font-bold text-gray-800 mb-8">{{ page.title }}</h1>
    <article class="prose prose-lg max-w-none text-gray-700" v-html="page.content" />
  </div>
</template>
