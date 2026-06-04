/**
 * Format cene za sr-RS locale.
 * - Ceo broj (npr. 2499) → "2.499 RSD"
 * - Decimalan (npr. 2499.50) → "2.499,50 RSD"
 * - currency suffix opciono (može i bez)
 *
 * Razlog: srpski Zakon o trgovini ne traži obavezno dva decimalna mesta.
 * "2.499,00 RSD" je vizuelni šum; sakrivanje ,00 daje cleaner UI.
 */

export interface FormatPriceOptions {
  /** Show currency suffix. Default true. */
  currency?: boolean
  /** Currency symbol. Default "RSD". */
  symbol?: string
  /** Force 2 decimals always (npr. za invoice/financial reports). */
  forceDecimals?: boolean
}

export function formatPrice(value: number | string | null | undefined, options: FormatPriceOptions = {}): string {
  if (value === null || value === undefined) return '—'

  const num = typeof value === 'string' ? parseFloat(value) : value
  if (Number.isNaN(num)) return '—'

  const { currency = true, symbol = 'RSD', forceDecimals = false } = options

  const isWhole = Number.isInteger(num) || Math.abs(num - Math.round(num)) < 0.005

  const formatted = num.toLocaleString('sr-RS', {
    minimumFractionDigits: forceDecimals || !isWhole ? 2 : 0,
    maximumFractionDigits: 2,
  })

  return currency ? `${formatted} ${symbol}` : formatted
}

/**
 * Razdvoji cenu na amount + currency parts za render kontrolu.
 * Korisno kad želiš RSD u manjem fontu/lighter color:
 *
 *   <span class="text-2xl font-bold tabular-nums">{{ amount }}</span>
 *   <span class="text-sm text-gray-500 ml-1">{{ currency }}</span>
 */
export function formatPriceParts(value: number | string | null | undefined, options: FormatPriceOptions = {}): { amount: string; currency: string } {
  const symbol = options.symbol || 'RSD'
  const amount = formatPrice(value, { ...options, currency: false })
  return { amount, currency: symbol }
}
