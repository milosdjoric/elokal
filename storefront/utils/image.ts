// Jedinstveni izvor za URL slike. Relativan path (npr "products/x.jpg") se servira
// sa `storage/` na API hostu — host se izvlači iz apiBase (radi i lokalno i na prod),
// nikad hardkodovan. Apsolutni URL-ovi (placehold, opendesk CDN) prolaze netaknuti.
export function resolveImageUrl(path: string | null | undefined): string {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path
  }
  const apiBase = useRuntimeConfig().public.apiBase as string
  return `${apiBase.replace(/\/api\/?$/, '')}/storage/${path}`
}
