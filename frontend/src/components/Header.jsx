import { useState } from 'react'
import { useQuote } from '../context/QuoteContext.jsx'

export default function Header({ settings }) {
  const { brand, navigation, header } = settings
  const { openQuote } = useQuote()
  const [mobileOpen, setMobileOpen] = useState(false)

  return (
    <header
      className="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#eee8dd] shadow-sm transition-all duration-300"
      data-testid="site-header"
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <a
          aria-label={`${brand.name} Home`}
          className="flex items-center gap-3 group focus:outline-none"
          href="#home"
        >
          <img
            alt={brand.logoAlt || brand.name}
            className="h-10 md:h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
            src={brand.logo}
          />
        </a>

        <nav
          aria-label="Main Navigation"
          className="hidden lg:flex items-center space-x-7 text-xs font-semibold tracking-wider text-neutral-800 uppercase"
        >
          {navigation.map((item, index) => (
            <a
              className={
                index === 0
                  ? 'text-brand-red font-bold transition-colors'
                  : 'hover:text-brand-red transition-colors'
              }
              href={item.href}
              key={`${item.label}-${item.href}`}
            >
              {item.label === 'SERVICES' ? (
                <span className="flex items-center gap-1.5">
                  {item.label}
                  <i
                    aria-hidden="true"
                    className="fa-solid fa-chevron-down text-[9px] text-neutral-400 group-hover:rotate-180 transition-transform"
                  />
                </span>
              ) : (
                item.label
              )}
            </a>
          ))}
        </nav>

        <div className="hidden sm:flex items-center gap-5">
          <a
            className="flex items-center gap-2 text-xs font-bold text-neutral-800 hover:text-brand-red transition-colors"
            href={header.phoneHref}
          >
            <span className="w-7 h-7 rounded-full bg-red-50 text-brand-red flex items-center justify-center text-xs">
              <i aria-hidden="true" className="fa-solid fa-phone" />
            </span>
            <span>{header.phoneDisplay}</span>
          </a>
          <button
            className="bg-brand-red hover:bg-brand-redHover text-white text-xs font-bold px-5 py-2.5 rounded-md shadow-sm transition-all duration-200 transform hover:-translate-y-0.5"
            onClick={openQuote}
            type="button"
          >
            {header.ctaLabel}
          </button>
        </div>

        <div className="lg:hidden flex items-center">
          <button
            aria-controls="mobile-nav"
            aria-expanded={mobileOpen}
            aria-label="Toggle menu"
            className="text-neutral-700 hover:text-brand-red p-2"
            onClick={() => setMobileOpen((open) => !open)}
            type="button"
          >
            <i aria-hidden="true" className="fa-solid fa-bars text-xl" />
          </button>
        </div>
      </div>

      <div
        className={`${
          mobileOpen ? '' : 'hidden'
        } lg:hidden bg-white border-b border-gray-200 px-6 py-4 space-y-3`}
        id="mobile-nav"
      >
        {navigation.map((item, index) => (
          <a
            className={
              index === 0
                ? 'block text-sm font-semibold text-brand-red'
                : 'block text-sm font-semibold text-neutral-700 hover:text-brand-red'
            }
            href={item.href}
            key={`mobile-${item.label}-${item.href}`}
            onClick={() => setMobileOpen(false)}
          >
            {item.label}
          </a>
        ))}
        <div className="pt-3 border-t border-gray-100 flex flex-col gap-3">
          <a
            className="text-xs font-bold text-neutral-800 flex items-center gap-2"
            href={header.phoneHref}
          >
            <i aria-hidden="true" className="fa-solid fa-phone text-brand-red" />
            {header.phoneDisplay}
          </a>
          <button
            className="text-center bg-brand-red text-white text-xs font-bold py-2 rounded-md"
            onClick={() => {
              setMobileOpen(false)
              openQuote()
            }}
            type="button"
          >
            {header.ctaLabel}
          </button>
        </div>
      </div>
    </header>
  )
}
