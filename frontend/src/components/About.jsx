import { SectionTitle } from './SectionTitle.jsx'

export default function About({ about }) {
  return (
    <section
      className="pt-24 pb-20 bg-brand-cream border-b border-[#eee7dd]"
      id="about"
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between pb-8">
          <span className="kicker">{about.kicker}</span>
          <div className="text-brand-red/80 text-3xl">
            <i aria-hidden="true" className="fa-solid fa-fire-flame-curved" />
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
          <div className="lg:col-span-3 pt-2">
            <div className="text-5xl sm:text-6xl font-black text-neutral-900 tracking-tight flex items-baseline">
              {about.years}
              <span className="text-brand-red">+</span>
            </div>
            <p className="text-xs font-bold text-neutral-500 uppercase tracking-widest mt-1">
              {about.yearsLabel}
            </p>
            <p className="text-[11px] text-neutral-400 mt-2 font-medium">{about.yearsNote}</p>
          </div>

          <div className="lg:col-span-9 space-y-6">
            <SectionTitle
              className="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-neutral-900 tracking-tight leading-snug"
              end={about.titleEnd}
              highlight={about.titleHighlight}
              start={about.titleStart}
            />
            <p className="text-sm sm:text-base font-medium text-neutral-700 leading-relaxed">
              {about.lead}
            </p>
            <p className="text-xs sm:text-sm text-neutral-500 leading-relaxed max-w-3xl">
              {about.body}
            </p>
            <div className="pt-2">
              <a
                className="inline-flex items-center justify-center bg-brand-red hover:bg-brand-redHover text-white text-xs font-bold px-6 py-2.5 rounded shadow transition-all"
                href={about.ctaHref}
              >
                {about.ctaLabel}
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
