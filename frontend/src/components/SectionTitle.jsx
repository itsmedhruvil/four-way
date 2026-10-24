export function Kicker({ children, className = '' }) {
  return <span className={`kicker ${className}`.trim()}>{children}</span>
}

export function SectionTitle({
  as: Tag = 'h2',
  start,
  highlight,
  end,
  className = 'text-2xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight',
}) {
  return (
    <Tag className={className}>
      {start}{' '}
      {highlight ? <span className="text-brand-red">{highlight}</span> : null}
      {end ? ` ${end}` : null}
    </Tag>
  )
}
