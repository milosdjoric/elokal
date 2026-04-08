<script setup lang="ts">
const { currencies, activeCurrency, setCurrency, fetchCurrencies } = useCurrency()

onMounted(fetchCurrencies)
</script>

<template>
  <div v-if="currencies.length > 1" class="relative">
    <select
      :value="activeCurrency?.code || ''"
      class="appearance-none bg-transparent text-xs text-gray-600 border border-gray-300 rounded px-2 py-1 pr-6 cursor-pointer focus:outline-none focus:ring-1 focus:ring-primary-500"
      @change="setCurrency(($event.target as HTMLSelectElement).value)"
    >
      <option v-for="c in currencies" :key="c.id" :value="c.code">
        {{ c.code }} ({{ c.symbol }})
      </option>
    </select>
    <svg class="absolute right-1.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
    </svg>
  </div>
</template>
