<script setup lang="ts">
const { admin, logout } = useAuth()
const authStore = useAuthStore()

onMounted(() => {
  authStore.initAuth()
})
</script>

<template>
  <div class="p-8">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-2xl font-bold text-gray-800">
        Dashboard
      </h1>
      <div class="flex items-center gap-4">
        <span v-if="admin" class="text-sm text-gray-600">
          {{ admin.name }}
        </span>
        <button
          class="text-sm text-red-600 hover:text-red-800"
          @click="logout"
        >
          Odjavi se
        </button>
      </div>
    </div>
    <p class="text-gray-600">
      Dobrodošli u eLokal admin panel.
    </p>
  </div>
</template>
