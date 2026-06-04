<script setup lang="ts">
interface Props {
  size?: 'sm' | 'md' | 'lg'
  inverse?: boolean
  withDescriptor?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  inverse: false,
  withDescriptor: true,
})

const sizeMap = {
  sm: { letter: 'text-[14px]', descriptor: 'text-[10px]', gap: 'gap-1.5' },
  md: { letter: 'text-[18px]', descriptor: 'text-[11px]', gap: 'gap-2' },
  lg: { letter: 'text-[28px] md:text-[36px]', descriptor: 'text-[14px] md:text-[16px]', gap: 'gap-3' },
} as const

const colors = computed(() => props.inverse
  ? { letter: 'text-ply-50', descriptor: 'text-ply-300/70', divider: 'bg-ply-300/40' }
  : { letter: 'text-ink-800', descriptor: 'text-ink-500', divider: 'bg-ink-300' },
)
</script>

<template>
  <span class="inline-flex flex-col leading-none select-none" aria-label="sloj kolektiv">
    <span
      class="font-medium tracking-[0.08em]"
      :class="[sizeMap[size].letter, colors.letter]"
      aria-hidden="true"
    >
      S<span class="mx-1 opacity-70">—</span>L<span class="mx-1 opacity-70">—</span>O<span class="mx-1 opacity-70">—</span>J
    </span>
    <span
      v-if="withDescriptor"
      class="lowercase font-normal tracking-[0.16em] mt-1"
      :class="[sizeMap[size].descriptor, colors.descriptor]"
      aria-hidden="true"
    >
      kolektiv
    </span>
  </span>
</template>
