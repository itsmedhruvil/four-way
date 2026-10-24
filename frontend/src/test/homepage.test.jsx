import { render, screen, waitFor, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import App from '../App.jsx'
import { endpoints } from '../lib/api.js'
import fallbackContent from '../data/site-content.json'

const mockFetch = (handler) => {
  const fetchMock = vi.fn(handler)
  vi.stubGlobal('fetch', fetchMock)
  return fetchMock
}

const jsonResponse = (body, ok = true, status = 200) => ({
  ok,
  status,
  json: async () => body,
})

describe('4Way Safety homepage', () => {
  beforeEach(() => {
    mockFetch(() => Promise.reject(new Error('offline')))
  })

  it('renders the designed homepage sections from bundled content when WordPress is offline', async () => {
    render(<App />)

    expect(
      screen.getByRole('heading', {
        level: 1,
        name: /professional fire safety for a safer facility/i,
      }),
    ).toBeInTheDocument()

    expect(screen.getByText(/who we are/i)).toBeInTheDocument()
    expect(
      screen.getByRole('heading', { name: /complete fire & protection care/i }),
    ).toBeInTheDocument()
    expect(
      screen.getByRole('heading', { name: /professional fire safety you can/i }),
    ).toBeInTheDocument()
    expect(
      screen.getByRole('heading', { name: /simple service\. clear process\./i }),
    ).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /engineering rigor you can/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /trusted by facility heads/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /everything you need to/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /tips, guides & ideas/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /stay in the know/i })).toBeInTheDocument()

    // Six service cards, four projects, eight FAQs, three blog posts.
    expect(screen.getAllByRole('link', { name: /^learn about (fire|extinguisher|gas|annual)/i })).toHaveLength(6)
    expect(screen.getAllByRole('link', { name: /^view project:/i })).toHaveLength(4)
    expect(
      screen.getAllByRole('button', {
        name: /fire noc be renewed|certified equipment|hydrant system needs repair|hydraulic calculations|fire safety audit take|before buying or leasing|quarterly amc|emergency site service/i,
      }),
    ).toHaveLength(8)
    expect(screen.getAllByRole('article')).toHaveLength(3)

    await waitFor(() => {
      expect(document.querySelector('[data-content-source]')).toHaveAttribute(
        'data-content-source',
        'fallback',
      )
    })
  })

  it('hydrates the page with live WordPress content from the 4way/v1/home endpoint', async () => {
    mockFetch((url) => {
      if (url === endpoints.home) {
        return Promise.resolve(
          jsonResponse({
            settings: {
              brand: { name: '4Way Live' },
              hero: {
                titleStart: 'Live WordPress',
                titleHighlight: 'Powered',
                titleEnd: 'Homepage',
                description: 'Content served from the headless CMS.',
              },
            },
            services: [
              {
                slug: 'live-service',
                title: 'Live Hydrant Service',
                description: 'Sourced from a WordPress custom post type.',
                image: 'https://example.test/live.jpg',
                imageAlt: 'Live service',
                href: '#contact',
              },
            ],
            faqs: [{ question: 'Live FAQ question?', answer: 'Live FAQ answer.', open: false }],
          }),
        )
      }
      return Promise.resolve(jsonResponse({}, false, 404))
    })

    render(<App />)

    expect(
      await screen.findByRole('heading', { name: /live wordpress powered homepage/i }),
    ).toBeInTheDocument()
    expect(screen.getByText('Live Hydrant Service')).toBeInTheDocument()
    expect(screen.queryByText('Fire Hydrant & Sprinkler Systems')).not.toBeInTheDocument()
    // Collections not returned by WordPress keep their designed fallback rows.
    expect(screen.getAllByRole('article')).toHaveLength(3)
    expect(document.querySelector('[data-content-source]')).toHaveAttribute(
      'data-content-source',
      'wordpress',
    )
  })

  it('toggles FAQ answers and keeps the first answer open by design', async () => {
    const user = userEvent.setup()
    render(<App />)

    expect(document.getElementById('faq-answer-0')).not.toHaveClass('hidden')
    expect(document.getElementById('faq-answer-1')).toHaveClass('hidden')

    const secondToggle = screen.getByRole('button', {
      name: /do you supply bis and nfpa certified equipment/i,
    })
    expect(secondToggle).toHaveAttribute('aria-expanded', 'false')

    await user.click(secondToggle)

    expect(secondToggle).toHaveAttribute('aria-expanded', 'true')
    expect(document.getElementById('faq-answer-1')).not.toHaveClass('hidden')

    await user.click(
      screen.getByRole('button', { name: /how often should our fire noc be renewed/i }),
    )
    expect(document.getElementById('faq-answer-0')).toHaveClass('hidden')
  })

  it('opens the quote modal from the header CTA and stores the enquiry in WordPress', async () => {
    const user = userEvent.setup()
    const fetchMock = mockFetch((url, options) => {
      if (url === endpoints.home) return Promise.reject(new Error('offline'))
      if (url === endpoints.contact && options?.method === 'POST') {
        return Promise.resolve(jsonResponse({ ok: true, id: 42 }))
      }
      return Promise.resolve(jsonResponse({}, false, 404))
    })

    render(<App />)

    const header = screen.getByTestId('site-header')
    await user.click(
      within(header).getAllByRole('button', { name: /get a free quote/i })[0],
    )

    const dialog = screen.getByRole('dialog')
    await user.type(within(dialog).getByLabelText(/full name/i), 'Anita Shah')
    await user.type(within(dialog).getByLabelText(/work email/i), 'anita@example.com')
    await user.type(within(dialog).getByLabelText(/^phone$/i), '+91 99999 00000')
    await user.type(within(dialog).getByLabelText(/facility \/ company/i), 'Vapi GIDC Plant')
    await user.type(within(dialog).getByLabelText(/what do you need\?/i), 'Hydrant AMC quote')
    await user.click(within(dialog).getByRole('button', { name: /request my quote/i }))

    expect(await within(dialog).findByRole('status')).toHaveTextContent(
      /our fire safety engineer will contact you/i,
    )

    const contactCall = fetchMock.mock.calls.find(([url]) => url === endpoints.contact)
    expect(contactCall).toBeTruthy()
    expect(JSON.parse(contactCall[1].body)).toMatchObject({
      name: 'Anita Shah',
      email: 'anita@example.com',
      message: 'Hydrant AMC quote',
      source: 'homepage-quote-modal',
    })
  })

  it('subscribes visitors through the WordPress newsletter endpoint', async () => {
    const user = userEvent.setup()
    const fetchMock = mockFetch((url, options) => {
      if (url === endpoints.home) return Promise.reject(new Error('offline'))
      if (url === endpoints.subscribe && options?.method === 'POST') {
        return Promise.resolve(jsonResponse({ ok: true }))
      }
      return Promise.resolve(jsonResponse({}, false, 404))
    })

    render(<App />)

    await user.type(screen.getByLabelText(/email address/i), 'safety@example.com')
    await user.click(screen.getByRole('button', { name: /^subscribe$/i }))

    expect(
      await screen.findByText(fallbackContent.settings.newsletter.successMessage),
    ).toBeInTheDocument()
    const subscribeCall = fetchMock.mock.calls.find(([url]) => url === endpoints.subscribe)
    expect(JSON.parse(subscribeCall[1].body)).toEqual({ email: 'safety@example.com' })
  })
})
