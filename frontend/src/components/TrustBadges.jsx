export default function TrustBadges({ badges }) {
  return (
    <div className="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-14 sm:-mt-16">
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {badges.map((badge) => (
          <div
            className="bg-white rounded-lg p-5 shadow-lg border border-[#ede7dc] flex items-start gap-3.5 hover:shadow-xl transition-shadow"
            key={badge.title}
          >
            <div className="w-10 h-10 rounded-full bg-red-50 text-brand-red flex items-center justify-center shrink-0 mt-0.5">
              <i aria-hidden="true" className={`${badge.icon} text-base`} />
            </div>
            <div>
              <h4 className="text-xs font-bold text-neutral-900 tracking-wide uppercase">
                {badge.title}
              </h4>
              <p className="text-[11px] text-neutral-500 mt-1 leading-snug">
                {badge.description}
              </p>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
