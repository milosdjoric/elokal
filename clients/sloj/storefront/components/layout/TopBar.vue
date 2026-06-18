<script setup lang="ts">
const { hasFreeShippingThreshold, formattedThreshold } = useShippingConfig()
</script>

<template>
  <div class="bg-ink-900 text-ply-200 text-[11px] tracking-[0.08em] py-2 hidden md:block">
    <div class="container-site flex justify-between items-center">
      <span class="lowercase">slojevit nameštaj — pažljivo birano</span>
      <div class="flex items-center gap-6">
        <span v-if="hasFreeShippingThreshold" class="flex items-center gap-2 lowercase">
          besplatna dostava preko <span class="tabular-nums font-medium text-paper">{{ formattedThreshold }} RSD</span>
        </span>
        <LayoutLanguagePicker />
        <LayoutCurrencySwitcher />
      </div>
    </div>
  </div>
</template>
