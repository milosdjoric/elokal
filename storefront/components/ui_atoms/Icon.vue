<script setup lang="ts">
interface Props {
  size?: 'sm' | 'md' | 'lg'
}

withDefaults(defineProps<Props>(), { size: 'md' })

const sizeClasses: Record<string, string> = {
  sm: 'w-4 h-4',
  md: 'w-5 h-5',
  lg: 'w-6 h-6',
}
</script>

<template>
  <span class="inline-flex items-center justify-center flex-shrink-0" :class="sizeClasses[size]">
    <slot />
  </span>
</template>
