<script setup lang="ts">
interface Props {
  modelValue?: string | number
  value: string | number
  label?: string
  name: string
  disabled?: boolean
  id?: string
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
}>()

const radioId = computed(() => props.id || `radio-${Math.random().toString(36).slice(2, 9)}`)
</script>

<template>
  <label :for="radioId" class="inline-flex items-center gap-2 cursor-pointer" :class="{ 'opacity-50 cursor-not-allowed': disabled }">
    <input
      :id="radioId"
      type="radio"
      :name="name"
      :value="value"
      :checked="modelValue === value"
      :disabled="disabled"
      class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-500"
      @change="emit('update:modelValue', value)"
    />
    <span v-if="label" class="text-sm text-gray-700">{{ label }}</span>
  </label>
</template>
