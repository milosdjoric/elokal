<script setup lang="ts">
defineProps<{ url: string; title: string }>()

const copied = ref(false)

function copyLink(url: string) {
  navigator.clipboard.writeText(url)
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}
</script>

<template>
  <div class="flex items-center gap-3">
    <span class="text-sm text-gray-500">Podeli:</span>
    <a
      :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`"
      target="_blank"
      rel="noopener"
      aria-label="Podeli na Facebook"
      class="text-gray-400 hover:text-[#1877f2] transition-colors"
    >
      <Icon name="lucide:facebook" class="w-5 h-5" />
    </a>
    <a
      :href="`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`"
      target="_blank"
      rel="noopener"
      aria-label="Podeli na LinkedIn"
      class="text-gray-400 hover:text-[#0a66c2] transition-colors"
    >
      <Icon name="lucide:linkedin" class="w-5 h-5" />
    </a>
    <a
      :href="`https://api.whatsapp.com/send?text=${encodeURIComponent(title + ' ' + url)}`"
      target="_blank"
      rel="noopener"
      aria-label="Podeli preko WhatsApp"
      class="text-gray-400 hover:text-[#25d366] transition-colors"
    >
      <Icon name="lucide:message-circle" class="w-5 h-5" />
    </a>
    <a
      :href="`https://www.pinterest.com/pin/create/button/?url=${encodeURIComponent(url)}&description=${encodeURIComponent(title)}`"
      target="_blank"
      rel="noopener"
      aria-label="Podeli na Pinterest"
      class="text-gray-400 hover:text-[#bd081c] transition-colors"
    >
      <Icon name="lucide:image" class="w-5 h-5" />
    </a>
    <button
      type="button"
      class="text-gray-400 hover:text-primary-700 transition-colors"
      :aria-label="copied ? 'Link kopiran' : 'Kopiraj link'"
      @click="copyLink(url)"
    >
      <Icon v-if="!copied" name="lucide:link-2" class="w-5 h-5" />
      <Icon v-else name="lucide:check" class="w-5 h-5 text-success" />
    </button>
  </div>
</template>
