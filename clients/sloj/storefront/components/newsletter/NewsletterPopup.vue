<script setup lang="ts">
const { isEnabled, isFeatureEnabled, getValue, loadSettings } = useFeature()
const { apiBase } = useApi()

const show = ref(false)
const email = ref('')
const submitting = ref(false)
const submitted = ref(false)
const error = ref('')

let timerId: ReturnType<typeof setTimeout> | null = null

async function init() {
  await loadSettings()

  if (!isFeatureEnabled(FEATURES.newsletter)) return
  if (!isEnabled('newsletter_popup_enabled', true)) return

  if (import.meta.client) {
    // Već prikazan u ovoj sesiji
    if (sessionStorage.getItem('newsletter_popup_shown')) return
    // Korisnik je već pretplaćen
    if (localStorage.getItem('newsletter_subscribed')) return

    // Timer trigger
    const delay = parseInt(getValue('newsletter_popup_delay', '10'), 10)
    timerId = setTimeout(() => tryShow(), delay * 1000)

    // Exit intent: desktop mouseleave + mobile visibilitychange
    document.documentElement.addEventListener('mouseleave', onMouseLeave)
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
  if (sessionStorage.getItem('newsletter_popup_shown')) return

  show.value = true
  sessionStorage.setItem('newsletter_popup_shown', '1')
  cleanup()
}

function cleanup() {
  if (timerId) {
    clearTimeout(timerId)
    timerId = null
  }
  document.documentElement.removeEventListener('mouseleave', onMouseLeave)
  document.removeEventListener('visibilitychange', onVisibilityChange)
}

function close() {
  show.value = false
}

async function handleSubmit() {
  if (!email.value.trim()) return
  submitting.value = true
  error.value = ''
  try {
    await $fetch(`${apiBase}/v1/newsletter/subscribe`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email: email.value, source: 'popup' }),
    })
    submitted.value = true
    localStorage.setItem('newsletter_subscribed', '1')
  }
  catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    error.value = err.data?.message || 'Došlo je do greške. Pokušajte ponovo.'
  }
  finally {
    submitting.value = false
  }
}

onMounted(init)
onUnmounted(cleanup)
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
        <div class="relative bg-white shadow-xl max-w-md w-full p-8 text-center">
          <button
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
            aria-label="Zatvori"
            @click="close"
          >
            <Icon name="lucide:x" class="w-5 h-5" />
          </button>

          <template v-if="!submitted">
            <div class="mx-auto w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mb-4">
              <Icon name="lucide:mail" class="w-8 h-8 text-primary-700" />
            </div>

            <h3 class="text-xl font-bold text-gray-800 mb-2">Ne propustite akcije!</h3>
            <p class="text-sm text-gray-500 mb-6">
              Prijavite se na newsletter i budite prvi koji saznaju o novim proizvodima i posebnim ponudama.
            </p>

            <form class="space-y-3" @submit.prevent="handleSubmit">
              <input
                v-model="email"
                type="email"
                placeholder="Vaša email adresa"
                required
                class="w-full px-4 py-3 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
              <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
              <button
                type="submit"
                :disabled="submitting"
                class="w-full px-4 py-3 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 disabled:opacity-50 transition-colors"
              >
                {{ submitting ? 'Prijava...' : 'Prijavi se' }}
              </button>
            </form>

            <button class="mt-3 text-xs text-gray-400 hover:text-gray-600" @click="close">
              Ne hvala
            </button>
          </template>

          <template v-else>
            <div class="mx-auto w-16 h-16 bg-success-soft rounded-full flex items-center justify-center mb-4">
              <Icon name="lucide:check-circle-2" class="w-8 h-8 text-success" />
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Hvala na prijavi!</h3>
            <p class="text-sm text-gray-500 mb-4">Proverite inbox — poslali smo vam email za potvrdu.</p>
            <button
              class="px-6 py-2 text-sm font-medium text-primary-600 border border-primary-600 hover:bg-primary-50 transition-colors"
              @click="close"
            >
              Nastavi sa kupovinom
            </button>
          </template>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
