<script setup lang="ts">
type Variant =
  | 'cart' | 'wishlist' | 'orders' | 'search' | 'comparison' | 'reviews'
  | 'posts' | 'downloads' | 'addresses' | 'loyalty' | 'credits' | 'looks'
  | 'generic'

const props = withDefaults(defineProps<{
  variant?: Variant
  title: string
  description?: string
  size?: 'sm' | 'md' | 'lg'
}>(), {
  variant: 'generic',
  size: 'md',
})

const iconMap: Record<Variant, string> = {
  cart: 'lucide:shopping-bag',
  wishlist: 'lucide:heart',
  orders: 'lucide:package',
  search: 'lucide:search-x',
  comparison: 'lucide:scale',
  reviews: 'lucide:message-square',
  posts: 'lucide:newspaper',
  downloads: 'lucide:download',
  addresses: 'lucide:map-pin',
  loyalty: 'lucide:gift',
  credits: 'lucide:wallet',
  looks: 'lucide:layout-grid',
  generic: 'lucide:inbox',
}

const sizeMap = {
  sm: { container: 'py-8', iconBox: 'w-16 h-16', icon: 'w-7 h-7', title: 'text-[15px]', desc: 'text-[13px]' },
  md: { container: 'py-12', iconBox: 'w-20 h-20', icon: 'w-9 h-9', title: 'text-[17px]', desc: 'text-[14px]' },
  lg: { container: 'py-16', iconBox: 'w-24 h-24', icon: 'w-11 h-11', title: 'text-[20px]', desc: 'text-[15px]' },
}

const sizes = computed(() => sizeMap[props.size])
const icon = computed(() => iconMap[props.variant])
</script>

<template>
  <div class="text-center" :class="sizes.container">
    <!-- Branded icon block: petrol bg + lucide icon, accent corner stripe -->
    <div class="relative inline-block mb-5">
      <div
        class="bg-placeholder flex items-center justify-center text-primary-700"
        :class="sizes.iconBox"
      >
        <Icon :name="icon" :class="sizes.icon" :stroke-width="1.5" />
      </div>
      <!-- Accent corner triangle -->
      <div
        class="absolute -top-1 -right-1 w-3 h-3 bg-accent-500"
        aria-hidden="true"
      />
    </div>

    <h3 class="font-semibold text-gray-900" :class="sizes.title">{{ title }}</h3>
    <p
      v-if="description"
      class="text-gray-500 mt-1.5 max-w-sm mx-auto leading-relaxed"
      :class="sizes.desc"
    >
      {{ description }}
    </p>

    <!-- Optional CTA via slot -->
    <div v-if="$slots.default" class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
      <slot />
    </div>

    <!-- Optional secondary text via footer slot -->
    <div v-if="$slots.footer" class="mt-4 text-[12px] text-gray-400">
      <slot name="footer" />
    </div>
  </div>
</template>
