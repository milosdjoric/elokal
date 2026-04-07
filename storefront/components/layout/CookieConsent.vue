<script setup lang="ts">
const show = ref(false)

onMounted(() => {
  if (!localStorage.getItem('cookie_consent')) {
    show.value = true
  }
})

function accept() {
  localStorage.setItem('cookie_consent', 'true')
  show.value = false
}
</script>

<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 translate-y-4"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0 translate-y-4"
  >
    <div
      v-if="show"
      class="fixed bottom-16 lg:bottom-0 left-0 right-0 z-50 bg-dark text-white p-4"
    >
      <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-300">
          Koristimo kolačiće za poboljšanje vašeg iskustva na sajtu.
          <a href="#" class="underline hover:text-white">Saznaj više</a>
        </p>
        <button
          class="px-6 py-2 bg-primary-600 text-white text-sm font-medium hover:bg-primary-700 transition-colors flex-shrink-0"
          @click="accept"
        >
          Prihvatam
        </button>
      </div>
    </div>
  </Transition>
</template>
