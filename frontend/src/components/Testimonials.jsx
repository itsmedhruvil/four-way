import { SectionTitle } from './SectionTitle.jsx'

export default function Testimonials({ heading, testimonials }) {
  return (
    <section className="py-24 bg-[#faf7f2]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
          <div className="lg:col-span-5">
            <div className="relative max-w-md mx-auto">
              <div className="rounded-2xl overflow-hidden aspect-[4/5] shadow-lg border border-[#ede4d4]">
                <img
                  alt={heading.imageAlt}
                  className="w-full h-full object-cover"
                  src={heading.image}
                />
              </div>
              <div className="absolute -bottom-6 left-6 right-6 bg-white p-3.5 rounded-xl shadow-lg border border-[#ede4d4] flex items-center gap-3">
                <div className="flex -space-x-2">
                  {(heading.avatars || []).map((avatar, index) => (
                    <img
                      alt=""
                      aria-hidden="true"
                      className="w-8 h-8 rounded-full border-2 border-white object-cover"
                      key={`avatar-${index}`}
                      src={avatar}
                    />
                  ))}
                </div>
                <div className="text-[11px] leading-tight">
                  <p className="font-bold text-neutral-900">{heading.trustTitle}</p>
                  <p className="text-neutral-500">{heading.trustSubtitle}</p>
                </div>
              </div>
            </div>
          </div>

          <div className="lg:col-span-7 space-y-6 pt-6 lg:pt-0">
            <div>
              <span className="kicker">{heading.kicker}</span>
              <SectionTitle
                className="text-2xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight mt-2 leading-tight"
                end={heading.titleEnd}
                highlight={heading.titleHighlight}
                start={heading.titleStart}
              />
              <p className="text-xs sm:text-sm text-neutral-500 mt-2">{heading.description}</p>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
              {testimonials.map((testimonial) => (
                <div
                  className="bg-white p-5 rounded-xl border border-[#ede5d6] shadow-sm flex flex-col justify-between relative"
                  key={testimonial.name}
                >
                  <div className="text-brand-red/20 absolute top-4 right-4 text-4xl font-serif">“</div>
                  <div>
                    <div className="flex text-amber-400 text-xs mb-3">
                      {Array.from({ length: Number(testimonial.rating) || 5 }).map((_, index) => (
                        <i aria-hidden="true" className="fa-solid fa-star" key={index} />
                      ))}
                    </div>
                    <p className="text-xs text-neutral-600 leading-relaxed italic">
                      &quot;{testimonial.quote}&quot;
                    </p>
                  </div>
                  <div className="flex items-center gap-3 pt-4 mt-4 border-t border-gray-100">
                    <img
                      alt={testimonial.name}
                      className="w-9 h-9 rounded-full object-cover border border-red-200"
                      src={testimonial.avatar}
                    />
                    <div>
                      <h4 className="text-xs font-bold text-neutral-900">{testimonial.name}</h4>
                      <p className="text-[10px] text-neutral-500">{testimonial.role}</p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
