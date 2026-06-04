<script setup lang="ts">
import type { Review, ReviewStats, PaginatedResponse } from '~/types'

const props = defineProps<{
  productId: number
}>()

const { get } = useApi()
const authStore = useAuthStore()

const reviews = ref<Review[]>([])
const stats = ref<ReviewStats>({ average_rating: 0, total_reviews: 0, distribution: {} })
const loading = ref(true)
const sort = ref('newest')
const page = ref(1)
const lastPage = ref(1)

// Review forma
const showForm = ref(false)
const formRating = ref(5)
const formTitle = ref('')
const formContent = ref('')
const formLoading = ref(false)
const formError = ref('')
const formSuccess = ref(false)

const headers = computed(() => {
  const h: Record<string, string> = { Accept: 'application/json' }
  if (authStore.token) h.Authorization = `Bearer ${authStore.token}`
  return h
})

async function fetchReviews(p = 1) {
  loading.value = true
  try {
    const { apiBase } = useApi()
    const data = await $fetch<PaginatedResponse<Review> & { stats: ReviewStats }>(
      `${apiBase}/v1/products/${props.productId}/reviews?sort=${sort.value}&page=${p}`,
      { headers: { Accept: 'application/json' } },
    )
    reviews.value = data.data
    stats.value = data.stats
    page.value = data.meta.current_page
    lastPage.value = data.meta.last_page
  }
  catch { /* silent */ }
  finally { loading.value = false }
}

function handleSort(newSort: string) {
  sort.value = newSort
  page.value = 1
  fetchReviews()
}

async function submitReview() {
  formLoading.value = true
  formError.value = ''
  try {
    const { apiBase } = useApi()
    await $fetch(`${apiBase}/v1/products/${props.productId}/reviews`, {
      method: 'POST',
      headers: { ...headers.value, 'Content-Type': 'application/json' },
      body: JSON.stringify({
        rating: formRating.value,
        title: formTitle.value || undefined,
        content: formContent.value,
      }),
    })
    formSuccess.value = true
    showForm.value = false
    formRating.value = 5
    formTitle.value = ''
    formContent.value = ''
    fetchReviews()
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    formError.value = err.data?.message || 'Greška pri slanju recenzije.'
  }
  finally { formLoading.value = false }
}

async function voteHelpful(reviewId: number, helpful: boolean) {
  if (!authStore.isLoggedIn) return
  try {
    const { apiBase } = useApi()
    const data = await $fetch<{ helpful_count: number; not_helpful_count: number }>(
      `${apiBase}/v1/reviews/${reviewId}/helpful`,
      {
        method: 'POST',
        headers: { ...headers.value, 'Content-Type': 'application/json' },
        body: JSON.stringify({ helpful }),
      },
    )
    const review = reviews.value.find(r => r.id === reviewId)
    if (review) {
      review.helpful_count = data.helpful_count
      review.not_helpful_count = data.not_helpful_count
    }
  }
  catch { /* silent */ }
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('sr-Latn-RS', {
    day: '2-digit', month: '2-digit', year: 'numeric',
  })
}

function ratingPercentage(rating: number): number {
  if (stats.value.total_reviews === 0) return 0
  return Math.round(((stats.value.distribution[rating] || 0) / stats.value.total_reviews) * 100)
}

onMounted(() => fetchReviews())
</script>

