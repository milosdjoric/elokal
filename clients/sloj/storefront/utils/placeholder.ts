/**
 * Privremeni placeholder za proizvode bez (dostupne) slike — koristi placehold.co
 * u sloj brand bojama (ink-800 pozadina, belo slovo). Eksterni URL, bez fajlova/storage-a.
 * Kad pravi Opendesk media bude na trajnom storage-u, ovo prestaje da se okida (samo @error fallback).
 */
export function placeholderImage(text: string, size = 600): string {
  const label = encodeURIComponent((text || 'sloj').trim()).replace(/%20/g, '+')
  return `https://placehold.co/${size}x${size}/1a1a1a/ffffff?text=${label}&font=lato`
}

/**
 * @error handler za <img>: pri padu učitavanja zameni izvor brand placeholderom.
 * Guard (dataset.fallback) sprečava beskonačnu petlju ako i placeholder padne.
 */
export function onImageError(event: Event, text: string, size = 600): void {
  const el = event.target as HTMLImageElement
  if (el.dataset.fallback) return
  el.dataset.fallback = '1'
  el.src = placeholderImage(text, size)
}
