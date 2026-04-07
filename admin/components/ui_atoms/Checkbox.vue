<script setup lang="ts">
interface Props {
  modelValue?: boolean
  label?: string
  disabled?: boolean
  id?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const checkboxId = computed(() => props.id || `checkbox-${Math.random().toString(36).slice(2, 9)}`)
</script>

<template>
  <label :for="checkboxId" class="inline-flex items-center gap-2 cursor-pointer" :class="{ 'opacity-50 cursor-not-allowed': disabled }">
    <input
      :id="checkboxId"
      type="checkbox"
      :checked="modelValue"
      :disabled="disabled"
      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
      @change="emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
    />
    <span v-if="label" class="text-sm text-gray-700">{{ label }}</span>
  </label>
</template>
