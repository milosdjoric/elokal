<script setup lang="ts">
interface ToastItem { id: number; message: string; type: 'success' | 'error' | 'info' }

const toasts = ref<ToastItem[]>([])
let nextId = 0

function add(message: string, type: ToastItem['type'] = 'info', duration = 4000) {
  const id = nextId++
  toasts.value.push({ id, message, type })
  if (duration > 0) setTimeout(() => remove(id), duration)
}

function remove(id: number) { toasts.value = toasts.value.filter(t => t.id !== id) }

defineExpose({ add, remove })

const typeClasses: Record<string, string> = {
  success: 'bg-green-600',
  error: 'bg-red-600',
  info: 'bg-primary-600',
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[100] space-y-2 w-80">
      <TransitionGroup enter-active-class="transition duration-300" enter-from-class="opacity-0 translate-x-4" leave-active-class="transition duration-200" leave-to-class="opacity-0 translate-x-4">
        <div v-for="toast in toasts" :key="toast.id" class="flex items-center gap-3 px-4 py-3 text-white text-sm shadow-lg" :class="typeClasses[toast.type]">
          <p class="flex-1">{{ toast.message }}</p>
          <button class="flex-shrink-0 opacity-75 hover:opacity-100" @click="remove(toast.id)">✕</button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>
