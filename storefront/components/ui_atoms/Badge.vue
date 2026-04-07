<script setup lang="ts">
interface Props {
  variant?: 'sale' | 'new' | 'out' | 'info'
  size?: 'sm' | 'md'
}

withDefaults(defineProps<Props>(), {
  variant: 'info',
  size: 'sm',
})

const variantClasses: Record<string, string> = {
  sale: 'bg-red-500 text-white',
  new: 'bg-green-500 text-white',
  out: 'bg-gray-500 text-white',
  info: 'bg-primary-600 text-white',
}

const sizeClasses: Record<string, string> = {
  sm: 'px-2 py-0.5 text-[10px]',
  md: 'px-3 py-1 text-xs',
}
</script>

<template>
  <span class="inline-flex items-center font-bold uppercase tracking-wide" :class="[variantClasses[variant], sizeClasses[size]]">
    <slot />
  </span>
</template>
