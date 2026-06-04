<script setup lang="ts">
interface Item { key: string; title: string }
defineProps<{ items: Item[] }>()
const openKeys = ref<Set<string>>(new Set())
function toggle(key: string) { openKeys.value.has(key) ? openKeys.value.delete(key) : openKeys.value.add(key) }
</script>

<template>
  <div class="divide-y divide-gray-200 border border-gray-200">
    <div v-for="item in items" :key="item.key">
      <button class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="toggle(item.key)">
        {{ item.title }}
        <Icon name="lucide:chevron-down" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': openKeys.has(item.key) }" />
      </button>
      <div v-if="openKeys.has(item.key)" class="px-4 pb-3 text-sm text-gray-600"><slot :name="item.key" /></div>
    </div>
  </div>
</template>
