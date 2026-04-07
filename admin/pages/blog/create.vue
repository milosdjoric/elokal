<script setup lang="ts">
import type { BlogCategory, Tag } from '~/types'

const { get, post: apiPost, getErrorMessage, getValidationErrors } = useApi()
const { success, error: toastError } = useToast()
const router = useRouter()

const form = reactive({
  title: '',
  slug: '',
  excerpt: '',
  content: '',
  featured_image: '',
  status: 'draft' as 'draft' | 'published' | 'scheduled',
  published_at: '',
  meta_title: '',
  meta_description: '',
  category_ids: [] as number[],
  tag_ids: [] as number[],
})

const categories = ref<BlogCategory[]>([])
const tags = ref<Tag[]>([])
const loading = ref(false)
const errors = ref<Record<string, string[]>>({})

async function fetchMeta() {
  try {
    const [catData, tagData] = await Promise.all([
      get<{ data: BlogCategory[] }>('/admin/blog-categories'),
      get<{ data: Tag[] }>('/admin/tags'),
    ])
    categories.value = catData.data
    tags.value = tagData.data
  }
  catch { /* silent */ }
}

function autoSlug() {
  if (!form.slug) {
    form.slug = form.title.toLowerCase()
      .replace(/[čć]/g, 'c').replace(/š/g, 's').replace(/ž/g, 'z').replace(/đ/g, 'dj')
      .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
  }
}

async function handleSubmit() {
  loading.value = true
  errors.value = {}
  try {
    autoSlug()
    await apiPost('/admin/posts', form)
    success('Post kreiran.')
    await router.push('/blog')
  }
  catch (e) {
    errors.value = getValidationErrors(e)
    toastError(getErrorMessage(e))
  }
  finally { loading.value = false }
}

function toggleCategory(id: number) {
  const i = form.category_ids.indexOf(id)
  if (i >= 0) form.category_ids.splice(i, 1)
  else form.category_ids.push(id)
}

function toggleTag(id: number) {
  const i = form.tag_ids.indexOf(id)
  if (i >= 0) form.tag_ids.splice(i, 1)
  else form.tag_ids.push(id)
}

onMounted(fetchMeta)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Blog', to: '/blog' }, { label: 'Novi post' }]" />
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Novi post</h1>

    <form @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <!-- Content -->
          <div class="bg-white border border-gray-200 p-6 space-y-4">
            <UiAtomsInput v-model="form.title" label="Naslov" required :error="errors.title?.[0]" @blur="autoSlug" />
            <UiAtomsInput v-model="form.slug" label="Slug" :error="errors.slug?.[0]" />
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Izvod</label>
              <textarea v-model="form.excerpt" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Kratak opis posta..." />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Sadržaj <span class="text-red-500">*</span></label>
              <textarea v-model="form.content" rows="15" required class="w-full px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 font-mono" placeholder="HTML sadržaj posta..." />
            </div>
          </div>

          <!-- SEO -->
          <div class="bg-white border border-gray-200 p-6 space-y-4">
            <h2 class="font-semibold text-gray-800">SEO</h2>
            <UiAtomsInput v-model="form.meta_title" label="Meta title" />
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Meta description</label>
              <textarea v-model="form.meta_description" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Publish -->
          <div class="bg-white border border-gray-200 p-5 space-y-4">
            <h2 class="font-semibold text-gray-800">Objavljivanje</h2>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select v-model="form.status" class="w-full px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="draft">Draft</option>
                <option value="published">Objavi odmah</option>
                <option value="scheduled">Zakaži</option>
              </select>
            </div>
            <UiAtomsInput v-if="form.status === 'scheduled'" v-model="form.published_at" label="Datum objavljivanja" type="text" placeholder="2026-04-10 10:00:00" />
            <UiAtomsInput v-model="form.featured_image" label="Featured image URL" placeholder="/images/post.jpg" />
            <UiAtomsButton type="submit" :loading="loading" class="w-full">Sačuvaj</UiAtomsButton>
          </div>

          <!-- Categories -->
          <div class="bg-white border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Kategorije</h2>
            <div v-if="categories.length === 0" class="text-sm text-gray-400">Nema kategorija.</div>
            <div v-else class="space-y-1 max-h-48 overflow-y-auto">
              <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" :checked="form.category_ids.includes(cat.id)" class="w-4 h-4 text-primary-600 border-gray-300 rounded" @change="toggleCategory(cat.id)" />
                {{ cat.name }}
              </label>
            </div>
          </div>

          <!-- Tags -->
          <div class="bg-white border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Tagovi</h2>
            <div v-if="tags.length === 0" class="text-sm text-gray-400">Nema tagova.</div>
            <div v-else class="flex flex-wrap gap-2">
              <button
                v-for="tag in tags"
                :key="tag.id"
                type="button"
                class="text-xs px-2.5 py-1 rounded-full border transition-colors"
                :class="form.tag_ids.includes(tag.id) ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-600 border-gray-300 hover:border-primary-400'"
                @click="toggleTag(tag.id)"
              >
                {{ tag.name }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
