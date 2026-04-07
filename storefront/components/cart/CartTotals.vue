<script setup lang="ts">
const { total } = useCart()
</script>

<template>
  <div class="border border-gray-200 p-6 space-y-3">
    <h3 class="text-lg font-bold mb-4">Pregled narudžbine</h3>

    <div class="flex justify-between text-sm">
      <span class="text-gray-600">Subtotal</span>
      <span class="font-medium">{{ total }}</span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-gray-600">Dostava</span>
      <span class="text-gray-400">Računa se na checkout-u</span>
    </div>
    <hr class="my-2" />
    <div class="flex justify-between text-lg font-bold">
      <span>Ukupno</span>
      <span>{{ total }}</span>
    </div>

    <UiAtomsButton class="w-full mt-4" size="lg">Nastavi ka plaćanju</UiAtomsButton>
  </div>
</template>
