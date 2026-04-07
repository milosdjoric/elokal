<script setup lang="ts">
interface AccordionItem {
  key: string
  title: string
}

defineProps<{
  items: AccordionItem[]
}>()

const openKeys = ref<Set<string>>(new Set())

function toggle(key: string) {
  if (openKeys.value.has(key)) {
    openKeys.value.delete(key)
  }
  else {
    openKeys.value.add(key)
  }
}
</script>

<template>
  <div class="divide-y divide-gray-200 border border-gray-200 rounded-lg">
    <div v-for="item in items" :key="item.key">
      <button
        class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
        @click="toggle(item.key)"
      >
        {{ item.title }}
        <svg
          class="w-4 h-4 transition-transform duration-200"
          :class="{ 'rotate-180': openKeys.has(item.key) }"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
      </button>

      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 max-h-0"
        enter-to-class="opacity-100 max-h-96"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 max-h-96"
        leave-to-class="opacity-0 max-h-0"
      >
        <div v-if="openKeys.has(item.key)" class="px-4 pb-3 text-sm text-gray-600 overflow-hidden">
          <slot :name="item.key" />
        </div>
      </Transition>
    </div>
  </div>
</template>
