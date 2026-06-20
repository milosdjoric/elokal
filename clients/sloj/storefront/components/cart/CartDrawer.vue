<script setup lang="ts">
import type { Product } from '~/types'

const cartStore = useCartStore()
const { items, isEmpty } = useCart()
const { isFreeShipping, remainingForFree, freeProgress, hasFreeShippingThreshold } = useShippingConfig()
const { get } = useApi()

const isOpen = computed(() => cartStore.drawerOpen)
const lastAdded = computed(() => items.value.find(i => i.product.id === cartStore.lastAddedId) || null)
const restItems = computed(() => {
  const la = lastAdded.value
  if (!la) return items.value
  return items.value.filter(i => i.product.id !== la.product.id)
})

const crossSell = ref<Product[]>([])
const crossSellLoading = ref(false)
let crossSellFetchedFor: number | null = null

async function fetchCrossSell() {
  if (!cartStore.lastAddedId || crossSellFetchedFor === cartStore.lastAddedId) return
  crossSellLoading.value = true
  try {
    // Featured proizvodi kao cross-sell (dedicated endpoint nije implementiran).
    const data = await get<{ data: Product[] }>('/v1/products', { featured: 1, per_page: 6 })
    const cartIds = new Set(items.value.map(i => i.product.id))
    crossSell.value = (data.data || []).filter(p => !cartIds.has(p.id)).slice(0, 4)
    crossSellFetchedFor = cartStore.lastAddedId
  }
  catch { crossSell.value = [] }
  finally { crossSellLoading.value = false }
}

watch(isOpen, (open) => {
  if (open) fetchCrossSell()
})

function close() {
  cartStore.closeDrawer()
}

function primaryImage(product: Product): string | null {
  const img = product.images?.find(i => i.is_primary) || product.images?.[0]
  return img ? resolveImageUrl(img.image_path) : null
}

function addCrossSell(product: Product) {
  cartStore.addItem(product)
}

