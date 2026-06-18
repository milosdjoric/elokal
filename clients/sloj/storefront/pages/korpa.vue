<script setup lang="ts">
const { items, isEmpty, updateQuantity, removeFromCart } = useCart()

const cartLayout = ref<'standard' | 'table'>('standard')

useHead({ title: 'Korpa — sloj kolektiv' })
</script>

<template>
  <div class="bg-paper">
    <div class="container-site pt-10 pb-20">
      <LayoutBreadcrumbs :items="[{ label: 'Korpa' }]" />

      <div class="flex items-end justify-between mt-8 mb-12 pb-8 border-b border-ink-100">
        <h1 class="text-[36px] md:text-[52px] font-light text-ink-800 tracking-[-0.02em] leading-[1.05]">
          Korpa
        </h1>
        <div v-if="!isEmpty" class="hidden md:flex items-center gap-6 text-[14px]">
          <button
            type="button"
            class="transition-colors"
            :class="cartLayout === 'standard' ? 'text-ink-800 border-b border-ink-800 pb-0.5' : 'text-ink-400 hover:text-ink-700'"
            aria-label="Standardni prikaz"
            @click="cartLayout = 'standard'"
          >
            Standard
          </button>
          <button
            type="button"
            class="transition-colors"
            :class="cartLayout === 'table' ? 'text-ink-800 border-b border-ink-800 pb-0.5' : 'text-ink-400 hover:text-ink-700'"
            aria-label="Tabelarni prikaz"
            @click="cartLayout = 'table'"
          >
            Tabela
          </button>
        </div>
      </div>

      <UiMoleculesEmptyState
        v-if="isEmpty"
        variant="cart"
        size="lg"
        title="Korpa je prazna."
        description="Pogledajte kolekciju i dodajte komade."
      >
        <NuxtLink
          to="/proizvodi"
          class="inline-block px-7 py-3.5 text-[14px] border border-ink-800 text-ink-800 hover:bg-ink-800 hover:text-paper transition-colors"
        >
          Pogledaj proizvode
        </NuxtLink>
        <NuxtLink to="/" class="text-[14px] text-ink-500 hover:text-ink-800 transition-colors">← Nazad na početnu</NuxtLink>
      </UiMoleculesEmptyState>

      <!-- Layout A: Standard (sidebar) -->
      <div v-else-if="cartLayout === 'standard'" class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        <div class="lg:col-span-8 space-y-6">
          <CartItem v-for="item in items" :key="item.product.id" :item="item" />
        </div>
        <div class="lg:col-span-4">
          <CartTotals />
        </div>
      </div>

      <!-- Layout B: Table (full-width) -->
      <div v-else>
        <div class="overflow-x-auto mb-8">
          <table class="w-full text-[14px]">
            <thead>
              <tr class="border-b border-ink-200">
                <th class="px-4 py-4 text-left text-[11px] uppercase tracking-[0.16em] text-ink-500 font-medium">proizvod</th>
                <th class="px-4 py-4 text-right text-[11px] uppercase tracking-[0.16em] text-ink-500 font-medium">cena</th>
                <th class="px-4 py-4 text-center text-[11px] uppercase tracking-[0.16em] text-ink-500 font-medium">količina</th>
                <th class="px-4 py-4 text-right text-[11px] uppercase tracking-[0.16em] text-ink-500 font-medium">ukupno</th>
                <th class="px-4 py-4 w-12" />
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.product.id" class="border-b border-ink-100">
                <td class="px-4 py-4">
                  <div class="flex items-center gap-4">
                    <img
                      v-if="item.product.images?.[0]"
                      :src="resolveImageUrl(item.product.images[0].image_path)"
                      :alt="item.product.name"
                      class="w-16 h-16 object-cover bg-ink-50 flex-shrink-0"
                    />
                    <NuxtLink :to="`/proizvodi/${item.product.slug}`" class="text-ink-800 hover:text-terra-600 transition-colors">
                      {{ item.product.name }}
                    </NuxtLink>
                  </div>
                </td>
                <td class="px-4 py-4 text-right text-ink-600 tabular-nums">
                  {{ formatPrice(item.product.effective_price) }}
                </td>
                <td class="px-4 py-4">
                  <div class="flex justify-center">
                    <UiMoleculesQuantitySelector v-model="item.quantity" @update:model-value="updateQuantity(item.product.id, $event)" />
                  </div>
                </td>
                <td class="px-4 py-4 text-right text-ink-800 tabular-nums">
                  {{ formatPrice(parseFloat(item.product.effective_price) * item.quantity) }}
                </td>
                <td class="px-4 py-4 text-center">
                  <button class="text-ink-400 hover:text-terra-600 transition-colors" aria-label="Ukloni" @click="removeFromCart(item.product.id)">
                    <Icon name="lucide:x" class="w-4 h-4" />
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
  </div>
</template>
