import { SectionTitle } from './SectionTitle.jsx'

export default function Services({ heading, services }) {
  return (
    <section className="py-24 bg-[#faf7f2]" id="services">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-14">
          <span className="kicker">{heading.kicker}</span>
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end mt-2">
            <div className="lg:col-span-6">
              <SectionTitle
                className="text-2xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight leading-tight"
                end={heading.titleEnd}
                highlight={heading.titleHighlight}
                start={heading.titleStart}
              />
            </div>
            <div className="lg:col-span-6">
              <p className="text-xs sm:text-sm text-neutral-500 leading-relaxed">
                {heading.description}
              </p>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {services.map((service) => (
            <div
              className="group relative rounded-xl overflow-hidden aspect-[4/3] shadow-md border border-[#ede5d6]"
              key={service.slug || service.title}
            >
              <img
                alt={service.imageAlt || service.title}
                className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                src={service.image}
              />
              <div className="absolute inset-0 service-card-overlay" />
              <a
                aria-label={`Learn about ${service.title}`}
                className="absolute top-4 right-4 w-9 h-9 rounded bg-brand-red text-white flex items-center justify-center text-sm shadow hover:bg-neutral-900 transition-colors"
                href={service.href || '#contact'}
              >
                <i aria-hidden="true" className="fa-solid fa-arrow-up-right-from-square text-xs" />
              </a>
              <div className="absolute bottom-0 inset-x-0 p-5 text-white">
                <h3 className="text-base font-bold text-white group-hover:text-red-300 transition-colors">
                  {service.title}
                </h3>
                <p className="text-[11px] text-neutral-300 mt-1 line-clamp-2">
                  {service.description}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
