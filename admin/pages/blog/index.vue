<script setup lang="ts">
import type { Post, PaginatedResponse } from '~/types'

const { get, del, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

const posts = ref<Post[]>([])
const loading = ref(true)
const statusFilter = ref('')
const search = ref('')
const page = ref(1)
const totalPages = ref(1)
const total = ref(0)

const deleteModal = ref(false)
const deleteTarget = ref<Post | null>(null)
const deleteLoading = ref(false)

const statusLabels: Record<string, string> = { draft: 'Draft', published: 'Objavljen', scheduled: 'Zakazan' }
const statusVariants: Record<string, string> = { draft: 'neutral', published: 'success', scheduled: 'info' }

async function fetchPosts() {
  loading.value = true
  try {
    const params: Record<string, string | number> = { page: page.value, per_page: 15 }
    if (statusFilter.value) params.status = statusFilter.value
    if (search.value) params.search = search.value

    const data = await get<PaginatedResponse<Post>>('/admin/posts', params)
    posts.value = data.data
    totalPages.value = data.meta.last_page
    total.value = data.meta.total
    page.value = data.meta.current_page
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

function confirmDelete(post: Post) {
  deleteTarget.value = post
  deleteModal.value = true
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    await del(`/admin/posts/${deleteTarget.value.id}`)
    success('Post obrisan.')
    deleteModal.value = false
    fetchPosts()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { deleteLoading.value = false }
}

function formatDate(dateStr: string | null): string {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('sr-Latn-RS', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(fetchPosts)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Blog' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Blog postovi</h1>
      <NuxtLink to="/blog/create">
        <UiAtomsButton>+ Novi post</UiAtomsButton>
      </NuxtLink>
    </div>

    <div class="flex items-center gap-4 mb-4">
      <div class="flex-1 max-w-sm">
        <UiMoleculesSearchBar v-model="search" placeholder="Pretraži po naslovu..." @search="() => { page = 1; fetchPosts() }" />
      </div>
      <select
        :value="statusFilter"
        class="px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
        @change="statusFilter = ($event.target as HTMLSelectElement).value; page = 1; fetchPosts()"
      >
        <option value="">Svi statusi</option>
        <option value="draft">Draft</option>
        <option value="published">Objavljeni</option>
        <option value="scheduled">Zakazani</option>
      </select>
    </div>

    <div v-if="loading" class="space-y-3">
      <UiAtomsSkeleton v-for="i in 5" :key="i" height="60px" />
    </div>

    <div v-else-if="posts.length === 0" class="text-center py-16 text-gray-400">Nema postova.</div>

    <div v-else class="bg-white border border-gray-200 divide-y divide-gray-100">
      <div v-for="post in posts" :key="post.id" class="flex items-center justify-between px-5 py-3">
        <div class="min-w-0 flex-1">
          <NuxtLink :to="`/blog/${post.id}/edit`" class="font-medium text-gray-800 hover:text-primary-600">
            {{ post.title }}
          </NuxtLink>
          <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
            <span>{{ post.author?.name }}</span>
            <span>{{ formatDate(post.published_at) }}</span>
            <UiAtomsBadge :variant="statusVariants[post.status] as 'success' | 'info' | 'neutral'" class="text-[10px]">
              {{ statusLabels[post.status] }}
            </UiAtomsBadge>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <NuxtLink :to="`/blog/${post.id}/edit`">
            <UiAtomsButton variant="ghost" size="sm">Izmeni</UiAtomsButton>
          </NuxtLink>
          <UiAtomsButton variant="ghost" size="sm" @click="confirmDelete(post)">
            <span class="text-red-500">Obriši</span>
          </UiAtomsButton>
        </div>
      </div>
    </div>

    <div v-if="totalPages > 1" class="flex justify-center gap-2 pt-4">
      <UiAtomsButton variant="secondary" size="sm" :disabled="page <= 1" @click="page--; fetchPosts()">← Prethodna</UiAtomsButton>
      <span class="flex items-center text-sm text-gray-500">{{ page }} / {{ totalPages }}</span>
      <UiAtomsButton variant="secondary" size="sm" :disabled="page >= totalPages" @click="page++; fetchPosts()">Sledeća →</UiAtomsButton>
    </div>

    <UiMoleculesConfirmDialog v-model="deleteModal" title="Brisanje posta" :message="`Obrisati '${deleteTarget?.title}'?`" confirm-text="Obriši" :loading="deleteLoading" @confirm="handleDelete" />
  </div>
</template>
