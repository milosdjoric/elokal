<script setup lang="ts">
interface Props {
  itemCount: number
  autoplay?: boolean
  interval?: number
}

const props = withDefaults(defineProps<Props>(), {
  autoplay: false,
  interval: 5000,
})

const current = ref(0)
let timer: ReturnType<typeof setInterval>

function next() {
  current.value = (current.value + 1) % props.itemCount
}

function prev() {
  current.value = (current.value - 1 + props.itemCount) % props.itemCount
}

function goTo(index: number) {
  current.value = index
}

onMounted(() => {
  if (props.autoplay && props.itemCount > 1) {
    timer = setInterval(next, props.interval)
  }
})

onUnmounted(() => clearInterval(timer))

// Swipe support
let touchStartX = 0
function onTouchStart(e: TouchEvent) { touchStartX = e.touches[0].clientX }
function onTouchEnd(e: TouchEvent) {
  const diff = touchStartX - e.changedTouches[0].clientX
  if (Math.abs(diff) > 50) {
    diff > 0 ? next() : prev()
  }
}
</script>

<template>
  <div
    class="relative overflow-hidden"
    @touchstart="onTouchStart"
    @touchend="onTouchEnd"
  >
    <div class="flex transition-transform duration-300 ease-in-out" :style="{ transform: `translateX(-${current * 100}%)` }">
      <slot :current="current" />
    </div>

    <!-- Arrows -->
    <button
      v-if="itemCount > 1"
      class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 flex items-center justify-center hover:bg-white shadow"
      aria-label="Prethodno"
      @click="prev"
    >
      <Icon name="lucide:chevron-left" class="w-5 h-5" />
    </button>
    <button
      v-if="itemCount > 1"
      class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 flex items-center justify-center hover:bg-white shadow"
      aria-label="Sledeće"
      @click="next"
    >
      <Icon name="lucide:chevron-right" class="w-5 h-5" />
    </button>

    <!-- Dots -->
    <div v-if="itemCount > 1" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2">
      <button
        v-for="i in itemCount"
        :key="i"
        class="w-2.5 h-2.5 rounded-full transition-colors"
        :class="current === i - 1 ? 'bg-primary-600' : 'bg-white/60'"
        @click="goTo(i - 1)"
      />
    </div>
  </div>
</template>
