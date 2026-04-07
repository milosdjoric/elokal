<script setup lang="ts">
interface Props {
  modelValue: string
  placeholder?: string
}

withDefaults(defineProps<Props>(), {
  placeholder: 'Pretraži...',
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  search: [value: string]
}>()

function handleSubmit() {
  emit('search', props.modelValue)
}

const props = defineProps<Props>()

function clear() {
  emit('update:modelValue', '')
  emit('search', '')
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="relative">
    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
    </svg>

    <input
      :value="modelValue"
      type="text"
      :placeholder="placeholder"
      class="w-full pl-10 pr-10 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />

    <button
      v-if="modelValue"
      type="button"
      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
      @click="clear"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </form>
</template>
