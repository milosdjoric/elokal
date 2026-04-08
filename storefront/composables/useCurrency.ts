interface Currency {
  id: number
  code: string
  name: string
  symbol: string
  exchange_rate: string
  is_default: boolean
}

const currencies = ref<Currency[]>([])
const activeCurrency = ref<Currency | null>(null)

export function useCurrency() {
  const { get } = useApi()

  async function fetchCurrencies() {
    if (currencies.value.length > 0) return
    try {
      const data = await get<{ data: Currency[] }>('/v1/currencies')
      currencies.value = data.data

      // Učitaj iz localStorage ili koristi default
      const saved = import.meta.client ? localStorage.getItem('currency') : null
      const match = saved ? data.data.find(c => c.code === saved) : null
      activeCurrency.value = match || data.data.find(c => c.is_default) || data.data[0] || null
    }
    catch { /* silent */ }
  }

  function setCurrency(code: string) {
    const found = currencies.value.find(c => c.code === code)
    if (found) {
      activeCurrency.value = found
      if (import.meta.client) localStorage.setItem('currency', code)
    }
  }

  function convert(amountRsd: number | string): number {
    const amount = typeof amountRsd === 'string' ? parseFloat(amountRsd) : amountRsd
    if (!activeCurrency.value || activeCurrency.value.is_default) return amount
    return amount * parseFloat(activeCurrency.value.exchange_rate)
  }

  function formatPrice(amountRsd: number | string): string {
    const converted = convert(amountRsd)
    const symbol = activeCurrency.value?.symbol || 'RSD'
    return `${converted.toLocaleString('sr-RS', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${symbol}`
  }

  const isDefault = computed(() => !activeCurrency.value || activeCurrency.value.is_default)

  return {
    currencies,
    activeCurrency,
    fetchCurrencies,
    setCurrency,
    convert,
    formatPrice,
    isDefault,
  }
}
