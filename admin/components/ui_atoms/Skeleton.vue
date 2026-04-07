<script setup lang="ts">
interface Props {
  width?: string
  height?: string
  rounded?: 'sm' | 'md' | 'lg' | 'full'
}

withDefaults(defineProps<Props>(), {
  width: '100%',
  height: '1rem',
  rounded: 'md',
})

const roundedClasses: Record<string, string> = {
  sm: 'rounded-sm',
  md: 'rounded-md',
  lg: 'rounded-lg',
  full: 'rounded-full',
}
</script>

<template>
  <div
    class="animate-pulse bg-gray-200"
    :class="roundedClasses[rounded]"
    :style="{ width, height }"
  />
</template>
