<script setup lang="ts">
const { items, isEmpty, updateQuantity, removeFromCart } = useCart()

const cartLayout = ref<'standard' | 'table'>('standard')

useHead({ title: 'Korpa — eLokal' })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <LayoutBreadcrumbs :items="[{ label: 'Korpa' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Korpa</h1>
      <div v-if="!isEmpty" class="flex border border-gray-300 divide-x divide-gray-300">
        <button
          type="button"
          class="p-1.5 transition-colors"
          :class="cartLayout === 'standard' ? 'bg-primary-50 text-primary-600' : 'text-gray-400 hover:text-gray-600'"
          title="Standardni prikaz"
          @click="cartLayout = 'standard'"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16"><rect x="1" y="1" width="6" height="6" rx="1" /><rect x="9" y="1" width="6" height="6" rx="1" /><rect x="1" y="9" width="6" height="6" rx="1" /><rect x="9" y="9" width="6" height="6" rx="1" /></svg>
        </button>
        <button
          type="button"
          class="p-1.5 transition-colors"
          :class="cartLayout === 'table' ? 'bg-primary-50 text-primary-600' : 'text-gray-400 hover:text-gray-600'"
          title="Tabelarni prikaz"
          @click="cartLayout = 'table'"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16"><rect x="1" y="1" width="14" height="3" rx="1" /><rect x="1" y="6" width="14" height="3" rx="1" /><rect x="1" y="11" width="14" height="3" rx="1" /></svg>
        </button>
      </div>
    </div>

    <div v-if="isEmpty" class="text-center py-16">
      <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
      </svg>
      <p class="text-gray-500 mb-4">Vaša korpa je prazna.</p>
      <NuxtLink to="/proizvodi">
        <UiAtomsButton>Nastavi kupovinu</UiAtomsButton>
      </NuxtLink>
    </div>

    <!-- Layout A: Standard (sidebar) -->
    <div v-else-if="cartLayout === 'standard'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-4">
        <CartItem v-for="item in items" :key="item.product.id" :item="item" />
      </div>
      <div>
        <CartTotals />
      </div>
    </div>

    <!-- Layout B: Table (full-width) -->
    <div v-else>
      <div class="bg-white border border-gray-200 overflow-x-auto mb-6">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Proizvod</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Cena</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Količina</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Ukupno</th>
              <th class="px-4 py-3 w-12" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="item in items" :key="item.product.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <img
                    v-if="item.product.images?.[0]"
                    :src="resolveImageUrl(item.product.images[0].image_path)"
                    :alt="item.product.name"
                    class="w-14 h-14 object-cover border border-gray-200 flex-shrink-0"
                  />
                  <NuxtLink :to="`/proizvodi/${item.product.slug}`" class="font-medium text-gray-800 hover:text-primary-600">
                    {{ item.product.name }}
                  </NuxtLink>
                </div>
              </td>
              <td class="px-4 py-3 text-right text-gray-600">
                {{ Number(item.product.effective_price).toLocaleString('sr-RS') }} RSD
              </td>
              <td class="px-4 py-3">
                <div class="flex justify-center">
                  <UiMoleculesQuantitySelector v-model="item.quantity" @update:model-value="updateQuantity(item.product.id, $event)" />
                </div>
              </td>
              <td class="px-4 py-3 text-right font-semibold text-gray-800">
                {{ (parseFloat(item.product.effective_price) * item.quantity).toLocaleString('sr-RS', { minimumFractionDigits: 2 }) }} RSD
              </td>
              <td class="px-4 py-3 text-center">
                <button class="text-red-400 hover:text-red-600 text-xs" @click="removeFromCart(item.product.id)">
                  ✕
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="max-w-md ml-auto">
        <CartTotals />
      </div>
    </div>
  </div>
</template>
