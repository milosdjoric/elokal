<script setup lang="ts">
interface Props {
  modelValue: number
  min?: number
  max?: number
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  min: 0,
  max: 99999,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: number]
}>()

function decrement() {
  if (props.modelValue > props.min) {
    emit('update:modelValue', props.modelValue - 1)
  }
}

function increment() {
  if (props.modelValue < props.max) {
    emit('update:modelValue', props.modelValue + 1)
  }
}

function onInput(e: Event) {
  const val = parseInt((e.target as HTMLInputElement).value) || props.min
  emit('update:modelValue', Math.min(Math.max(val, props.min), props.max))
}
</script>

<template>
  <div class="inline-flex items-center border border-gray-300 rounded-lg">
    <button
      type="button"
      :disabled="disabled || modelValue <= min"
      class="px-3 py-2 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed rounded-l-lg"
      @click="decrement"
    >
      −
    </button>
    <input
      type="number"
      :value="modelValue"
      :min="min"
      :max="max"
      :disabled="disabled"
      class="w-16 text-center text-sm border-x border-gray-300 py-2 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
      @input="onInput"
    />
    <button
      type="button"
      :disabled="disabled || modelValue >= max"
      class="px-3 py-2 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed rounded-r-lg"
      @click="increment"
    >
      +
    </button>
  </div>
</template>
