import { usePage } from '@inertiajs/vue3'

export function useTrans() {
  const page = usePage()
  const translations = page?.props?.translations || {}

  const t = (key, fallback = '') => {
    if (!key) return fallback

    const parts = key.split('.')
    let value = translations

    for (const part of parts) {
      if (value && Object.prototype.hasOwnProperty.call(value, part)) {
        value = value[part]
      } else {
        return fallback || key
      }
    }

    return value
  }

  return { t }
}
