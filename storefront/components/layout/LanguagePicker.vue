<script setup lang="ts">
const { locale, setLocale, availableLocales } = useI18n()

const labels: Record<string, string> = {
  sr: 'Srpski',
  en: 'English',
}

const flags: Record<string, string> = {
  sr: '🇷🇸',
  en: '🇬🇧',
}

const open = ref(false)

function select(loc: string) {
  setLocale(loc)
  open.value = false
}
</script>

<template>
  <div class="relative">
    <button
      class="flex items-center gap-1.5 text-xs text-gray-300 hover:text-white transition-colors"
      @click="open = !open"
    >
      <span>{{ flags[locale] }}</span>
      <span>{{ labels[locale] }}</span>
      <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
      </svg>
    </button>

    <div
      v-if="open"
      class="absolute top-full right-0 mt-1 bg-white border border-gray-200 shadow-lg z-50 py-1 min-w-[120px]"
      @mouseleave="open = false"
    >
      <button
        v-for="loc in availableLocales"
        :key="loc"
        class="w-full flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
        :class="loc === locale ? 'font-medium bg-gray-50' : ''"
        @click="select(loc)"
      >
        <span>{{ flags[loc] }}</span>
        <span>{{ labels[loc] }}</span>
      </button>
    </div>
  </div>
</template>
