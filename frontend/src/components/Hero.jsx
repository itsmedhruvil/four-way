import { useQuote } from '../context/QuoteContext.jsx'

export default function Hero({ hero }) {
  const { openQuote } = useQuote()

  return (
    <section
      className="relative bg-brand-dark text-white pt-16 pb-28 md:pt-24 md:pb-36 overflow-hidden"
      id="home"
    >
      <div className="absolute inset-0 z-0">
        <img
          alt={hero.backgroundAlt}
          className="w-full h-full object-cover opacity-25 filter blur-[1px] brightness-75 scale-105"
          src={hero.backgroundImage}
        />
        <div className="absolute inset-0 hero-fire-overlay" />
        <div className="absolute inset-0 hero-glow pointer-events-none" />
      </div>

      <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
          <div className="lg:col-span-7 space-y-6">
            <span className="kicker">{hero.kicker}</span>
            <h1 className="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.12] text-white">
              {hero.titleStart}{' '}
              <span className="text-brand-red font-black">{hero.titleHighlight}</span>{' '}
              {hero.titleEnd}
            </h1>
            <p className="text-neutral-300 text-sm sm:text-base leading-relaxed max-w-xl font-normal">
              {hero.description}
            </p>
            <div className="pt-2 flex flex-wrap items-center gap-4">
              <button
                className="bg-brand-red hover:bg-brand-redHover text-white text-xs sm:text-sm font-bold px-7 py-3 rounded shadow-md transition-all transform hover:-translate-y-0.5 inline-flex items-center gap-2"
                onClick={openQuote}
                type="button"
              >
                <span>{hero.primaryCtaLabel}</span>
              </button>
              <a
                className="bg-white/10 hover:bg-white/20 border border-white/25 text-white text-xs sm:text-sm font-semibold px-6 py-3 rounded transition-all backdrop-blur-sm"
                href={hero.secondaryCtaHref}
              >
                {hero.secondaryCtaLabel}
              </a>
            </div>
          </div>

          <div className="lg:col-span-5 flex justify-center lg:justify-end">
            <div className="relative w-full max-w-md bg-neutral-900/60 p-2.5 rounded-2xl border border-white/15 shadow-2xl backdrop-blur-md">
              <div className="relative rounded-xl overflow-hidden aspect-[4/3] group">
                <img
                  alt={hero.mediaAlt}
                  className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                  src={hero.mediaImage}
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
                <div className="absolute inset-0 flex items-center justify-center">
                  <a
                    aria-label="Watch how we engineer fire safety"
                    className="w-14 h-14 rounded-full bg-white/90 hover:bg-white text-brand-red flex items-center justify-center shadow-lg transition-transform duration-300 hover:scale-110 pl-1"
                    href={hero.mediaLink}
                  >
                    <i aria-hidden="true" className="fa-solid fa-play text-lg" />
                  </a>
                </div>
                <div className="absolute bottom-3 left-3 right-3 flex items-center justify-between text-xs text-neutral-300 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-lg border border-white/10">
                  <span className="flex items-center gap-1.5 font-medium text-white">
                    <span className="w-2 h-2 rounded-full bg-brand-red animate-pulse" />
                    {hero.badgeTitle}
                  </span>
                  <span className="text-[11px] text-neutral-300">{hero.badgeMeta}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
