import { SectionTitle } from './SectionTitle.jsx'

export default function Process({ heading, steps }) {
  return (
    <section className="py-24 bg-[#faf7f2]" id="process">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <span className="kicker">{heading.kicker}</span>
          <SectionTitle
            className="text-2xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight mt-2 leading-tight"
            end={heading.titleEnd}
            highlight={heading.titleHighlight}
            start={heading.titleStart}
          />
          <p className="text-xs sm:text-sm text-neutral-500 mt-3 leading-relaxed">
            {heading.description}
          </p>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          {steps.map((step) => (
            <div className="text-center group" key={step.number}>
              <div className="text-4xl sm:text-5xl font-extrabold text-brand-red/90 font-serif tracking-tight mb-3 group-hover:scale-105 transition-transform">
                {step.number}
              </div>
              <div className="w-12 h-12 mx-auto rounded-full bg-red-50 border border-red-100 text-brand-red flex items-center justify-center mb-3">
                <i aria-hidden="true" className={`${step.icon} text-sm`} />
              </div>
              <h3 className="text-sm font-bold text-neutral-900 uppercase tracking-wide mb-1">
                {step.title}
              </h3>
              <p className="text-xs text-neutral-500 leading-relaxed px-2">{step.description}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
