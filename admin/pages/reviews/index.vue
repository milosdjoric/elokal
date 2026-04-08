<script setup lang="ts">
import type { Review, PaginatedResponse } from '~/types'

const { get, patch, post, getErrorMessage } = useApi()
const { success, error: toastError } = useToast()

const reviews = ref<Review[]>([])
const loading = ref(true)
const statusFilter = ref('')
const ratingFilter = ref('')
const page = ref(1)
const totalPages = ref(1)
const total = ref(0)

// Reply modal
const replyModal = ref(false)
const replyTarget = ref<Review | null>(null)
const replyText = ref('')
const replyLoading = ref(false)

// Bulk
const selected = ref<Set<number>>(new Set())
const bulkLoading = ref(false)

const statusLabels: Record<string, string> = {
  pending: 'Na čekanju',
  approved: 'Odobrena',
  rejected: 'Odbijena',
}

const statusVariants: Record<string, string> = {
  pending: 'warning',
  approved: 'success',
  rejected: 'danger',
}

async function fetchReviews() {
  loading.value = true
  try {
    const params: Record<string, string | number> = { page: page.value, per_page: 15 }
    if (statusFilter.value) params.status = statusFilter.value
    if (ratingFilter.value) params.rating = ratingFilter.value

    const data = await get<PaginatedResponse<Review>>('/admin/reviews', params)
    reviews.value = data.data
    totalPages.value = data.meta.last_page
    total.value = data.meta.total
    page.value = data.meta.current_page
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { loading.value = false }
}

async function approve(id: number) {
  try {
    await patch(`/admin/reviews/${id}/approve`)
    success('Recenzija odobrena.')
    fetchReviews()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

async function reject(id: number) {
  try {
    await patch(`/admin/reviews/${id}/reject`)
    success('Recenzija odbijena.')
    fetchReviews()
  }
  catch (e) { toastError(getErrorMessage(e)) }
}

function openReply(review: Review) {
  replyTarget.value = review
  replyText.value = review.admin_reply || ''
  replyModal.value = true
}

async function submitReply() {
  if (!replyTarget.value) return
  replyLoading.value = true
  try {
    await post(`/admin/reviews/${replyTarget.value.id}/reply`, { admin_reply: replyText.value })
    success('Odgovor sačuvan.')
    replyModal.value = false
    fetchReviews()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { replyLoading.value = false }
}

// Bulk actions
function toggleSelect(id: number) {
  if (selected.value.has(id)) selected.value.delete(id)
  else selected.value.add(id)
}

function toggleSelectAll() {
  if (selected.value.size === reviews.value.length) {
    selected.value.clear()
  } else {
    reviews.value.forEach(r => selected.value.add(r.id))
  }
}

async function bulkAction(action: 'approve' | 'reject') {
  bulkLoading.value = true
  try {
    for (const id of selected.value) {
      await patch(`/admin/reviews/${id}/${action}`)
    }
    success(`${selected.value.size} recenzija ${action === 'approve' ? 'odobreno' : 'odbijeno'}.`)
    selected.value.clear()
    fetchReviews()
  }
  catch (e) { toastError(getErrorMessage(e)) }
  finally { bulkLoading.value = false }
}

function handlePageChange(p: number) {
  page.value = p
  fetchReviews()
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('sr-Latn-RS', {
    day: '2-digit', month: '2-digit', year: 'numeric',
  })
}

onMounted(fetchReviews)
</script>

<template>
  <div>
    <LayoutBreadcrumbs :items="[{ label: 'Recenzije' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Recenzije</h1>
      <span class="text-sm text-gray-400">{{ total }} ukupno</span>
    </div>

    <!-- Filters -->
    <div class="flex items-center gap-4 mb-4">
      <select
        :value="statusFilter"
        class="px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
        @change="statusFilter = ($event.target as HTMLSelectElement).value; page = 1; fetchReviews()"
      >
        <option value="">Svi statusi</option>
        <option value="pending">Na čekanju</option>
        <option value="approved">Odobrene</option>
        <option value="rejected">Odbijene</option>
      </select>

      <select
        :value="ratingFilter"
        class="px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
        @change="ratingFilter = ($event.target as HTMLSelectElement).value; page = 1; fetchReviews()"
      >
        <option value="">Sve ocene</option>
        <option v-for="i in 5" :key="i" :value="i">{{ i }} ★</option>
      </select>

      <!-- Bulk actions -->
      <div v-if="selected.size > 0" class="flex items-center gap-2 ml-auto">
        <span class="text-sm text-gray-500">{{ selected.size }} selektovano</span>
        <UiAtomsButton size="sm" :loading="bulkLoading" @click="bulkAction('approve')">Odobri sve</UiAtomsButton>
        <UiAtomsButton size="sm" variant="danger" :loading="bulkLoading" @click="bulkAction('reject')">Odbij sve</UiAtomsButton>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <UiAtomsSkeleton v-for="i in 5" :key="i" height="100px" />
    </div>

    <!-- Empty -->
    <div v-else-if="reviews.length === 0" class="text-center py-16 text-gray-400">
      Nema recenzija.
    </div>

    <!-- Reviews list -->
    <div v-else class="space-y-3">
      <div v-for="review in reviews" :key="review.id" class="border border-gray-200 bg-white p-5">
        <div class="flex items-start gap-4">
          <!-- Checkbox -->
          <input
            type="checkbox"
            :checked="selected.has(review.id)"
            class="mt-1 w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
            @change="toggleSelect(review.id)"
          />

          <div class="flex-1 min-w-0">
            <!-- Header -->
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-3">
                <!-- Stars -->
                <div class="flex gap-0.5">
                  <svg
                    v-for="i in 5" :key="i"
                    class="w-4 h-4"
                    :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-200'"
                    fill="currentColor" viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
                <UiAtomsBadge :variant="statusVariants[review.status] as 'success' | 'warning' | 'danger'">
                  {{ statusLabels[review.status] }}
                </UiAtomsBadge>
                <span v-if="review.is_verified_purchase" class="text-xs text-green-600 font-medium">✓ Verifikovan</span>
              </div>
              <span class="text-xs text-gray-400">{{ formatDate(review.created_at) }}</span>
            </div>

            <!-- Product + User -->
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
              <span class="font-medium text-gray-700">{{ review.user.name }}</span>
              <span>za</span>
              <NuxtLink v-if="review.product" :to="`/products/${review.product.id}/edit`" class="text-primary-600 hover:underline">
                {{ review.product.name }}
              </NuxtLink>
            </div>

            <!-- Content -->
            <p v-if="review.title" class="font-semibold text-gray-800 text-sm">{{ review.title }}</p>
            <p class="text-sm text-gray-600 mt-1">{{ review.content }}</p>

            <!-- Admin reply -->
            <div v-if="review.admin_reply" class="mt-3 bg-gray-50 border-l-4 border-primary-400 px-4 py-2 text-sm text-gray-600">
              <span class="text-xs font-semibold text-gray-500">Vaš odgovor:</span> {{ review.admin_reply }}
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 mt-3">
              <UiAtomsButton v-if="review.status !== 'approved'" size="sm" @click="approve(review.id)">Odobri</UiAtomsButton>
              <UiAtomsButton v-if="review.status !== 'rejected'" size="sm" variant="danger" @click="reject(review.id)">Odbij</UiAtomsButton>
              <UiAtomsButton size="sm" variant="ghost" @click="openReply(review)">
                {{ review.admin_reply ? 'Izmeni odgovor' : 'Odgovori' }}
              </UiAtomsButton>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-center gap-2 pt-4">
      <UiAtomsButton variant="secondary" size="sm" :disabled="page <= 1" @click="handlePageChange(page - 1)">
        ← Prethodna
      </UiAtomsButton>
      <span class="flex items-center text-sm text-gray-500">{{ page }} / {{ totalPages }}</span>
      <UiAtomsButton variant="secondary" size="sm" :disabled="page >= totalPages" @click="handlePageChange(page + 1)">
        Sledeća →
      </UiAtomsButton>
    </div>

    <!-- Reply modal -->
    <UiMoleculesModal v-model="replyModal" title="Odgovor na recenziju">
      <div v-if="replyTarget" class="mb-4 text-sm">
        <p class="text-gray-500">{{ replyTarget.user.name }} — {{ replyTarget.rating }}★</p>
        <p class="text-gray-700 mt-1">{{ replyTarget.content }}</p>
      </div>
      <textarea
        v-model="replyText"
        rows="4"
        placeholder="Vaš odgovor..."
        class="w-full px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
      />
      <template #footer>
        <UiAtomsButton variant="secondary" @click="replyModal = false">Otkaži</UiAtomsButton>
        <UiAtomsButton :loading="replyLoading" @click="submitReply">Sačuvaj odgovor</UiAtomsButton>
      </template>
    </UiMoleculesModal>

    <!-- Select all checkbox helper -->
    <div v-if="reviews.length > 0" class="flex items-center gap-2 mt-4 text-sm text-gray-400">
      <input
        type="checkbox"
        :checked="selected.size === reviews.length && reviews.length > 0"
        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
        @change="toggleSelectAll"
      />
      Selektuj sve na stranici
    </div>
  </div>
</template>
