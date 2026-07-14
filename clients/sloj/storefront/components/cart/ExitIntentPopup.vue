<script setup lang="ts">
const { isFeatureEnabled } = useFeature()
const { items, isEmpty } = useCart()
const cartStore = useCartStore()
const { apiBase } = useApi()

const show = ref(false)
const email = ref('')
const submitting = ref(false)
const submitted = ref(false)
const error = ref('')

let featureActive = false

async function init() {
  featureActive = isFeatureEnabled(FEATURES.abandonedCart)
  if (!featureActive) return

  if (import.meta.client) {
    if (sessionStorage.getItem('exit_intent_shown')) return

    // Desktop: mouseleave kad kursor napusti viewport
    document.documentElement.addEventListener('mouseleave', onMouseLeave)

    // Mobile: kad korisnik napusti tab
    document.addEventListener('visibilitychange', onVisibilityChange)
  }
}

function onMouseLeave(e: MouseEvent) {
  if (e.clientY <= 0) {
    tryShow()
  }
}

function onVisibilityChange() {
  if (document.visibilityState === 'hidden') {
    tryShow()
  }
}

function tryShow() {
  if (show.value || submitted.value) return
  if (isEmpty.value) return
  if (sessionStorage.getItem('exit_intent_shown')) return

  show.value = true
  sessionStorage.setItem('exit_intent_shown', '1')
}

function close() {
  show.value = false
}

async function handleSubmit() {
  if (!email.value.trim()) return
  submitting.value = true
  error.value = ''
  try {
    await $fetch(`${apiBase}/v1/abandoned-cart`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        email: email.value,
        items: items.value.map(i => ({
          product_id: i.product.id,
          name: i.product.name,
          price: i.product.effective_price,
          quantity: i.quantity,
        })),
        total: cartStore.total,
      }),
    })
    submitted.value = true
  }
  catch {
    error.value = 'Došlo je do greške. Pokušajte ponovo.'
  }
  finally {
    submitting.value = false
  }
}

onMounted(init)

onUnmounted(() => {
  if (import.meta.client) {
    document.documentElement.removeEventListener('mouseleave', onMouseLeave)
    document.removeEventListener('visibilitychange', onVisibilityChange)
  }
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="close">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/50" />

        <!-- Modal -->
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-8 text-center">
          <button
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
            aria-label="Zatvori"
            @click="close"
          >
            <Icon name="lucide:x" class="w-5 h-5" />
          </button>

          <template v-if="!submitted">
            <!-- Ikonica korpe -->
            <div class="mx-auto w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mb-4">
              <Icon name="lucide:shopping-bag" class="w-8 h-8 text-primary-600" />
            </div>

            <h3 class="text-xl font-bold text-gray-800 mb-2">Sačuvajte svoju korpu!</h3>
            <p class="text-sm text-gray-500 mb-6">
              Unesite email i poslačemo vam link za nastavak kupovine.
            </p>

            <form class="space-y-3" @submit.prevent="handleSubmit">
              <input
                v-model="email"
                type="email"
                placeholder="Vaš email"
                required
                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
              <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
              <button
                type="submit"
                :disabled="submitting"
                class="w-full px-4 py-3 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50 transition-colors"
              >
                {{ submitting ? 'Čuvanje...' : 'Sačuvaj korpu' }}
              </button>
            </form>

            <button class="mt-3 text-xs text-gray-400 hover:text-gray-600" @click="close">
              Ne hvala, nastaviću kasnije
            </button>
          </template>

          <template v-else>
            <!-- Success -->
            <div class="mx-auto w-16 h-16 bg-success-soft rounded-full flex items-center justify-center mb-4">
              <Icon name="lucide:check-circle-2" class="w-8 h-8 text-success" />
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Korpa sačuvana!</h3>
            <p class="text-sm text-gray-500 mb-4">Poslačemo vam link na {{ email }}.</p>
            <button
              class="px-6 py-2 text-sm font-medium text-primary-600 border border-primary-600 rounded-lg hover:bg-primary-50 transition-colors"
              @click="close"
            >
              Nastavi kupovinu
            </button>
          </template>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
