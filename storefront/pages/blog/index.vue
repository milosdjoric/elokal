<script setup lang="ts">
import type { Post, BlogSidebar, PaginatedResponse } from '~/types'

const { get } = useApi()

const posts = ref<Post[]>([])
const sidebar = ref<BlogSidebar | null>(null)
const loading = ref(true)
const page = ref(1)
const lastPage = ref(1)
const search = ref('')

async function fetchPosts(p = 1) {
  loading.value = true
  try {
    const params: Record<string, string | number> = { page: p, per_page: 9 }
    if (search.value) params.search = search.value

    const data = await get<PaginatedResponse<Post>>('/v1/blog', params)
    posts.value = data.data
    page.value = data.meta.current_page
    lastPage.value = data.meta.last_page
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

async function fetchSidebar() {
  try {
    sidebar.value = await get<BlogSidebar>('/v1/blog/sidebar')
  }
  catch { /* silent */ }
}

function handleSearch() {
  page.value = 1
  fetchPosts()
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('sr-Latn-RS', {
    day: 'numeric', month: 'long', year: 'numeric',
  })
}

function imageUrl(path: string | null): string {
  if (!path) return ''
  if (path.startsWith('http')) return path
  const { apiBase } = useApi()
  return `${apiBase.replace('/api', '')}/storage/${path}`
}

onMounted(() => {
  fetchPosts()
  fetchSidebar()
})

useHead({ title: 'Blog — eLokal' })
useSeoMeta({ description: 'Najnoviji članci, saveti i novosti.' })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <LayoutBreadcrumbs :items="[{ label: 'Blog' }]" />

    <h1 class="text-3xl font-bold text-gray-800 mb-8">Blog</h1>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
      <!-- Posts grid -->
      <div class="lg:col-span-3">
        <!-- Search -->
        <form class="mb-6 flex gap-2" @submit.prevent="handleSearch">
          <input
            v-model="search"
            type="text"
            placeholder="Pretraži članke..."
            class="flex-1 px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
          <UiAtomsButton type="submit">Traži</UiAtomsButton>
        </form>

        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <UiAtomsSkeleton v-for="i in 6" :key="i" height="280px" />
        </div>

        <div v-else-if="posts.length === 0" class="text-center py-16 text-gray-400">
          Nema objavljenih članaka.
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <NuxtLink
            v-for="post in posts"
            :key="post.id"
            :to="`/blog/${post.slug}`"
            class="group border border-gray-200 hover:shadow-md transition-shadow"
          >
            <div class="aspect-video bg-gray-100 overflow-hidden">
              <img
                v-if="post.featured_image"
                :src="imageUrl(post.featured_image)"
                :alt="post.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
            </div>
            <div class="p-4">
              <div v-if="post.categories?.length" class="flex gap-2 mb-2">
                <span v-for="cat in post.categories" :key="cat.id" class="text-xs text-primary-600 font-medium">
                  {{ cat.name }}
                </span>
              </div>
              <h2 class="font-semibold text-gray-800 group-hover:text-primary-600 line-clamp-2 mb-2">
                {{ post.title }}
              </h2>
              <p v-if="post.excerpt" class="text-sm text-gray-500 line-clamp-2 mb-3">{{ post.excerpt }}</p>
              <div class="flex items-center justify-between text-xs text-gray-400">
                <span>{{ post.published_at ? formatDate(post.published_at) : '' }}</span>
                <span>{{ post.reading_time }} min čitanja</span>
              </div>
            </div>
          </NuxtLink>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="flex justify-center gap-2 pt-8">
          <UiAtomsButton variant="outline" size="sm" :disabled="page <= 1" @click="fetchPosts(page - 1)">← Prethodna</UiAtomsButton>
          <span class="flex items-center text-sm text-gray-500">{{ page }} / {{ lastPage }}</span>
          <UiAtomsButton variant="outline" size="sm" :disabled="page >= lastPage" @click="fetchPosts(page + 1)">Sledeća →</UiAtomsButton>
        </div>
      </div>

      <!-- Sidebar -->
      <aside v-if="sidebar" class="space-y-6">
        <!-- Recent posts -->
        <div v-if="sidebar.recent_posts.length" class="border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 mb-3">Najnovije</h3>
          <div class="space-y-3">
            <NuxtLink
              v-for="rp in sidebar.recent_posts"
              :key="rp.id"
              :to="`/blog/${rp.slug}`"
              class="flex gap-3 group"
            >
              <img v-if="rp.featured_image" :src="imageUrl(rp.featured_image)" class="w-16 h-12 object-cover flex-shrink-0" alt="" />
              <div v-else class="w-16 h-12 bg-gray-100 flex-shrink-0" />
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800 group-hover:text-primary-600 line-clamp-2">{{ rp.title }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(rp.published_at) }}</p>
              </div>
            </NuxtLink>
          </div>
        </div>

        <!-- Categories -->
        <div v-if="sidebar.categories.length" class="border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 mb-3">Kategorije</h3>
          <div class="space-y-1">
            <NuxtLink
              v-for="cat in sidebar.categories"
              :key="cat.id"
              :to="`/blog/kategorija/${cat.slug}`"
              class="flex justify-between text-sm text-gray-600 hover:text-primary-600 py-1"
            >
              <span>{{ cat.name }}</span>
              <span class="text-gray-400">{{ cat.posts_count }}</span>
            </NuxtLink>
          </div>
        </div>

        <!-- Tags -->
        <div v-if="sidebar.tags.length" class="border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-800 mb-3">Tagovi</h3>
          <div class="flex flex-wrap gap-2">
            <NuxtLink
              v-for="tag in sidebar.tags"
              :key="tag.id"
              :to="`/blog/tag/${tag.slug}`"
              class="text-xs bg-gray-100 text-gray-600 hover:bg-primary-50 hover:text-primary-600 px-2.5 py-1 rounded-full"
            >
              {{ tag.name }}
            </NuxtLink>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>
