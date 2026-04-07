<script setup lang="ts">
const { admin, logout } = useAuth()

const searchQuery = ref('')

function handleSearch() {
  if (searchQuery.value.trim()) {
    navigateTo({ path: '/products', query: { search: searchQuery.value } })
    searchQuery.value = ''
  }
}
</script>

<template>
  <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
    <!-- Search -->
    <div class="flex-1 max-w-lg">
      <form @submit.prevent="handleSearch" class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Pretraži proizvode..."
          class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </form>
    </div>

    <!-- Right side -->
    <div class="flex items-center gap-4 ml-4">
      <!-- Notifications bell -->
      <button class="relative p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
      </button>

      <!-- Admin info + logout -->
      <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
        <div class="text-right">
          <p class="text-sm font-medium text-gray-700">{{ admin?.name }}</p>
          <p class="text-xs text-gray-400">{{ admin?.role }}</p>
        </div>

        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
          {{ admin?.name?.charAt(0) }}
        </div>

        <button
          class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-gray-100"
          title="Odjavi se"
          @click="logout"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
          </svg>
        </button>
      </div>
    </div>
  </header>
</template>
