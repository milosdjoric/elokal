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
  <nav class="flex items-center gap-2 py-4 text-[12px] text-ink-400" aria-label="Breadcrumb">
    <NuxtLink to="/" class="hover:text-ink-700 transition-colors">Početna</NuxtLink>

    <template v-for="(item, index) in items" :key="index">
      <Icon name="lucide:chevron-right" class="w-3 h-3 text-ink-300" />

      <NuxtLink v-if="item.to" :to="item.to" class="hover:text-ink-700 transition-colors">
        {{ item.label }}
      </NuxtLink>
      <span v-else class="text-ink-800">{{ item.label }}</span>
    </template>
  </nav>
</template>
