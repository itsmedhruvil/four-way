import { SectionTitle } from './SectionTitle.jsx'

export default function Projects({ heading, projects }) {
  return (
    <section className="py-24 bg-brand-cream border-t border-b border-[#eee7dd]" id="projects">
      <span className="sr-only" id="sectors">
        Sectors we serve
      </span>
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

        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          {projects.map((project) => (
            <div
              className="group bg-white rounded-xl overflow-hidden border border-[#ede5d6] shadow-sm hover:shadow-md transition-shadow"
              key={project.title}
            >
              <div className="relative aspect-[16/10] overflow-hidden">
                <img
                  alt={project.imageAlt || project.title}
                  className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                  src={project.image}
                />
                <a
                  aria-label={`View project: ${project.title}`}
                  className="absolute top-4 right-4 w-9 h-9 rounded bg-brand-red text-white flex items-center justify-center text-sm shadow hover:bg-neutral-900 transition-colors"
                  href={project.href || '#contact'}
                >
                  <i
                    aria-hidden="true"
                    className="fa-solid fa-arrow-up-right-from-square text-xs"
                  />
                </a>
              </div>
              <div className="p-6">
                <span className="text-[10px] font-bold uppercase tracking-wider text-brand-red">
                  {project.category}
                </span>
                <h3 className="text-base font-bold text-neutral-900 mt-1 group-hover:text-brand-red transition-colors">
                  {project.title}
                </h3>
                <p className="text-xs text-neutral-500 mt-2 leading-relaxed">
                  {project.description}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
