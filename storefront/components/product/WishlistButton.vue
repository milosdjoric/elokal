<script setup lang="ts">
const props = defineProps<{
  productId: number
  size?: 'sm' | 'md'
}>()

const wishlistStore = useWishlistStore()
const isActive = computed(() => wishlistStore.isInWishlist(props.productId))

const { isEnabled } = useFeature()
const featureActive = computed(() => isEnabled('feature_wishlist', true))

function toggle() {
  wishlistStore.toggle(props.productId)
}
</script>

<template>
  <button
    v-if="featureActive"
    type="button"
    :aria-label="isActive ? 'Ukloni iz liste želja' : 'Dodaj u listu želja'"
    class="group transition-colors"
    :class="size === 'sm' ? 'p-1' : 'p-2'"
    @click.prevent.stop="toggle"
  >
    <svg
      :class="[
        size === 'sm' ? 'w-5 h-5' : 'w-6 h-6',
        isActive ? 'text-red-500 fill-current' : 'text-gray-400 group-hover:text-red-400',
      ]"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"
        :fill="isActive ? 'currentColor' : 'none'"
      />
    </svg>
  </button>
</template>
