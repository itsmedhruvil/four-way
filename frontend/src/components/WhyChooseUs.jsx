import { SectionTitle } from './SectionTitle.jsx'

function ReasonCard({ reason }) {
  return (
    <div className="bg-white p-5 rounded-lg border border-[#eae3d5] shadow-sm flex items-start gap-4">
      <div className="w-10 h-10 rounded-full bg-red-50 text-brand-red flex items-center justify-center shrink-0 mt-0.5">
        <i aria-hidden="true" className={`${reason.icon} text-sm`} />
      </div>
      <div>
        <h4 className="text-xs font-bold text-neutral-900 uppercase tracking-wide">
          {reason.title}
        </h4>
        <p className="text-[11px] text-neutral-500 mt-1 leading-snug">{reason.description}</p>
      </div>
    </div>
  )
}

export default function WhyChooseUs({ heading, reasons }) {
  const leftReasons = reasons.filter((reason) => reason.column !== 'right')
  const rightReasons = reasons.filter((reason) => reason.column === 'right')

  return (
    <section className="py-24 bg-brand-cream border-t border-b border-[#eee7dd]" id="why-choose-us">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <span className="kicker">{heading.kicker}</span>
          <SectionTitle
            className="text-2xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight mt-2"
            end={heading.titleEnd}
            highlight={heading.titleHighlight}
            start={heading.titleStart}
          />
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
          <div className="lg:col-span-4 space-y-4">
            {leftReasons.map((reason) => (
              <ReasonCard key={reason.title} reason={reason} />
            ))}
          </div>

          <div className="lg:col-span-4 flex justify-center">
            <div className="relative w-full max-w-xs rounded-2xl overflow-hidden shadow-xl border-4 border-white aspect-[3/4]">
              <img
                alt={heading.imageAlt}
                className="w-full h-full object-cover"
                src={heading.image}
              />
              <div className="absolute inset-0 bg-gradient-to-t from-neutral-950/85 via-neutral-950/20 to-transparent" />
              <div className="absolute bottom-5 inset-x-5 text-center text-white">
                <span className="inline-block px-3 py-1 bg-brand-red text-white text-[10px] font-extrabold uppercase tracking-wider rounded-full mb-1">
                  {heading.imageBadge}
                </span>
                <p className="text-xs font-semibold text-neutral-200">{heading.imageCaption}</p>
              </div>
            </div>
          </div>

          <div className="lg:col-span-4 space-y-4">
            {rightReasons.map((reason) => (
              <ReasonCard key={reason.title} reason={reason} />
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}
