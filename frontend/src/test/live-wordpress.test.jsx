import { render, screen, waitFor } from '@testing-library/react'
import { beforeAll, describe, expect, it, vi } from 'vitest'
import App from '../App.jsx'

/**
 * End-to-end check against a real WordPress install.
 *
 * Run with:
 *   LIVE_WP_URL_BASE=http://localhost:8080 npm run test:live
 */
describe.runIf(process.env.LIVE_WP_URL)('live WordPress integration', () => {
  beforeAll(() => {
    vi.stubEnv('VITE_WP_URL', process.env.LIVE_WP_URL_BASE || 'http://localhost:8080')
  })

  it('renders the homepage with data served by the headless CMS', async () => {
    render(<App />)

    await waitFor(
      () => {
        expect(document.querySelector('[data-content-source]')).toHaveAttribute(
          'data-content-source',
          'wordpress',
        )
      },
      { timeout: 15000 },
    )

    expect(
      screen.getByRole('heading', {
        level: 1,
        name: /professional fire safety for a safer facility/i,
      }),
    ).toBeInTheDocument()

    // Seeded custom post types render through the REST payload.
    expect(screen.getByText('Fire Hydrant & Sprinkler Systems')).toBeInTheDocument()
    expect(screen.getByText('Heavy Chemical Processing Hydrant Grid')).toBeInTheDocument()
    expect(screen.getAllByRole('article')).toHaveLength(3)
    expect(
      screen.getAllByRole('button', {
        name: /fire noc be renewed|certified equipment|hydrant system needs repair|hydraulic calculations|fire safety audit take|before buying or leasing|quarterly amc|emergency site service/i,
      }),
    ).toHaveLength(8)
  }, 20000)
})
