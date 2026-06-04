<script setup lang="ts">
const props = withDefaults(defineProps<{
  endsAt: string
  size?: 'sm' | 'md'
  inline?: boolean
}>(), { size: 'md', inline: false })

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
  <span v-if="!isExpired && inline" class="font-medium text-gray-800 tabular-nums">
    Akcija ističe za
    <span class="font-bold ml-1">
      <span v-if="days > 0">{{ days }}d </span>
      {{ String(hours).padStart(2, '0') }}:{{ String(minutes).padStart(2, '0') }}:{{ String(seconds).padStart(2, '0') }}
    </span>
  </span>

  <div v-else-if="!isExpired" :class="size === 'sm' ? 'text-xs' : 'text-sm'">
    <div class="flex items-center gap-1.5 text-danger font-medium tabular-nums">
      <Icon name="lucide:clock" class="w-3.5 h-3.5 flex-shrink-0" />
      <span v-if="days > 0">{{ days }}d</span>
      <span>{{ String(hours).padStart(2, '0') }}:{{ String(minutes).padStart(2, '0') }}:{{ String(seconds).padStart(2, '0') }}</span>
    </div>
  </div>
</template>
