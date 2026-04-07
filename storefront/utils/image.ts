export function resolveImageUrl(path: string): string {
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path
  }
  return `http://localhost:8000/storage/${path}`
}
