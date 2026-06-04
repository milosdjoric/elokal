<script setup lang="ts">
interface Props {
  width?: string
  height?: string
  variant?: 'brand' | 'neutral' | 'dark'
  rounded?: boolean
}

withDefaults(defineProps<Props>(), {
  width: '100%',
  height: '1rem',
  variant: 'brand',
  rounded: false,
})
</script>

<template>
  <div
    class="skeleton-shimmer"
    :class="[
      variant === 'brand' && 'bg-primary-50',
      variant === 'neutral' && 'bg-gray-200',
      variant === 'dark' && 'bg-gray-800',
      rounded && 'rounded',
    ]"
    :style="{ width, height }"
  />
</template>

<style scoped>
.skeleton-shimmer {
  position: relative;
  overflow: hidden;
}

.skeleton-shimmer::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(255, 255, 255, 0.5) 50%,
    transparent 100%
  );
  transform: translateX(-100%);
  animation: skeleton-shimmer 1.6s ease-in-out infinite;
}

@keyframes skeleton-shimmer {
  100% { transform: translateX(100%); }
}

@media (prefers-reduced-motion: reduce) {
  .skeleton-shimmer::after {
    animation: none;
  }

  .skeleton-shimmer {
    animation: pulse 2s ease-in-out infinite;
    opacity: 0.7;
  }
}
</style>
