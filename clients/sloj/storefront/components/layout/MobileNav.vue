<script setup lang="ts">
const route = useRoute()
const { count: cartCount } = useCart()
const wishlistStore = useWishlistStore()
const wishlistCount = computed(() => wishlistStore.count)

interface NavItem {
  label: string
  to: string
  icon: string
}

const items: NavItem[] = [
  { label: 'Početna', to: '/', icon: 'lucide:home' },
  { label: 'Kategorije', to: '/kategorije', icon: 'lucide:layout-grid' },
  { label: 'Pretraga', to: '/pretraga', icon: 'lucide:search' },
  { label: 'Lista', to: '/nalog/wishlist', icon: 'lucide:heart' },
  { label: 'Korpa', to: '/korpa', icon: 'lucide:shopping-bag' },
]

function isActive(path: string): boolean {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}
</script>

<template>
  <nav
    class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200
           pb-[env(safe-area-inset-bottom)]"
    aria-label="Glavna mobilna navigacija"
  >
    <div class="grid grid-cols-5">
      <NuxtLink
        v-for="item in items"
        :key="item.to"
        :to="item.to"
        class="relative flex flex-col items-center justify-center gap-0.5 py-2 text-[11px] transition-colors"
        :class="isActive(item.to) ? 'text-primary-700 font-semibold' : 'text-gray-500 font-medium hover:text-gray-700'"
      >
        <Icon :name="item.icon" class="w-5 h-5" :stroke-width="isActive(item.to) ? 2 : 1.75" />

        <!-- Wishlist dot indicator -->
        <span
          v-if="item.icon === 'lucide:heart' && wishlistCount > 0"
          class="absolute top-1.5 right-[28%] w-1.5 h-1.5 bg-rose-600 rounded-full"
          aria-hidden="true"
        />

        <!-- Cart count badge -->
        <span
          v-if="item.icon === 'lucide:shopping-bag' && cartCount > 0"
          class="absolute top-1 right-[20%] min-w-[16px] h-4 px-1 bg-accent-500 text-gray-900 text-[10px] font-bold flex items-center justify-center tabular-nums"
        >
          {{ cartCount > 99 ? '99+' : cartCount }}
        </span>

        <span>{{ item.label }}</span>

        <span
          v-if="isActive(item.to)"
          class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-[2px] bg-primary-700"
          aria-hidden="true"
        />
      </NuxtLink>
    </div>
  </nav>
</template>
