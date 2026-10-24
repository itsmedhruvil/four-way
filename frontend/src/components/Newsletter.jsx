import { useState } from 'react'
import { subscribe } from '../lib/api.js'

export default function Newsletter({ newsletter }) {
  const [email, setEmail] = useState('')
  const [state, setState] = useState({ status: 'idle', message: '' })

  const handleSubmit = async (event) => {
    event.preventDefault()
    setState({ status: 'submitting', message: '' })

    try {
      await subscribe(email)
      setEmail('')
      setState({ status: 'success', message: newsletter.successMessage })
    } catch {
      setState({
        status: 'error',
        message: 'Subscription failed. Please try again in a moment.',
      })
    }
  }

  return (
    <section className="bg-[#191b1f] py-14 text-white border-b border-neutral-800">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 text-center space-y-4">
        <h3 className="text-xl sm:text-2xl font-bold tracking-tight text-white">
          {newsletter.title}
        </h3>
        <p className="text-xs text-neutral-400 max-w-md mx-auto">{newsletter.description}</p>
        <form
          className="flex flex-col sm:flex-row items-center justify-center gap-2 max-w-md mx-auto pt-2"
          onSubmit={handleSubmit}
        >
          <input
            aria-label="Email address"
            className="w-full bg-[#24272c] border border-neutral-700 text-white placeholder-neutral-500 text-xs px-4 py-2.5 rounded focus:outline-none focus:border-brand-red"
            onChange={(event) => setEmail(event.target.value)}
            placeholder={newsletter.placeholder}
            required
            type="email"
            value={email}
          />
          <button
            className="w-full sm:w-auto bg-brand-red hover:bg-brand-redHover disabled:opacity-70 text-white text-xs font-bold px-6 py-2.5 rounded transition-colors shrink-0"
            disabled={state.status === 'submitting'}
            type="submit"
          >
            {newsletter.buttonLabel}
          </button>
        </form>
        {state.status !== 'idle' && state.status !== 'submitting' ? (
          <p
            className={`text-[11px] ${
              state.status === 'success' ? 'text-emerald-400' : 'text-brand-red'
            }`}
            role="status"
          >
            {state.message}
          </p>
        ) : null}
      </div>
    </section>
  )
}
