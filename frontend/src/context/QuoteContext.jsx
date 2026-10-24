import { createContext, useCallback, useContext, useMemo, useState } from 'react'
import QuoteModal from '../components/QuoteModal.jsx'

const QuoteContext = createContext(null)

export function QuoteProvider({ settings, children }) {
  const [isOpen, setIsOpen] = useState(false)

  const openQuote = useCallback(() => setIsOpen(true), [])
  const closeQuote = useCallback(() => setIsOpen(false), [])

  const value = useMemo(() => ({ openQuote, closeQuote }), [openQuote, closeQuote])

  return (
    <QuoteContext.Provider value={value}>
      {children}
      <QuoteModal open={isOpen} onClose={closeQuote} settings={settings} />
    </QuoteContext.Provider>
  )
}

export function useQuote() {
  const context = useContext(QuoteContext)
  if (!context) {
    throw new Error('useQuote must be used inside a QuoteProvider')
  }
  return context
}
