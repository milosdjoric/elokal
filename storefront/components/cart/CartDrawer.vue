<script setup lang="ts">
defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()
const { items, total, isEmpty } = useCart()
const cartStore = useCartStore()
const { isFreeShipping, remainingForFree, freeProgress, hasFreeShippingThreshold, formattedThreshold } = useShippingConfig()

function close() { emit('update:modelValue', false) }
</script>

<template>
  <Teleport to="body">
    <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0" leave-active-class="transition duration-200" leave-to-class="opacity-0">
      <div v-if="modelValue" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/50" @click="close" />
        <div class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-xl flex flex-col">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-lg font-bold">Korpa</h2>
            <button class="p-1 text-gray-400 hover:text-gray-600" @click="close">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <!-- Items -->
          <div class="flex-1 overflow-y-auto px-6 py-4">
            <p v-if="isEmpty" class="text-gray-500 text-center py-8">Korpa je prazna.</p>
            <div v-else class="space-y-4">
              <CartItem v-for="item in items" :key="item.product.id" :item="item" />
            </div>
          </div>

          <!-- Footer -->
          <div v-if="!isEmpty" class="border-t px-6 py-4 space-y-3">
            <!-- Free shipping progress -->
            <div v-if="hasFreeShippingThreshold" class="text-sm">
              <div v-if="isFreeShipping(cartStore.total)" class="text-green-600 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                Besplatna dostava!
              </div>
              <div v-else>
                <p class="text-gray-500 mb-1">
                  Još <span class="font-semibold text-primary-600">{{ remainingForFree(cartStore.total).toLocaleString('sr-RS') }} RSD</span> do besplatne dostave
                </p>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                  <div class="bg-primary-500 h-1.5 rounded-full transition-all" :style="{ width: `${freeProgress(cartStore.total)}%` }" />
                </div>
              </div>
            </div>

            <div class="flex items-center justify-between font-bold text-lg">
              <span>Ukupno:</span>
              <span>{{ total }}</span>
            </div>
            <NuxtLink to="/cart" class="block" @click="close">
              <UiAtomsButton class="w-full" size="lg">Pregledaj korpu</UiAtomsButton>
            </NuxtLink>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
