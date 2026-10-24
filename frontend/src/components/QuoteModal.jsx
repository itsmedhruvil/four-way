import { useEffect, useRef, useState } from 'react'
import { submitQuote } from '../lib/api.js'
import { Kicker } from './SectionTitle.jsx'

const EMPTY_FORM = {
  name: '',
  email: '',
  phone: '',
  facility: '',
  message: '',
}

export default function QuoteModal({ open, onClose, settings }) {
  const [form, setForm] = useState(EMPTY_FORM)
  const [state, setState] = useState({ status: 'idle', message: '' })
  const firstFieldRef = useRef(null)

  useEffect(() => {
    if (!open) return undefined

    const previousOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    firstFieldRef.current?.focus()

    const handleKeyDown = (event) => {
      if (event.key === 'Escape') onClose()
    }
    document.addEventListener('keydown', handleKeyDown)

    return () => {
      document.body.style.overflow = previousOverflow
      document.removeEventListener('keydown', handleKeyDown)
    }
  }, [open, onClose])

  useEffect(() => {
    if (!open) {
      setState({ status: 'idle', message: '' })
    }
  }, [open])

  if (!open) return null

  const update = (field) => (event) =>
    setForm((current) => ({ ...current, [field]: event.target.value }))

  const handleSubmit = async (event) => {
    event.preventDefault()
    setState({ status: 'submitting', message: '' })

    try {
      await submitQuote({ ...form, source: 'homepage-quote-modal' })
      setForm(EMPTY_FORM)
      setState({
        status: 'success',
        message:
          'Thank you — our fire safety engineer will contact you within one working day.',
      })
    } catch {
      setState({
        status: 'error',
        message: `We could not submit your request right now. Please call ${
          settings?.header?.phoneDisplay || '+91 95866 50088'
        } and our team will assist you immediately.`,
      })
    }
  }

  const inputClasses =
    'w-full bg-white border border-[#e5ddcd] text-neutral-800 placeholder-neutral-400 text-xs px-3.5 py-2.5 rounded focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red/30'

  return (
    <div
      className="fixed inset-0 z-[100] flex items-start sm:items-center justify-center overflow-y-auto bg-neutral-950/70 backdrop-blur-sm px-4 py-10"
      onClick={(event) => {
        if (event.target === event.currentTarget) onClose()
      }}
      data-testid="quote-modal-backdrop"
    >
      <div
        aria-labelledby="quote-modal-title"
        aria-modal="true"
        className="relative w-full max-w-lg bg-brand-cream rounded-2xl border border-brand-warmBorder shadow-2xl p-6 sm:p-8"
        role="dialog"
      >
        <button
          aria-label="Close quote form"
          className="absolute top-4 right-4 w-8 h-8 rounded-full bg-white border border-[#e5ddcd] text-neutral-500 hover:text-brand-red hover:border-brand-red/40 flex items-center justify-center transition-colors"
          onClick={onClose}
          type="button"
        >
          <i className="fa-solid fa-xmark text-xs" aria-hidden="true" />
        </button>

        <Kicker>Get a free quote</Kicker>
        <h2
          className="text-xl sm:text-2xl font-extrabold text-neutral-900 tracking-tight mt-2"
          id="quote-modal-title"
        >
          Tell us about your <span className="text-brand-red">facility</span>
        </h2>
        <p className="text-[11px] text-neutral-500 mt-1.5 leading-relaxed">
          Share a few details and our engineering desk will revert with a scope
          and indicative pricing.
        </p>

        {state.status === 'success' ? (
          <div className="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4" role="status">
            <p className="text-xs font-semibold text-emerald-800">{state.message}</p>
            <button
              className="mt-3 bg-brand-red hover:bg-brand-redHover text-white text-xs font-bold px-5 py-2.5 rounded transition-colors"
              onClick={onClose}
              type="button"
            >
              Close
            </button>
          </div>
        ) : (
          <form className="mt-5 space-y-3" onSubmit={handleSubmit}>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label className="block">
                <span className="text-[10px] font-bold uppercase tracking-wider text-neutral-600">
                  Full name
                </span>
                <input
                  className={`mt-1 ${inputClasses}`}
                  onChange={update('name')}
                  placeholder="Your name"
                  ref={firstFieldRef}
                  required
                  type="text"
                  value={form.name}
                />
              </label>
              <label className="block">
                <span className="text-[10px] font-bold uppercase tracking-wider text-neutral-600">
                  Work email
                </span>
                <input
                  className={`mt-1 ${inputClasses}`}
                  onChange={update('email')}
                  placeholder="you@company.com"
                  required
                  type="email"
                  value={form.email}
                />
              </label>
              <label className="block">
                <span className="text-[10px] font-bold uppercase tracking-wider text-neutral-600">
                  Phone
                </span>
                <input
                  className={`mt-1 ${inputClasses}`}
                  onChange={update('phone')}
                  placeholder="+91"
                  type="tel"
                  value={form.phone}
                />
              </label>
              <label className="block">
                <span className="text-[10px] font-bold uppercase tracking-wider text-neutral-600">
                  Facility / company
                </span>
                <input
                  className={`mt-1 ${inputClasses}`}
                  onChange={update('facility')}
                  placeholder="Plant, hospital, tower…"
                  type="text"
                  value={form.facility}
                />
              </label>
            </div>
            <label className="block">
              <span className="text-[10px] font-bold uppercase tracking-wider text-neutral-600">
                What do you need?
              </span>
              <textarea
                className={`mt-1 ${inputClasses} resize-none`}
                onChange={update('message')}
                placeholder="Hydrant system, AMC, Fire NOC audit…"
                required
                rows={3}
                value={form.message}
              />
            </label>

            {state.status === 'error' ? (
              <p className="text-[11px] text-brand-red leading-relaxed" role="alert">
                {state.message}
              </p>
            ) : null}

            <button
              className="w-full bg-brand-red hover:bg-brand-redHover disabled:opacity-70 text-white text-xs font-bold px-6 py-3 rounded shadow transition-colors"
              disabled={state.status === 'submitting'}
              type="submit"
            >
              {state.status === 'submitting' ? 'Sending…' : 'Request my quote'}
            </button>
            <p className="text-[10px] text-neutral-400 text-center">
              Your details are stored securely in our WordPress CRM.
            </p>
          </form>
        )}
      </div>
    </div>
  )
}
