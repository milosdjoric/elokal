export function useSlug() {
  function generateSlug(text: string): string {
    const charMap: Record<string, string> = {
      č: 'c', ć: 'c', š: 's', ž: 'z', đ: 'dj',
      Č: 'c', Ć: 'c', Š: 's', Ž: 'z', Đ: 'dj',
    }

    return text
      .split('')
      .map(c => charMap[c] || c)
      .join('')
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/[\s]+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '')
  }

  return { generateSlug }
}
