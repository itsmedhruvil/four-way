/**
 * Thin client for the headless WordPress REST API.
 *
 * Every call fails soft: the UI always has bundled fallback content so the
 * marketing site keeps rendering even when WordPress is unreachable.
 */
const DEFAULT_WP_URL = 'http://localhost:8080'
export const HOME_ENDPOINT =
  import.meta.env?.VITE_WP_HOME_ENDPOINT || '/4way/v1/home'

/** Base URL resolved at call time so tests and previews can stub it. */
export function wpBase() {
  return String(import.meta.env?.VITE_WP_URL || DEFAULT_WP_URL).replace(/\/+$/, '')
}

export function restRoot() {
  return `${wpBase()}/wp-json`
}

/**
 * Endpoints are getters so a changed environment is picked up on the next call.
 */
export const endpoints = {
  get home() {
    return `${restRoot()}${HOME_ENDPOINT}`
  },
  get settings() {
    return `${restRoot()}/4way/v1/settings`
  },
  get contact() {
    return `${restRoot()}/4way/v1/contact`
  },
  get subscribe() {
    return `${restRoot()}/4way/v1/subscribe`
  },
}

/** Kept for backwards compatibility with older imports. */
export const WP_BASE = DEFAULT_WP_URL

const REQUEST_TIMEOUT_MS = 8000

async function request(url, { method = 'GET', body, signal } = {}) {
  const controller = new AbortController()
  const timeout = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS)

  if (signal) {
    if (signal.aborted) controller.abort()
    else signal.addEventListener('abort', () => controller.abort(), { once: true })
  }

  try {
    const init = {
      method,
      headers: {
        Accept: 'application/json',
        ...(body ? { 'Content-Type': 'application/json' } : {}),
      },
      body: body ? JSON.stringify(body) : undefined,
      signal: controller.signal,
    }

    let response
    try {
      response = await fetch(url, init)
    } catch (error) {
      // jsdom exposes its own AbortSignal realm, which undici rejects. The
      // browser path keeps real cancellation; tests simply skip the signal.
      if (error instanceof TypeError && /signal/i.test(error.message || '')) {
        response = await fetch(url, { ...init, signal: undefined })
      } else {
        throw error
      }
    }

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
