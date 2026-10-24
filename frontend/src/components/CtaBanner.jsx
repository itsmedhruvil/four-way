import { useQuote } from '../context/QuoteContext.jsx'

export default function CtaBanner({ cta }) {
  const { openQuote } = useQuote()

  return (
    <section className="relative bg-brand-dark py-20 text-white overflow-hidden">
      <div className="absolute inset-0 z-0">
        <img
          alt={cta.backgroundAlt}
          className="w-full h-full object-cover opacity-20 filter brightness-50"
          src={cta.backgroundImage}
        />
        <div className="absolute inset-0 bg-gradient-to-r from-neutral-950 via-neutral-900/90 to-neutral-950" />
      </div>
      <div className="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
        <span className="kicker text-brand-red">{cta.kicker}</span>
        <h2 className="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
          {cta.titleStart}{' '}
          <br className="hidden sm:inline" />
          {cta.titleLead} <span className="text-brand-red">{cta.titleHighlight}</span>
          {cta.titleEnd ? ` ${cta.titleEnd}` : null}
        </h2>
        <p className="text-xs sm:text-sm text-neutral-300 max-w-xl mx-auto leading-relaxed">
          {cta.description}
        </p>
        <div className="pt-2 flex flex-wrap justify-center gap-4">
          <button
            className="bg-brand-red hover:bg-brand-redHover text-white text-xs sm:text-sm font-bold px-7 py-3 rounded shadow-md transition-transform transform hover:-translate-y-0.5"
            onClick={openQuote}
            type="button"
          >
            {cta.primaryCtaLabel}
          </button>
          <a
            className="bg-white hover:bg-neutral-100 text-neutral-900 text-xs sm:text-sm font-bold px-7 py-3 rounded shadow transition-all"
            href={cta.secondaryCtaHref}
          >
            {cta.secondaryCtaLabel}
          </a>
        </div>
      </div>
    </section>
  )
}
