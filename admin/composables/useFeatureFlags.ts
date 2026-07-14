const featureFlags = ref<Record<string, boolean>>({})
const loaded = ref(false)

export function useFeatureFlags() {
  const { get } = useApi()

  async function loadFlags() {
    if (loaded.value) return
    try {
      const res = await get<{ data: Record<string, Record<string, string>> }>('/admin/settings')
      const features = res.data.features || {}
      for (const [key, value] of Object.entries(features)) {
        featureFlags.value[key] = value === 'true' || value === '1'
      }
      loaded.value = true
    }
    catch { /* silent */ }
  }

  function isEnabled(key: string, defaultValue?: boolean): boolean {
    // Default dolazi iz centralnog registra (utils/features.ts) ako nije eksplicitno prosleđen
    const fallback = defaultValue ?? FEATURE_DEFAULTS[key as FeatureKey] ?? false
    if (!loaded.value) return fallback
    return featureFlags.value[key] ?? fallback
  }

  return { loadFlags, isEnabled, loaded }
}