// ESC za zatvaranje + scroll lock dok je drawer otvoren
watch(isOpen, (open) => {
  if (!import.meta.client) return
  if (open) {
    document.body.style.overflow = 'hidden'
    document.addEventListener('keydown', onKeydown)
  } else {
    document.body.style.overflow = ''
    document.removeEventListener('keydown', onKeydown)
  }
})

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') close()
}
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0"
    >
      <div v-if="isOpen" class="fixed inset-0 z-50">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-ink-900/40" @click="close" />

        <!-- Panel: side drawer (md+) / bottom sheet (mobile) -->
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="md:translate-x-full translate-y-full"
          enter-to-class="translate-x-0 translate-y-0"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="translate-x-0 translate-y-0"
          leave-to-class="md:translate-x-full translate-y-full"
          appear
        >
          <aside
            v-if="isOpen"
            class="absolute
                   left-0 right-0 bottom-0 max-h-[88vh]
                   md:left-auto md:right-0 md:top-0 md:bottom-0 md:w-full md:max-w-md md:max-h-none
                   bg-paper flex flex-col"
          >
            <!-- Header -->
            <header class="flex items-center justify-between px-6 py-5 border-b border-ink-100 flex-shrink-0">
              <h2 class="text-[16px] font-medium text-ink-800">
                {{ lastAdded ? 'Dodato u korpu' : 'Vaša korpa' }}
              </h2>
              <button
                type="button"
                aria-label="Zatvori korpu"
                class="text-ink-500 hover:text-terra-600 transition-colors"
                @click="close"
              >
                <Icon name="lucide:x" class="w-5 h-5" />
              </button>
            </header>

            <!-- Just-added highlight -->
            <div v-if="lastAdded" class="px-6 py-5 bg-ink-50 border-b border-ink-100 flex gap-4 flex-shrink-0">
              <img
                v-if="primaryImage(lastAdded.product)"
                :src="primaryImage(lastAdded.product)!"
                :alt="lastAdded.product.name"
                class="w-20 h-20 object-cover flex-shrink-0"
              />
              <div v-else class="w-20 h-20 bg-placeholder flex-shrink-0" />
              <div class="flex-1 min-w-0">
                <p class="text-[12px] text-terra-600 mb-1">Upravo dodato</p>
                <p class="text-[14px] text-ink-800 line-clamp-2 leading-snug">{{ lastAdded.product.name }}</p>
                <p class="text-[14px] text-ink-800 tabular-nums mt-1.5">
                  {{ formatPrice(lastAdded.product.effective_price) }}
                </p>
              </div>
            </div>

            <!-- Free shipping progress -->
            <div v-if="hasFreeShippingThreshold && !isEmpty" class="px-6 py-4 border-b border-ink-100 flex-shrink-0">
              <div v-if="isFreeShipping(cartStore.total)" class="flex items-center gap-2 text-[13px] text-success">
                <Icon name="lucide:check" class="w-4 h-4" />
                Ostvarili ste besplatnu dostavu
              </div>
              <div v-else>
                <p class="text-[12px] text-ink-500 mb-2 leading-relaxed">
                  Još <span class="text-ink-800 tabular-nums">{{ formatPrice(remainingForFree(cartStore.total)) }}</span> do besplatne dostave
                </p>
                <div class="w-full bg-ink-100 h-[2px]">
                  <div class="bg-terra-500 h-[2px] transition-all duration-300" :style="{ width: `${freeProgress(cartStore.total)}%` }" />
                </div>
              </div>
            </div>

            <!-- Items list -->
            <div class="flex-1 overflow-y-auto px-6 py-4">
              <div v-if="isEmpty" class="py-16 text-center">
                <p class="text-[22px] font-light text-ink-800 tracking-[-0.01em]">Korpa je prazna</p>
                <p class="text-[14px] text-ink-500 mt-3 max-w-xs mx-auto">Pogledajte kolekciju i dodajte komade.</p>
                <NuxtLink
                  to="/proizvodi"
                  class="inline-block mt-6 px-6 py-3 text-[14px] border border-ink-800 text-ink-800 hover:bg-ink-800 hover:text-paper transition-colors"
                  @click="close"
                >
                  Pogledaj proizvode
                </NuxtLink>
              </div>

              <div v-else>
                <p v-if="lastAdded && items.length > 1" class="text-[12px] text-ink-500 mb-4">
                  Ostatak korpe ({{ items.length - 1 }})
                </p>
                <div class="space-y-4">
                  <CartItem
                    v-for="item in restItems"
                    :key="item.product.id"
                    :item="item"
                  />
                </div>
              </div>
            </div>

            <!-- Cross-sell -->
            <div v-if="!isEmpty && crossSell.length > 0" class="border-t border-ink-100 px-6 py-4 flex-shrink-0">
              <p class="text-[12px] text-ink-500 mb-3">Često kupljeno zajedno</p>
              <div class="flex gap-3 overflow-x-auto pb-1 snap-x">
                <div
                  v-for="product in crossSell"
                  :key="product.id"
                  class="flex-shrink-0 w-36 snap-start"
                >
                  <NuxtLink :to="`/proizvodi/${product.slug}`" class="block aspect-square bg-surface overflow-hidden" @click="close">
                    <img v-if="primaryImage(product)" :src="primaryImage(product)!" :alt="product.name" class="w-full h-full object-cover" />
                  </NuxtLink>
                  <div class="pt-2">
                    <p class="text-[12px] text-ink-800 line-clamp-2 leading-snug min-h-[32px]">{{ product.name }}</p>
                    <p class="text-[12px] text-ink-800 tabular-nums mt-1">{{ formatPrice(product.effective_price) }}</p>
                    <button
                      type="button"
                      class="mt-2 text-[12px] text-ink-700 hover:text-terra-600 border-b border-ink-300 hover:border-terra-500 pb-0.5 transition-colors"
                      @click="addCrossSell(product)"
                    >
                      + Dodaj
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer: dual CTA -->
            <footer v-if="!isEmpty" class="border-t border-ink-100 px-6 py-5 space-y-4 flex-shrink-0 bg-paper">
              <div class="flex items-baseline justify-between">
                <span class="text-[14px] text-ink-500">Međuzbir</span>
                <span class="text-ink-800 text-[20px] font-light tabular-nums">{{ formatPrice(cartStore.total) }}</span>
              </div>
              <p class="text-[12px] text-ink-400 leading-relaxed">Dostava i porez se obračunavaju u sledećem koraku.</p>
              <div class="grid grid-cols-2 gap-3">
                <button
                  type="button"
                  class="py-4 text-[14px] border border-ink-200 text-ink-700 hover:border-ink-800 hover:text-ink-800 transition-colors"
                  @click="close"
                >
                  Nastavi
                </button>
                <NuxtLink
                  to="/korpa"
                  class="flex items-center justify-center gap-2 py-4 text-[14px] bg-ink-800 text-paper hover:bg-terra-600 transition-colors"
                  @click="close"
                >
                  Idi na kasu →
                </NuxtLink>
              </div>
            </footer>
          </aside>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
