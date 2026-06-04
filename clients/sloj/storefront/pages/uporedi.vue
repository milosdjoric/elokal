<script setup lang="ts">
const store = useCompareStore()

function primaryImage(product: { images?: Array<{ image_path: string; is_primary: boolean }> }): string | null {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  if (!img) return null
  return resolveImageUrl(img.image_path)
}

useHead({ title: 'Poređenje proizvoda — sloj kolektiv' })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <LayoutBreadcrumbs :items="[{ label: 'Poređenje proizvoda' }]" />

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Poređenje proizvoda</h1>
      <UiAtomsButton v-if="store.count > 0" variant="secondary" size="sm" @click="store.clear()">Obriši sve</UiAtomsButton>
    </div>

    <UiMoleculesEmptyState
      v-if="store.count === 0"
      variant="comparison"
      size="lg"
      title="Nema proizvoda za poređenje"
      description="Klikom na ikonicu vage na kartici proizvoda dodajte do 4 proizvoda za upoređivanje cena, specifikacija i ocena."
    >
      <NuxtLink to="/proizvodi">
        <UiAtomsButton variant="outline">Pogledaj proizvode</UiAtomsButton>
      </NuxtLink>
    </UiMoleculesEmptyState>

    <div v-else class="overflow-x-auto">
      <table class="w-full border-collapse">
        <!-- Images -->
        <tr class="border-b border-gray-200">
          <td class="py-4 pr-4 text-sm font-medium text-gray-500 align-top w-32" />
          <td v-for="product in store.items" :key="product.id" class="py-4 px-4 text-center min-w-[200px]">
            <NuxtLink :to="`/proizvodi/${product.slug}`">
              <img
                v-if="primaryImage(product)"
                :src="primaryImage(product)!"
                :alt="product.name"
                class="w-32 h-32 object-cover mx-auto mb-3"
              />
              <div v-else class="w-32 h-32 bg-gray-100 mx-auto mb-3" />
            </NuxtLink>
            <button class="text-xs text-red-500 hover:text-red-700" @click="store.remove(product.id)">Ukloni</button>
          </td>
        </tr>

        <!-- Name -->
        <tr class="border-b border-gray-100">
          <td class="py-3 pr-4 text-sm font-medium text-gray-500">Naziv</td>
          <td v-for="product in store.items" :key="product.id" class="py-3 px-4">
            <NuxtLink :to="`/proizvodi/${product.slug}`" class="text-sm font-semibold text-gray-800 hover:text-primary-600">
              {{ product.name }}
            </NuxtLink>
          </td>
        </tr>

        <!-- Price -->
        <tr class="border-b border-gray-100">
          <td class="py-3 pr-4 text-sm font-medium text-gray-500">Cena</td>
          <td v-for="product in store.items" :key="product.id" class="py-3 px-4">
            <span class="text-sm font-bold text-primary-600 tabular-nums">{{ formatPrice(product.effective_price) }}</span>
            <span v-if="product.is_on_sale" class="text-xs text-gray-400 line-through ml-2 tabular-nums">{{ formatPrice(product.price) }}</span>
          </td>
        </tr>

        <!-- Stock -->
        <tr class="border-b border-gray-100">
          <td class="py-3 pr-4 text-sm font-medium text-gray-500">Stanje</td>
          <td v-for="product in store.items" :key="product.id" class="py-3 px-4">
            <span :class="product.stock_quantity > 0 ? 'text-green-600' : 'text-red-500'" class="text-sm font-medium">
              {{ product.stock_quantity > 0 ? 'Na stanju' : 'Nema' }}
            </span>
          </td>
        </tr>

        <!-- SKU -->
        <tr class="border-b border-gray-100">
          <td class="py-3 pr-4 text-sm font-medium text-gray-500">SKU</td>
          <td v-for="product in store.items" :key="product.id" class="py-3 px-4 text-sm text-gray-600">
            {{ product.sku || '—' }}
          </td>
        </tr>

        <!-- Description -->
        <tr class="border-b border-gray-100">
          <td class="py-3 pr-4 text-sm font-medium text-gray-500">Opis</td>
          <td v-for="product in store.items" :key="product.id" class="py-3 px-4 text-sm text-gray-600">
            {{ product.short_description || '—' }}
          </td>
        </tr>

        <!-- Add to cart -->
        <tr>
          <td class="py-4 pr-4" />
          <td v-for="product in store.items" :key="product.id" class="py-4 px-4 text-center">
            <NuxtLink :to="`/proizvodi/${product.slug}`">
              <UiAtomsButton size="sm">Pogledaj</UiAtomsButton>
            </NuxtLink>
          </td>
        </tr>
      </table>
    </div>
  </div>
</template>
