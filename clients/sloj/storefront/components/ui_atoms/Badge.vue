<script setup lang="ts">
interface Props {
  variant?: 'sale' | 'new' | 'out' | 'info' | 'success' | 'warning'
  size?: 'sm' | 'md'
}

withDefaults(defineProps<Props>(), {
  variant: 'info',
  size: 'sm',
})

const variantClasses: Record<string, string> = {
  sale: 'bg-paper text-terra-600 border border-terra-500/40',
  new: 'bg-paper text-ink-700 border border-ink-300',
  out: 'bg-paper text-ink-500 border border-ink-200',
  info: 'bg-paper text-ink-700 border border-ink-300',
  success: 'bg-paper text-success border border-success/30',
  warning: 'bg-paper text-warning border border-warning/30',
}

const sizeClasses: Record<string, string> = {
  sm: 'px-2 py-0.5 text-[10px]',
  md: 'px-2.5 py-1 text-[11px]',
}
</script>

<template>
  <span class="inline-flex items-center font-medium uppercase tracking-[0.12em]" :class="[variantClasses[variant], sizeClasses[size]]">
    <slot />
  </span>
</template>
