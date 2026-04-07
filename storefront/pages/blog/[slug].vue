<script setup lang="ts">
import type { Post } from '~/types'

const route = useRoute()
const { get } = useApi()

const post = ref<Post | null>(null)
const related = ref<Post[]>([])
const loading = ref(true)

async function fetchPost() {
  loading.value = true
  try {
    const data = await get<{ data: Post; related: Post[] }>(`/v1/blog/${route.params.slug}`)
    post.value = data.data
    related.value = data.related
  }
  catch {
    navigateTo('/blog')
  }
  finally { loading.value = false }
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

onMounted(fetchPost)

useHead({
  title: computed(() => post.value?.meta_title || (post.value ? `${post.value.title} — Blog — eLokal` : 'Blog — eLokal')),
})

useSeoMeta({
  description: computed(() => post.value?.meta_description || post.value?.excerpt || ''),
  ogTitle: computed(() => post.value?.title || ''),
  ogDescription: computed(() => post.value?.excerpt || ''),
  ogImage: computed(() => post.value?.featured_image ? imageUrl(post.value.featured_image) : ''),
  ogType: 'article',
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-6">
    <div v-if="loading" class="py-20 flex justify-center">
      <UiAtomsSpinner size="lg" />
    </div>

    <template v-else-if="post">
      <LayoutBreadcrumbs :items="[
        { label: 'Blog', to: '/blog' },
        { label: post.title },
      ]" />

      <!-- Header -->
      <header class="mb-8">
        <div v-if="post.categories?.length" class="flex gap-2 mb-3">
          <NuxtLink
            v-for="cat in post.categories"
            :key="cat.id"
            :to="`/blog/kategorija/${cat.slug}`"
            class="text-sm text-primary-600 font-medium hover:underline"
          >
            {{ cat.name }}
          </NuxtLink>
        </div>

        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">{{ post.title }}</h1>

        <div class="flex items-center gap-4 text-sm text-gray-500">
          <span v-if="post.author">{{ post.author.name }}</span>
          <span v-if="post.published_at">{{ formatDate(post.published_at) }}</span>
          <span>{{ post.reading_time }} min čitanja</span>
        </div>
      </header>

      <!-- Featured image -->
      <div v-if="post.featured_image" class="mb-8 aspect-video overflow-hidden">
        <img :src="imageUrl(post.featured_image)" :alt="post.title" class="w-full h-full object-cover" />
      </div>

      <!-- Content -->
      <article class="prose prose-lg max-w-none text-gray-700 mb-8" v-html="post.content?.replace(/\n/g, '<br>')" />

      <!-- Tags -->
      <div v-if="post.tags?.length" class="flex items-center gap-2 mb-8 pt-6 border-t border-gray-200">
        <span class="text-sm text-gray-500">Tagovi:</span>
        <NuxtLink
          v-for="tag in post.tags"
          :key="tag.id"
          :to="`/blog/tag/${tag.slug}`"
          class="text-xs bg-gray-100 text-gray-600 hover:bg-primary-50 hover:text-primary-600 px-2.5 py-1 rounded-full"
        >
          {{ tag.name }}
        </NuxtLink>
      </div>

      <!-- Social share -->
      <div class="mb-12 pt-4 border-t border-gray-200">
        <UiMoleculesSocialShare :url="`http://localhost:3000/blog/${post.slug}`" :title="post.title" />
      </div>

      <!-- Related posts -->
      <section v-if="related.length" class="mb-12">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Povezani članci</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <NuxtLink
            v-for="rp in related"
            :key="rp.id"
            :to="`/blog/${rp.slug}`"
            class="group border border-gray-200 hover:shadow-md transition-shadow"
          >
            <div class="aspect-video bg-gray-100 overflow-hidden">
              <img
                v-if="rp.featured_image"
                :src="imageUrl(rp.featured_image)"
                :alt="rp.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
            </div>
            <div class="p-4">
              <h3 class="font-semibold text-gray-800 group-hover:text-primary-600 line-clamp-2 mb-1">{{ rp.title }}</h3>
              <p v-if="rp.excerpt" class="text-sm text-gray-500 line-clamp-2">{{ rp.excerpt }}</p>
            </div>
          </NuxtLink>
        </div>
      </section>
    </template>
  </div>
</template>
