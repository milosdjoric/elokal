interface ShippingMethodInfo {
  id: number
  name: string
  cost: number
  free_above: number | null
  estimated_days: string | null
}

const shippingConfig = reactive({
  freeShippingThreshold: null as number | null,
  defaultShippingCost: null as number | null,
  methods: [] as ShippingMethodInfo[],
  loaded: false,
})

export function useShippingConfig() {
  async function load() {
    if (shippingConfig.loaded) return
    try {
      const { get } = useApi()
      const data = await get<{ data: { free_shipping_threshold: number | null; default_shipping_cost: number | null; methods: ShippingMethodInfo[] } }>('/v1/shipping/config')
      shippingConfig.freeShippingThreshold = data.data.free_shipping_threshold
      shippingConfig.defaultShippingCost = data.data.default_shipping_cost
      shippingConfig.methods = data.data.methods
      shippingConfig.loaded = true
    }
    catch { /* fallback — ostaju null */ }
  }

  const hasFreeShippingThreshold = computed(() => shippingConfig.freeShippingThreshold !== null)

  const isFreeShipping = (subtotal: number) =>
    shippingConfig.freeShippingThreshold !== null && subtotal >= shippingConfig.freeShippingThreshold

  const remainingForFree = (subtotal: number) =>
    shippingConfig.freeShippingThreshold !== null
      ? Math.max(0, shippingConfig.freeShippingThreshold - subtotal)
      : 0

  const freeProgress = (subtotal: number) =>
    shippingConfig.freeShippingThreshold !== null
      ? Math.min(100, (subtotal / shippingConfig.freeShippingThreshold) * 100)
      : 0

  const estimatedCost = (subtotal: number): number | null => {
    if (shippingConfig.defaultShippingCost === null) return null
    if (isFreeShipping(subtotal)) return 0
    return shippingConfig.defaultShippingCost
  }

  const formattedThreshold = computed(() =>
    shippingConfig.freeShippingThreshold !== null
      ? shippingConfig.freeShippingThreshold.toLocaleString('sr-RS', { minimumFractionDigits: 0 })
      : null,
  )

  return {
    config: shippingConfig,
    load,
    hasFreeShippingThreshold,
    isFreeShipping,
    remainingForFree,
    freeProgress,
    estimatedCost,
    formattedThreshold,
  }
}
