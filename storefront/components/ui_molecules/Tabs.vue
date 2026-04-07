<script setup lang="ts">
interface Tab { key: string; label: string }
defineProps<{ tabs: Tab[]; modelValue: string }>()
const emit = defineEmits<{ 'update:modelValue': [value: string] }>()
</script>

<template>
  <div>
    <!-- Desktop tabs -->
    <div class="hidden md:block border-b border-gray-200 mb-4">
      <nav class="flex gap-6 -mb-px">
        <button v-for="tab in tabs" :key="tab.key" class="py-3 text-sm font-medium border-b-2 transition-colors" :class="modelValue === tab.key ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" @click="emit('update:modelValue', tab.key)">
          {{ tab.label }}
        </button>
      </nav>
    </div>
    <!-- Mobile accordion -->
    <div class="md:hidden space-y-2">
      <button v-for="tab in tabs" :key="tab.key" class="w-full text-left px-4 py-3 text-sm font-medium border" :class="modelValue === tab.key ? 'bg-primary-50 border-primary-200 text-primary-700' : 'border-gray-200 text-gray-700'" @click="emit('update:modelValue', tab.key)">
        {{ tab.label }}
      </button>
    </div>
    <div class="py-2"><slot /></div>
  </div>
</template>
