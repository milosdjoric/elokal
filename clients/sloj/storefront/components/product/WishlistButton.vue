<script setup lang="ts">
const props = defineProps<{
  productId: number
  size?: 'sm' | 'md'
}>()

const wishlistStore = useWishlistStore()
const isActive = computed(() => wishlistStore.isInWishlist(props.productId))

function toggle() {
  wishlistStore.toggle(props.productId)
}
</script>

<template>
  <button
    type="button"
    :aria-label="isActive ? 'Ukloni iz liste želja' : 'Dodaj u listu želja'"
    class="group transition-colors"
    :class="size === 'sm' ? 'p-1' : 'p-2'"
    @click.prevent.stop="toggle"
  >
    <Icon
      :name="isActive ? 'lucide:heart' : 'lucide:heart'"
      :class="[
        size === 'sm' ? 'w-5 h-5' : 'w-6 h-6',
        isActive ? 'text-rose-600' : 'text-gray-400 group-hover:text-rose-500',
      ]"
      :style="isActive ? 'fill: currentColor' : ''"
    />
  </button>
</template>
