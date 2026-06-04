<script setup lang="ts">
interface Props {
  rating: number
  max?: number
  count?: number
}

withDefaults(defineProps<Props>(), { max: 5 })
</script>

<template>
  <div class="flex items-center gap-1">
    <Icon
      v-for="i in max" :key="i"
      name="lucide:star"
      class="w-4 h-4"
      :class="i <= Math.round(rating) ? 'text-accent-500' : 'text-gray-200'"
      :style="i <= Math.round(rating) ? 'fill: currentColor' : ''"
    />
    <span v-if="count !== undefined" class="text-xs text-gray-400 ml-1">({{ count }})</span>
  </div>
</template>
