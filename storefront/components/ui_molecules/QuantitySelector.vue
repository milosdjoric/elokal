<script setup lang="ts">
interface Props {
  modelValue: number
  min?: number
  max?: number
}

const props = withDefaults(defineProps<Props>(), { min: 1, max: 99 })
const emit = defineEmits<{ 'update:modelValue': [value: number] }>()

function decrement() { if (props.modelValue > props.min) emit('update:modelValue', props.modelValue - 1) }
function increment() { if (props.modelValue < props.max) emit('update:modelValue', props.modelValue + 1) }
</script>

<template>
  <div class="inline-flex items-center border border-gray-300">
    <button type="button" :disabled="modelValue <= min" class="px-3 py-2 text-gray-600 hover:bg-gray-100 disabled:opacity-40" @click="decrement">−</button>
    <span class="w-12 text-center text-sm font-medium border-x border-gray-300 py-2">{{ modelValue }}</span>
    <button type="button" :disabled="modelValue >= max" class="px-3 py-2 text-gray-600 hover:bg-gray-100 disabled:opacity-40" @click="increment">+</button>
  </div>
</template>
