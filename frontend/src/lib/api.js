/**
 * Thin client for the headless WordPress REST API.
 *
 * Every call fails soft: the UI always has bundled fallback content so the
 * marketing site keeps rendering even when WordPress is unreachable.
 */
const RAW_BASE = import.meta.env?.VITE_WP_URL || 'http://localhost:8080'

export const WP_BASE = String(RAW_BASE).replace(/\/+$/, '')
export const REST_ROOT = `${WP_BASE}/wp-json`
export const HOME_ENDPOINT =
  import.meta.env?.VITE_WP_HOME_ENDPOINT || '/4way/v1/home'

export const endpoints = {
  home: `${REST_ROOT}${HOME_ENDPOINT}`,
  settings: `${REST_ROOT}/4way/v1/settings`,
  contact: `${REST_ROOT}/4way/v1/contact`,
  subscribe: `${REST_ROOT}/4way/v1/subscribe`,
}

const REQUEST_TIMEOUT_MS = 8000

async function request(url, { method = 'GET', body, signal } = {}) {
  const controller = new AbortController()
  const timeout = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS)

  if (signal) {
    if (signal.aborted) controller.abort()
    else signal.addEventListener('abort', () => controller.abort(), { once: true })
  }

  try {
    const response = await fetch(url, {
      method,
      headers: {
        Accept: 'application/json',
        ...(body ? { 'Content-Type': 'application/json' } : {}),
      },
      body: body ? JSON.stringify(body) : undefined,
      signal: controller.signal,
    })

    if (!response.ok) {
      throw new Error(`WordPress request failed (${response.status})`)
    }

    return await response.json()
  } finally {
    clearTimeout(timeout)
  }
}

/** Fetch the aggregated homepage payload assembled by the 4way/v1/home route. */
export function fetchHome({ signal } = {}) {
  return request(endpoints.home, { signal })
}

/** Store a quote / enquiry in the wp_4way_leads table. */
export function submitQuote(payload) {
  return request(endpoints.contact, { method: 'POST', body: payload })
}

/** Store a newsletter subscriber in the wp_4way_subscribers table. */
export function subscribe(email) {
  return request(endpoints.subscribe, { method: 'POST', body: { email } })
}
