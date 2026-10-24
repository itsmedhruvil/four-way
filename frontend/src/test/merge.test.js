import { describe, expect, it } from 'vitest'
import { deepMerge, mergeContent } from '../lib/merge.js'

describe('content merging', () => {
  it('keeps design copy when a CMS field is blank', () => {
    const merged = deepMerge({ title: 'Design title', body: 'Design body' }, { title: '', body: 'CMS body' })
    expect(merged).toEqual({ title: 'Design title', body: 'CMS body' })
  })

  it('replaces arrays instead of merging them by index', () => {
    const merged = deepMerge({ nav: [{ label: 'A' }, { label: 'B' }] }, { nav: [{ label: 'C' }] })
    expect(merged.nav).toEqual([{ label: 'C' }])
  })

  it('falls back to the bundled content when WordPress is unreachable', () => {
    const defaults = { settings: { hero: { titleStart: 'Bundled' } }, services: [{ title: 'One' }] }
    expect(mergeContent(defaults, null)).toBe(defaults)
    expect(mergeContent(defaults, undefined)).toBe(defaults)
  })

  it('does not blank a collection when WordPress returns no rows', () => {
    const defaults = {
      settings: { hero: { titleStart: 'Bundled' }, header: { ctaLabel: 'Quote' } },
      services: [{ title: 'One' }],
    }
    const merged = mergeContent(defaults, { settings: { hero: { titleStart: 'Live' } }, services: [] })

    expect(merged.settings.hero.titleStart).toBe('Live')
    expect(merged.settings.header.ctaLabel).toBe('Quote')
    expect(merged.services).toEqual(defaults.services)
  })

  it('uses live collections when WordPress returns rows', () => {
    const defaults = { settings: {}, projects: [{ title: 'Old' }] }
    const merged = mergeContent(defaults, { projects: [{ title: 'New' }] })

    expect(merged.projects).toEqual([{ title: 'New' }])
  })
})
