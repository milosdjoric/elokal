<script setup lang="ts">
import type { Post, PostCategory, PaginatedResponse } from '~/types'

const route = useRoute()
const { get } = useApi()

const posts = ref<Post[]>([])
const category = ref<PostCategory | null>(null)
const loading = ref(true)
const page = ref(1)
const lastPage = ref(1)

async function fetchPosts(p = 1) {
  loading.value = true
  try {
    const data = await get<PaginatedResponse<Post> & { category: PostCategory }>(`/v1/blog/category/${route.params.slug}`, { page: p })
    posts.value = data.data
    category.value = data.category
    page.value = data.meta.current_page
    lastPage.value = data.meta.last_page
  }
  catch { navigateTo('/blog') }
  finally { loading.value = false }
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('sr-Latn-RS', { day: 'numeric', month: 'long', year: 'numeric' })
}

function imageUrl(path: string | null): string {
  return resolveImageUrl(path)
}

onMounted(() => fetchPosts())

useHead({ title: computed(() => category.value ? `${category.value.name} — Blog — eLokal` : 'Blog — eLokal') })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <LayoutBreadcrumbs :items="[
      { label: 'Blog', to: '/blog' },
      { label: category?.name || '...' },
    ]" />

    <h1 class="text-3xl font-bold text-gray-800 mb-8">{{ category?.name }}</h1>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <UiAtomsSkeleton v-for="i in 6" :key="i" height="280px" />
    </div>

    <div v-else-if="posts.length === 0" class="text-center py-16 text-gray-400">
      Nema članaka u ovoj kategoriji.
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <NuxtLink v-for="post in posts" :key="post.id" :to="`/blog/${post.slug}`" class="group border border-gray-200 hover:shadow-md transition-shadow">
        <div class="aspect-video bg-gray-100 overflow-hidden">
          <img v-if="post.featured_image" :src="imageUrl(post.featured_image)" :alt="post.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
        </div>
        <div class="p-4">
          <h2 class="font-semibold text-gray-800 group-hover:text-primary-600 line-clamp-2 mb-2">{{ post.title }}</h2>
          <p v-if="post.excerpt" class="text-sm text-gray-500 line-clamp-2 mb-3">{{ post.excerpt }}</p>
          <div class="flex items-center justify-between text-xs text-gray-400">
            <span>{{ post.published_at ? formatDate(post.published_at) : '' }}</span>
            <span>{{ post.reading_time }} min</span>
          </div>
        </div>
      </NuxtLink>
    </div>

    <div v-if="lastPage > 1" class="flex justify-center gap-2 pt-8">
      <UiAtomsButton variant="outline" size="sm" :disabled="page <= 1" @click="fetchPosts(page - 1)">← Prethodna</UiAtomsButton>
      <span class="flex items-center text-sm text-gray-500">{{ page }} / {{ lastPage }}</span>
      <UiAtomsButton variant="outline" size="sm" :disabled="page >= lastPage" @click="fetchPosts(page + 1)">Sledeća →</UiAtomsButton>
    </div>
  </div>
</template>
