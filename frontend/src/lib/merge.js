const isPlainObject = (value) =>
  value !== null && typeof value === 'object' && !Array.isArray(value)

/**
 * Merge editor-managed WordPress settings over the bundled defaults.
 * Arrays are replaced wholesale (never index-merged) so removing a repeater
 * row in WordPress never resurrects fallback rows.
 */
export function deepMerge(base, override) {
  if (!isPlainObject(override)) return override === undefined ? base : override

  const result = { ...base }
  for (const [key, value] of Object.entries(override)) {
    if (value === undefined || value === null || value === '') continue
    if (isPlainObject(value) && isPlainObject(base?.[key])) {
      result[key] = deepMerge(base[key], value)
    } else {
      result[key] = value
    }
  }
  return result
}

/**
 * Normalise a REST payload into the exact shape the page components consume.
 * A collection is only taken from WordPress when it actually has rows, which
 * keeps a freshly-installed (unseeded) WordPress from blanking the page.
 */
export function mergeContent(defaults, remote = null) {
  if (!defaults) return remote || {}
  if (!remote || typeof remote !== 'object') return defaults

  const settings = deepMerge(defaults.settings, remote.settings || {})
  const merged = { settings }

  for (const [key, value] of Object.entries(defaults)) {
    if (key === 'settings') continue
    const remoteValue = remote[key]
    if (Array.isArray(value)) {
      merged[key] = Array.isArray(remoteValue) && remoteValue.length > 0 ? remoteValue : value
    } else {
      merged[key] = remoteValue ?? value
    }
  }

  return merged
}
