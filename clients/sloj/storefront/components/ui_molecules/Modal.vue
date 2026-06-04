<script setup lang="ts">
defineProps<{ modelValue: boolean; title?: string }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()

function close() { emit('update:modelValue', false) }
function onKeydown(e: KeyboardEvent) { if (e.key === 'Escape') close() }

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="close" />
        <div class="relative bg-white shadow-xl w-full max-w-lg">
          <div v-if="title" class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">{{ title }}</h3>
            <button class="p-1 text-gray-400 hover:text-gray-600" @click="close">
              <Icon name="lucide:x" class="w-5 h-5" />
            </button>
          </div>
          <div class="px-6 py-4"><slot /></div>
          <div v-if="$slots.footer" class="px-6 py-4 border-t flex justify-end gap-3"><slot name="footer" /></div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
