import { useEffect, useState } from 'react'
import fallbackContent from '../data/site-content.json'
import { fetchHome } from '../lib/api'
import { mergeContent } from '../lib/merge'

/**
 * Resolves homepage content.
 *
 * Starts with the bundled content model (so first paint is never empty), then
 * upgrades to live WordPress data once the REST payload arrives.
 */
export function useSiteContent() {
  const [content, setContent] = useState(fallbackContent)
  const [status, setStatus] = useState('loading')
  const [source, setSource] = useState('fallback')
  const [error, setError] = useState(null)

  useEffect(() => {
    const controller = new AbortController()

    fetchHome({ signal: controller.signal })
      .then((payload) => {
        setContent(mergeContent(fallbackContent, payload))
        setSource('wordpress')
        setStatus('ready')
      })
      .catch((err) => {
        // The component unmounted: nothing to update.
        if (controller.signal.aborted) return

        setContent(fallbackContent)
        setSource('fallback')
        setError(err?.message || 'Unable to reach WordPress')
        setStatus('ready')
      })

    return () => controller.abort()
  }, [])

  return { content, status, source, error }
}
