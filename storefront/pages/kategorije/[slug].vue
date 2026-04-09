<script setup lang="ts">
const route = useRoute()
const { get } = useApi()

// Pronađi category ID po slug-u i redirectuj na PLP sa filterom
async function resolve() {
  try {
    const data = await get<{ data: { id: number; name: string; slug: string }[] }>('/v1/categories')
    const allCats = data.data

    // Traži u parent i child kategorijama
    let found: { id: number } | undefined
    for (const cat of allCats) {
      if (cat.slug === route.params.slug) { found = cat; break }
      if ((cat as unknown as { children?: { id: number; slug: string }[] }).children) {
        const child = (cat as unknown as { children: { id: number; slug: string }[] }).children.find(c => c.slug === route.params.slug)
        if (child) { found = child; break }
      }
    }

    if (found) {
      navigateTo({ path: '/proizvodi', query: { category: String(found.id) } }, { replace: true })
    } else {
      navigateTo('/proizvodi', { replace: true })
    }
  }
  catch {
    navigateTo('/proizvodi', { replace: true })
  }
}

onMounted(resolve)
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-20 text-center">
    <UiAtomsSpinner size="lg" />
  </div>
</template>
