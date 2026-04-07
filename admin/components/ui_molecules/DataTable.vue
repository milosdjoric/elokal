<script setup lang="ts">
interface Column {
  key: string
  label: string
  sortable?: boolean
  width?: string
}

interface Props {
  columns: Column[]
  rows: Record<string, unknown>[]
  loading?: boolean
  sortKey?: string
  sortDir?: 'asc' | 'desc'
  page?: number
  totalPages?: number
  total?: number
  emptyMessage?: string
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  emptyMessage: 'Nema podataka.',
})

const emit = defineEmits<{
  sort: [key: string]
  pageChange: [page: number]
}>()

function handleSort(col: Column) {
  if (col.sortable) emit('sort', col.key)
}
</script>

<template>
  <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              :style="col.width ? { width: col.width } : {}"
              :class="{ 'cursor-pointer hover:text-gray-700 select-none': col.sortable }"
              @click="handleSort(col)"
            >
              <div class="flex items-center gap-1">
                {{ col.label }}
                <template v-if="col.sortable && sortKey === col.key">
                  <svg v-if="sortDir === 'asc'" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" />
                  </svg>
                  <svg v-else class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" />
                  </svg>
                </template>
              </div>
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          <!-- Loading -->
          <template v-if="loading">
            <tr v-for="i in 5" :key="i">
              <td v-for="col in columns" :key="col.key" class="px-4 py-3">
                <UiAtomsSkeleton height="1.25rem" />
              </td>
            </tr>
          </template>

          <!-- Empty -->
          <tr v-else-if="rows.length === 0">
            <td :colspan="columns.length" class="px-4 py-12 text-center text-gray-500">
              {{ emptyMessage }}
            </td>
          </tr>

          <!-- Rows -->
          <tr
            v-else
            v-for="(row, index) in rows"
            :key="index"
            class="hover:bg-gray-50 transition-colors"
          >
            <td v-for="col in columns" :key="col.key" class="px-4 py-3">
              <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                {{ row[col.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages && totalPages > 1" class="px-4 py-3 border-t border-gray-200 flex items-center justify-between">
      <p class="text-sm text-gray-500">
        Ukupno: <span class="font-medium">{{ total }}</span>
      </p>
      <div class="flex items-center gap-1">
        <button
          :disabled="page === 1"
          class="px-3 py-1.5 text-sm rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          @click="emit('pageChange', (page || 1) - 1)"
        >
          Prethodna
        </button>
        <span class="px-3 py-1.5 text-sm text-gray-600">
          {{ page }} / {{ totalPages }}
        </span>
        <button
          :disabled="page === totalPages"
          class="px-3 py-1.5 text-sm rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          @click="emit('pageChange', (page || 1) + 1)"
        >
          Sledeća
        </button>
      </div>
    </div>
  </div>
</template>
