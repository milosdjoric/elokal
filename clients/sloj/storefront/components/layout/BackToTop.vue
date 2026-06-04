<script setup lang="ts">
const visible = ref(false)

function checkScroll() {
  visible.value = window.scrollY > 400
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => window.addEventListener('scroll', checkScroll))
onUnmounted(() => window.removeEventListener('scroll', checkScroll))
</script>

<template>
  <Transition
    enter-active-class="transition duration-200"
    enter-from-class="opacity-0 translate-y-4"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0 translate-y-4"
  >
    <button
      v-if="visible"
      class="fixed bottom-20 md:bottom-8 right-4 z-40 w-10 h-10 bg-primary-700 text-white flex items-center justify-center shadow-lg hover:bg-primary-800 transition-colors"
      aria-label="Vrati na vrh"
      @click="scrollToTop"
    >
      <Icon name="lucide:chevron-up" class="w-5 h-5" />
    </button>
  </Transition>
</template>
