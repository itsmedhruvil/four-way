import { useState } from 'react'
import { SectionTitle } from './SectionTitle.jsx'

export default function Faq({ heading, faqs }) {
  const [openItems, setOpenItems] = useState(
    () => new Set(faqs.reduce((acc, faq, index) => (faq.open ? [...acc, index] : acc), [])),
  )

  const toggle = (index) => {
    setOpenItems((current) => {
      const next = new Set(current)
      if (next.has(index)) next.delete(index)
      else next.add(index)
      return next
    })
  }

  return (
    <section className="py-24 bg-brand-cream border-t border-b border-[#eee7dd]" id="faq">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-14">
          <span className="kicker">{heading.kicker}</span>
          <SectionTitle
            className="text-2xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight mt-2"
            end={heading.titleEnd}
            highlight={heading.titleHighlight}
            start={heading.titleStart}
          />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-5 text-left">
          {faqs.map((faq, index) => {
            const isOpen = openItems.has(index)
            return (
              <div
                className="bg-white rounded-lg border border-[#ede5d6] p-5 shadow-sm"
                key={faq.question}
              >
                <button
                  aria-controls={`faq-answer-${index}`}
                  aria-expanded={isOpen}
                  className="w-full flex justify-between items-center gap-4 text-left cursor-pointer"
                  onClick={() => toggle(index)}
                  type="button"
                >
                  <h3 className="text-xs sm:text-sm font-bold text-neutral-900">{faq.question}</h3>
                  <span
                    className={`font-bold text-base shrink-0 ${
                      isOpen ? 'text-brand-red' : 'text-neutral-400'
                    }`}
                  >
                    {isOpen ? '−' : '+'}
                  </span>
                </button>
                <p
                  className={`text-xs text-neutral-500 mt-3 leading-relaxed border-t border-gray-100 pt-3 ${
                    isOpen ? '' : 'hidden'
                  }`}
                  id={`faq-answer-${index}`}
                >
                  {faq.answer}
                </p>
              </div>
            )
          })}
        </div>
      </div>
    </section>
  )
}
