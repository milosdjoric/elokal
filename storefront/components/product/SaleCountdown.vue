<script setup lang="ts">
const props = defineProps<{
  endsAt: string
  size?: 'sm' | 'md'
}>()

const now = ref(Date.now())
const endTime = computed(() => new Date(props.endsAt).getTime())
const remaining = computed(() => Math.max(0, endTime.value - now.value))
const isExpired = computed(() => remaining.value <= 0)

const days = computed(() => Math.floor(remaining.value / (1000 * 60 * 60 * 24)))
const hours = computed(() => Math.floor((remaining.value % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)))
const minutes = computed(() => Math.floor((remaining.value % (1000 * 60 * 60)) / (1000 * 60)))
const seconds = computed(() => Math.floor((remaining.value % (1000 * 60)) / 1000))

let interval: ReturnType<typeof setInterval>

onMounted(() => {
  interval = setInterval(() => { now.value = Date.now() }, 1000)
})

onUnmounted(() => clearInterval(interval))
</script>

<template>
  <div v-if="!isExpired" :class="size === 'sm' ? 'text-xs' : 'text-sm'">
    <div class="flex items-center gap-1.5 text-red-600 font-medium">
      <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span v-if="days > 0">{{ days }}d</span>
      <span>{{ String(hours).padStart(2, '0') }}:{{ String(minutes).padStart(2, '0') }}:{{ String(seconds).padStart(2, '0') }}</span>
    </div>
  </div>
</template>
