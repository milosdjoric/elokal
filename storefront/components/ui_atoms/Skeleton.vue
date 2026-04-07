<script setup lang="ts">
interface Props {
  width?: string
  height?: string
}

withDefaults(defineProps<Props>(), {
  width: '100%',
  height: '1rem',
})
</script>

<template>
  <div class="animate-pulse bg-gray-200" :style="{ width, height }" />
</template>
