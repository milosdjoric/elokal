<script setup lang="ts">
interface Props {
  modelValue: boolean
  title?: string
  message?: string
  confirmText?: string
  cancelText?: string
  variant?: 'danger' | 'warning' | 'info'
  loading?: boolean
}

withDefaults(defineProps<Props>(), {
  title: 'Potvrda',
  message: 'Da li ste sigurni?',
  confirmText: 'Potvrdi',
  cancelText: 'Otkaži',
  variant: 'danger',
  loading: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  confirm: []
  cancel: []
}>()

function close() {
  emit('update:modelValue', false)
  emit('cancel')
}

function confirm() {
  emit('confirm')
}

const confirmVariant: Record<string, 'danger' | 'primary'> = {
  danger: 'danger',
  warning: 'danger',
  info: 'primary',
}
</script>

<template>
  <UiMoleculesModal :model-value="modelValue" :title="title" size="sm" @update:model-value="close">
    <p class="text-sm text-gray-600">{{ message }}</p>

    <template #footer>
      <UiAtomsButton variant="secondary" @click="close">
        {{ cancelText }}
      </UiAtomsButton>
      <UiAtomsButton
        :variant="confirmVariant[variant]"
        :loading="loading"
        @click="confirm"
      >
        {{ confirmText }}
      </UiAtomsButton>
    </template>
  </UiMoleculesModal>
</template>
