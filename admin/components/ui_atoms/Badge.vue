<script setup lang="ts">
interface Props {
  variant?: 'success' | 'warning' | 'danger' | 'info' | 'neutral'
}

withDefaults(defineProps<Props>(), {
  variant: 'neutral',
})

const variantClasses: Record<string, string> = {
  success: 'bg-green-100 text-green-800',
  warning: 'bg-yellow-100 text-yellow-800',
  danger: 'bg-red-100 text-red-800',
  info: 'bg-primary-100 text-primary-800',
  neutral: 'bg-gray-100 text-gray-800',
}
</script>

<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="variantClasses[variant]"
  >
    <slot />
  </span>
</template>
