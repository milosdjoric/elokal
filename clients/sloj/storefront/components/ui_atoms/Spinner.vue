<script setup lang="ts">
// Sloj loader — tanka linija (border), brend ink boja (umesto debelog primary spinnera)
interface Props {
  size?: 'sm' | 'md' | 'lg'
}

withDefaults(defineProps<Props>(), { size: 'md' })

const sizeClasses: Record<string, string> = {
  sm: 'h-4 w-4 border',
  md: 'h-6 w-6 border-2',
  lg: 'h-9 w-9 border-2',
}
</script>

<template>
  <span
    class="inline-block animate-spin rounded-full border-ink-200 border-t-ink-800"
    :class="sizeClasses[size]"
    role="status"
    aria-label="Učitavanje"
  />
</template>
