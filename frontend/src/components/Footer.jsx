export default function Footer({ settings }) {
  const { brand, footer } = settings

  return (
    <footer className="bg-[#121316] text-neutral-400 pt-16 pb-12 text-xs" id="contact">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 pb-12 border-b border-neutral-800">
          <div className="lg:col-span-4 space-y-4">
            <img
              alt={brand.logoAlt || brand.name}
              className="h-12 w-auto object-contain bg-white p-1 rounded-sm shadow-sm"
              src={brand.logo}
            />
            <p className="text-neutral-400 text-xs leading-relaxed max-w-sm">
              {footer.description}
            </p>
            <div className="pt-2">
              <p className="text-[11px] font-bold text-neutral-300 uppercase tracking-wider mb-2">
                Follow Us
              </p>
              <div className="flex items-center space-x-3 text-neutral-400">
                {footer.socials.map((social) => (
                  <a
                    aria-label={social.label}
                    className="w-8 h-8 rounded bg-neutral-800 hover:bg-brand-red hover:text-white flex items-center justify-center transition-colors"
                    href={social.href}
                    key={social.label}
                  >
                    <i aria-hidden="true" className={social.icon} />
                  </a>
                ))}
              </div>
            </div>
          </div>

          {footer.columns.map((column, index) => (
            <div
              className={`${index === 0 ? 'lg:col-span-2' : 'lg:col-span-3'} space-y-3`}
              key={column.title}
            >
              <h4 className="text-xs font-bold text-white uppercase tracking-wider">
                {column.title}
              </h4>
              <ul className="space-y-2">
                {column.links.map((link) => (
                  <li key={`${column.title}-${link.label}`}>
                    <a className="hover:text-white transition-colors" href={link.href}>
                      {link.label}
                    </a>
                  </li>
                ))}
              </ul>
            </div>
          ))}

          <div className="lg:col-span-3 space-y-4">
            <div>
              <h4 className="text-xs font-bold text-white uppercase tracking-wider mb-2">
                Get in Touch
              </h4>
              <p className="text-neutral-400 leading-relaxed text-[11px] rich-text">
                {footer.address}
              </p>
              <p className="mt-2 text-[11px]">
                <a className="text-neutral-300 hover:text-brand-red" href={`mailto:${footer.email}`}>
                  {footer.email}
                </a>
                <br />
                <a className="text-neutral-300 hover:text-brand-red" href={footer.phoneHref}>
                  {footer.phoneDisplay}
                </a>
              </p>
            </div>
            <div>
              <h4 className="text-xs font-bold text-white uppercase tracking-wider mb-1">
                Working Hours
              </h4>
              <p className="text-[11px] text-neutral-400 rich-text">{footer.hours}</p>
            </div>
          </div>
        </div>

        <div className="pt-8 flex flex-col sm:flex-row items-center justify-between text-neutral-500 text-[11px] gap-4">
          <div>{footer.copyright}</div>
          <div className="flex items-center space-x-6">
            {footer.legalLinks.map((link) => (
              <a
                className="hover:text-neutral-400 transition-colors"
                href={link.href}
                key={link.label}
              >
                {link.label}
              </a>
            ))}
          </div>
        </div>
      </div>
    </footer>
  )
}
