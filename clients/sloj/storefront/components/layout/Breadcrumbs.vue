<script setup lang="ts">
interface BreadcrumbItem {
  label: string
  to?: string
}

defineProps<{
  items: BreadcrumbItem[]
}>()
</script>

<template>
  <nav class="flex items-center gap-2 text-sm text-gray-500 py-4">
    <NuxtLink to="/" class="hover:text-primary-600">Početna</NuxtLink>

    <template v-for="(item, index) in items" :key="index">
      <Icon name="lucide:chevron-right" class="w-3 h-3 text-gray-300" />

      <NuxtLink v-if="item.to" :to="item.to" class="hover:text-primary-600">
        {{ item.label }}
      </NuxtLink>
      <span v-else class="text-gray-800 font-medium">{{ item.label }}</span>
    </template>
  </nav>
</template>
