const settings = ref<Record<string, string>>({})
let loaded = false

export function useFeature() {
  const { get } = useApi()

  async function loadSettings() {
    if (loaded) return
    try {
      const data = await get<{ data: Record<string, Record<string, string>> }>('/v1/settings')
      const flat: Record<string, string> = {}
      for (const group of Object.values(data.data)) {
        Object.assign(flat, group)
      }
      settings.value = flat
      loaded = true
    }
    catch {
      // Settings not available yet — use defaults
    }
  }

  function isEnabled(key: string, defaultValue = false): boolean {
    const val = settings.value[key]
    if (val === undefined) return defaultValue
    return val === 'true' || val === '1'
  }

  // Za feature flagove — default dolazi iz centralnog registra (utils/features.ts)
  function isFeatureEnabled(key: FeatureKey): boolean {
    return isEnabled(key, FEATURE_DEFAULTS[key] ?? false)
  }

  function getValue(key: string, defaultValue = ''): string {
    return settings.value[key] ?? defaultValue
  }

  return { loadSettings, isEnabled, isFeatureEnabled, getValue, settings }
}
