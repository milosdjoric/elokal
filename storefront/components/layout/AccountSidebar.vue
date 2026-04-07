<script setup lang="ts">
const route = useRoute()
const { logout, user } = useAuth()

const items = [
  { label: 'Pregled', to: '/account', icon: 'home' },
  { label: 'Profil', to: '/account/profile', icon: 'user' },
  { label: 'Adrese', to: '/account/addresses', icon: 'map' },
  { label: 'Narudžbine', to: '/account/orders', icon: 'package' },
]

function isActive(path: string): boolean {
  if (path === '/account') return route.path === '/account'
  return route.path.startsWith(path)
}
</script>

<template>
  <aside class="w-full lg:w-56 flex-shrink-0">
    <div class="mb-4">
      <p class="text-sm font-semibold text-gray-800">{{ user?.name }}</p>
      <p class="text-xs text-gray-400">{{ user?.email }}</p>
    </div>

    <nav class="space-y-1">
      <NuxtLink
        v-for="item in items"
        :key="item.to"
        :to="item.to"
        class="block px-3 py-2 text-sm font-medium transition-colors"
        :class="isActive(item.to) ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50'"
      >
        {{ item.label }}
      </NuxtLink>
      <button class="block w-full text-left px-3 py-2 text-sm font-medium text-red-500 hover:bg-red-50" @click="logout">
        Odjavi se
      </button>
    </nav>
  </aside>
</template>
