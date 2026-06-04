<script setup lang="ts">
const { currencies, activeCurrency, setCurrency, fetchCurrencies } = useCurrency()

onMounted(fetchCurrencies)
</script>

<template>
  <div v-if="currencies.length > 1" class="relative">
    <select
      :value="activeCurrency?.code || ''"
      class="appearance-none bg-transparent text-xs text-white/80 border border-white/30 rounded px-2 py-1 pr-6 cursor-pointer focus:outline-none focus:ring-1 focus:ring-accent-500"
      @change="setCurrency(($event.target as HTMLSelectElement).value)"
    >
      <option v-for="c in currencies" :key="c.id" :value="c.code" class="text-gray-800">
        {{ c.code }} ({{ c.symbol }})
      </option>
    </select>
    <Icon name="lucide:chevron-down" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-3 h-3 text-white/60 pointer-events-none" />
  </div>
</template>