<template>
  <div>
    <!-- Stats + Write button -->
    <div class="flex flex-col sm:flex-row gap-8 mb-8">
      <!-- Rating overview -->
      <div class="flex-shrink-0 text-center sm:text-left">
        <div class="text-5xl font-bold text-gray-800">{{ stats.average_rating || '—' }}</div>
        <div class="flex items-center justify-center sm:justify-start gap-0.5 my-2">
          <Icon
            v-for="i in 5" :key="i"
            name="lucide:star"
            class="w-5 h-5"
            :class="i <= Math.round(stats.average_rating) ? 'text-accent-500' : 'text-gray-200'"
            :style="i <= Math.round(stats.average_rating) ? 'fill: currentColor' : ''"
          />
        </div>
        <p class="text-sm text-gray-500">{{ stats.total_reviews }} {{ stats.total_reviews === 1 ? 'recenzija' : 'recenzija' }}</p>
      </div>

      <!-- Distribution bars -->
      <div class="flex-1 space-y-1.5">
        <div v-for="i in [5, 4, 3, 2, 1]" :key="i" class="flex items-center gap-2 text-sm">
          <span class="w-3 text-gray-500">{{ i }}</span>
          <Icon name="lucide:star" class="w-4 h-4 text-accent-500" style="fill: currentColor" />
          <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-accent-500 rounded-full" :style="{ width: `${ratingPercentage(i)}%` }" />
          </div>
          <span class="w-8 text-right text-gray-400">{{ stats.distribution[i] || 0 }}</span>
        </div>
      </div>
    </div>

    <!-- Write review button -->
    <div class="mb-6">
      <div v-if="formSuccess" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm mb-4">
        Hvala! Vaša recenzija je poslata i čeka odobrenje.
      </div>
      <UiAtomsButton v-if="authStore.isLoggedIn && !showForm" variant="outline" @click="showForm = true">
        Napiši recenziju
      </UiAtomsButton>
      <p v-else-if="!authStore.isLoggedIn" class="text-sm text-gray-500">
        <NuxtLink to="/nalog/login" class="text-primary-600 hover:underline">Prijavite se</NuxtLink> da biste ostavili recenziju.
      </p>
    </div>

    <!-- Review form -->
    <div v-if="showForm" class="border border-gray-200 p-6 mb-8">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Vaša recenzija</h3>
      <div v-if="formError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm mb-4">{{ formError }}</div>
      <form @submit.prevent="submitReview" class="space-y-4">
        <!-- Rating stars -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Ocena</label>
          <div class="flex gap-1">
            <button
              v-for="i in 5" :key="i"
              type="button"
              @click="formRating = i"
            >
              <Icon
                name="lucide:star"
                class="w-8 h-8 transition-colors cursor-pointer"
                :class="i <= formRating ? 'text-accent-500' : 'text-gray-200 hover:text-accent-300'"
                :style="i <= formRating ? 'fill: currentColor' : ''"
              />
            </button>
          </div>
        </div>
        <UiAtomsInput v-model="formTitle" label="Naslov (opciono)" placeholder="Ukratko opišite utisak" />
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Vaš komentar <span class="text-red-500">*</span></label>
          <textarea
            v-model="formContent"
            rows="4"
            required
            minlength="10"
            placeholder="Opišite svoje iskustvo sa proizvodom..."
            class="w-full px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
        </div>
        <div class="flex gap-3">
          <UiAtomsButton type="submit" :loading="formLoading">Pošalji recenziju</UiAtomsButton>
          <UiAtomsButton variant="secondary" @click="showForm = false">Otkaži</UiAtomsButton>
        </div>
      </form>
    </div>

    <!-- Sort + Reviews list -->
    <div v-if="stats.total_reviews > 0" class="mb-4 flex items-center justify-between">
      <span class="text-sm text-gray-500">{{ stats.total_reviews }} recenzija</span>
      <select
        :value="sort"
        class="text-sm border border-gray-300 px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500"
        @change="handleSort(($event.target as HTMLSelectElement).value)"
      >
        <option value="newest">Najnovije</option>
        <option value="oldest">Najstarije</option>
        <option value="highest">Najviša ocena</option>
        <option value="lowest">Najniža ocena</option>
      </select>
    </div>

    <div v-if="loading" class="space-y-4">
      <UiAtomsSkeleton v-for="i in 3" :key="i" height="120px" />
    </div>

    <UiMoleculesEmptyState
      v-else-if="reviews.length === 0 && !formSuccess"
      variant="reviews"
      size="sm"
      title="Još nema recenzija"
      description="Budite prvi koji ostavlja recenziju za ovaj proizvod."
    />

    <div v-else class="space-y-6">
      <div v-for="review in reviews" :key="review.id" class="border-b border-gray-100 pb-6">
        <!-- Header -->
        <div class="flex items-start justify-between mb-2">
          <div>
            <div class="flex items-center gap-2">
              <div class="flex gap-0.5">
                <Icon
                  v-for="i in 5" :key="i"
                  name="lucide:star"
                  class="w-4 h-4"
                  :class="i <= review.rating ? 'text-accent-500' : 'text-gray-200'"
                  :style="i <= review.rating ? 'fill: currentColor' : ''"
                />
              </div>
              <span v-if="review.is_verified_purchase" class="text-xs font-medium text-green-600 bg-green-50 px-1.5 py-0.5 rounded">
                Verifikovan kupac
              </span>
            </div>
            <p v-if="review.title" class="font-semibold text-gray-800 mt-1">{{ review.title }}</p>
          </div>
          <span class="text-xs text-gray-400 flex-shrink-0">{{ formatDate(review.created_at) }}</span>
        </div>

        <!-- Content -->
        <p class="text-sm text-gray-600 mb-2">{{ review.content }}</p>
        <p class="text-xs text-gray-400 mb-3">— {{ review.user.name }}</p>

        <!-- Admin reply -->
        <div v-if="review.admin_reply" class="bg-gray-50 border-l-4 border-primary-400 px-4 py-3 mb-3">
          <p class="text-xs font-semibold text-gray-500 mb-1">Odgovor prodavnice</p>
          <p class="text-sm text-gray-600">{{ review.admin_reply }}</p>
        </div>

        <!-- Helpful -->
        <div class="flex items-center gap-4 text-xs text-gray-400">
          <span>Da li vam je ovo pomoglo?</span>
          <button
            class="hover:text-gray-600 flex items-center gap-1"
            :class="{ 'opacity-50 cursor-not-allowed': !authStore.isLoggedIn }"
            @click="voteHelpful(review.id, true)"
          >
            <Icon name="lucide:thumbs-up" class="w-3.5 h-3.5" />
            Da ({{ review.helpful_count }})
          </button>
          <button
            class="hover:text-gray-600 flex items-center gap-1"
            :class="{ 'opacity-50 cursor-not-allowed': !authStore.isLoggedIn }"
            @click="voteHelpful(review.id, false)"
          >
            <Icon name="lucide:thumbs-down" class="w-3.5 h-3.5" />
            Ne ({{ review.not_helpful_count }})
          </button>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="lastPage > 1" class="flex justify-center gap-2 pt-6">
      <UiAtomsButton variant="outline" size="sm" :disabled="page <= 1" @click="fetchReviews(page - 1)">
        ← Prethodna
      </UiAtomsButton>
      <span class="flex items-center text-sm text-gray-500">{{ page }} / {{ lastPage }}</span>
      <UiAtomsButton variant="outline" size="sm" :disabled="page >= lastPage" @click="fetchReviews(page + 1)">
        Sledeća →
      </UiAtomsButton>
    </div>
  </div>
</template>
