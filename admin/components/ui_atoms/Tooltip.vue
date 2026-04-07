<script setup lang="ts">
interface Props {
  text: string
  position?: 'top' | 'bottom' | 'left' | 'right'
}

withDefaults(defineProps<Props>(), {
  position: 'top',
})

const show = ref(false)

const positionClasses: Record<string, string> = {
  top: 'bottom-full left-1/2 -translate-x-1/2 mb-2',
  bottom: 'top-full left-1/2 -translate-x-1/2 mt-2',
  left: 'right-full top-1/2 -translate-y-1/2 mr-2',
  right: 'left-full top-1/2 -translate-y-1/2 ml-2',
}
</script>

<template>
  <div
    class="relative inline-flex"
    @mouseenter="show = true"
    @mouseleave="show = false"
  >
    <slot />

    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="show"
        class="absolute z-50 px-2.5 py-1.5 text-xs font-medium text-white bg-gray-900 rounded-md shadow-sm whitespace-nowrap pointer-events-none"
        :class="positionClasses[position]"
      >
        {{ text }}
      </div>
    </Transition>
  </div>
</template>
