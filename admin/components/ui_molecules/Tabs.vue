<script setup lang="ts">
interface Tab {
  key: string
  label: string
}

interface Props {
  tabs: Tab[]
  modelValue: string
}

defineProps<Props>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <div>
    <div class="border-b border-gray-200">
      <nav class="flex gap-6 -mb-px">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          class="py-3 text-sm font-medium border-b-2 transition-colors"
          :class="modelValue === tab.key
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
          @click="emit('update:modelValue', tab.key)"
        >
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <div class="py-4">
      <slot :active="modelValue" />
    </div>
  </div>
</template>
