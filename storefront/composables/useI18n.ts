import srMessages from '~/locales/sr.json'
import enMessages from '~/locales/en.json'

type Messages = typeof srMessages

const localeMessages: Record<string, Messages> = {
  sr: srMessages,
  en: enMessages,
}

const currentLocale = ref('sr')

export function useI18n() {
  function setLocale(locale: string) {
    if (localeMessages[locale]) {
      currentLocale.value = locale
      if (import.meta.client) {
        localStorage.setItem('locale', locale)
      }
    }
  }

  function loadSavedLocale() {
    if (import.meta.client) {
      const saved = localStorage.getItem('locale')
      if (saved && localeMessages[saved]) {
        currentLocale.value = saved
      }
    }
  }

  /**
   * Prevedi ključ. Podržava nested ključeve: t('product.addToCart')
   * i interpolaciju: t('product.boughtTimes', { count: 5 })
   */
  function t(key: string, params?: Record<string, string | number>): string {
    const messages = localeMessages[currentLocale.value] || localeMessages.sr
    const keys = key.split('.')
    let value: unknown = messages

    for (const k of keys) {
      if (value && typeof value === 'object' && k in value) {
        value = (value as Record<string, unknown>)[k]
      } else {
        return key // Fallback na ključ
      }
    }

    if (typeof value !== 'string') return key

    if (params) {
      return value.replace(/\{(\w+)\}/g, (_, k) => String(params[k] ?? `{${k}}`))
    }

    return value
  }

  const locale = computed(() => currentLocale.value)
  const availableLocales = Object.keys(localeMessages)

  return { t, locale, setLocale, loadSavedLocale, availableLocales }
}
