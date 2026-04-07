export function useApi() {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase as string

  async function get<T>(path: string, params?: Record<string, string | number | boolean>): Promise<T> {
    const query = params
      ? '?' + new URLSearchParams(
          Object.entries(params)
            .filter(([, v]) => v !== undefined && v !== null && v !== '')
            .map(([k, v]) => [k, String(v)]),
        ).toString()
      : ''

    return $fetch<T>(`${apiBase}${path}${query}`, {
      method: 'GET',
      headers: { Accept: 'application/json' },
    })
  }

  return { get, apiBase }
}
