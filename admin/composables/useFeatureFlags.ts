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

  function isEnabled(key: string, defaultValue = true): boolean {
    if (!loaded.value) return defaultValue
    return featureFlags.value[key] ?? defaultValue
  }

  return { loadFlags, isEnabled, loaded }
}
