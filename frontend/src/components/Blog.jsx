import { SectionTitle } from './SectionTitle.jsx'

export default function Blog({ heading, posts }) {
  return (
    <section className="py-24 bg-[#faf7f2]" id="blog">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-12 gap-4">
          <div>
            <span className="kicker">{heading.kicker}</span>
            <SectionTitle
              className="text-2xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight mt-1"
              end={heading.titleEnd}
              highlight={heading.titleHighlight}
              start={heading.titleStart}
            />
          </div>
          <a
            className="inline-flex self-start sm:self-auto items-center bg-brand-red hover:bg-brand-redHover text-white text-xs font-bold px-5 py-2.5 rounded transition-all"
            href={heading.ctaHref}
          >
            {heading.ctaLabel}
          </a>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {posts.map((post) => (
            <article
              className="bg-white rounded-xl overflow-hidden border border-[#ede5d6] shadow-sm flex flex-col justify-between group"
              key={post.slug || post.title}
            >
              <div>
                <div className="relative aspect-[16/10] overflow-hidden">
                  <img
                    alt={post.imageAlt || post.title}
                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                    src={post.image}
                  />
                  <span className="absolute top-3 left-3 bg-neutral-900/80 backdrop-blur-sm text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded">
                    {post.category}
                  </span>
                </div>
                <div className="p-5">
                  <div className="text-[11px] text-neutral-400 font-medium mb-1.5 flex items-center gap-1.5">
                    <i aria-hidden="true" className="fa-regular fa-calendar" />
                    {post.date}
                  </div>
                  <h3 className="text-sm font-bold text-neutral-900 group-hover:text-brand-red transition-colors leading-snug">
                    {post.title}
                  </h3>
                  <p className="text-xs text-neutral-500 mt-2 leading-relaxed">{post.excerpt}</p>
                </div>
              </div>
              <div className="px-5 pb-5 pt-2">
                <a
                  className="text-xs font-bold text-brand-red hover:underline inline-flex items-center gap-1"
                  href={post.href || '#contact'}
                >
                  Learn more <i aria-hidden="true" className="fa-solid fa-arrow-right text-[10px]" />
                </a>
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  )
}
