#!/usr/bin/env node
/**
 * Live smoke test for the headless WordPress backend.
 *
 * Usage:
 *   WP_URL=http://localhost:8080 node scripts/verify-live.mjs
 */
import { readFileSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'
import { mergeContent } from '../frontend/src/lib/merge.js'

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..')
const fallback = JSON.parse(
  readFileSync(resolve(root, 'frontend/src/data/site-content.json'), 'utf8'),
)

const base = (process.env.WP_URL || 'http://localhost:8080').replace(/\/+$/, '')

const failures = []
const check = (condition, message) => {
  if (condition) {
    console.log(`  ok   ${message}`)
  } else {
    failures.push(message)
    console.error(`  FAIL ${message}`)
  }
}

const expectedCounts = {
  badges: 4,
  services: 6,
  reasons: 6,
  steps: 4,
  projects: 4,
  testimonials: 2,
  faqs: 8,
  posts: 3,
}

console.log(`Fetching ${base}/wp-json/4way/v1/home`)
const response = await fetch(`${base}/wp-json/4way/v1/home`)

if (!response.ok) {
  console.error(`Homepage endpoint returned HTTP ${response.status}`)
  process.exit(1)
}

const payload = await response.json()
const content = mergeContent(fallback, payload)

check(payload.settings?.hero?.titleStart, 'settings.hero.titleStart is present')
check(
  payload.settings?.hero?.mediaImage?.startsWith('http'),
  'settings.hero.mediaImage resolves to a URL',
)

for (const [collection, expected] of Object.entries(expectedCounts)) {
  const items = payload[collection]
  check(Array.isArray(items) && items.length === expected, `${collection}: ${expected} items`)
  const broken = (Array.isArray(items) ? items : []).filter(
    (item) => !item.title && !item.question && !item.name,
  )
  check(broken.length === 0, `${collection}: every item has a title/question/name`)
}

const servicesWithImages = (payload.services || []).filter((service) => service.image)
check(servicesWithImages.length === 6, 'services: six image URLs')

const projectImages = (payload.projects || []).filter((project) => project.image)
check(projectImages.length === 4, 'projects: four image URLs')

check(
  (payload.posts || []).every((post) => post.image && post.category),
  'posts: image + category for every blog card',
)

check(content.settings.hero.titleStart === payload.settings.hero.titleStart, 'settings merge into content model')
check(content.services.length === 6, 'merged content exposes six services')

console.log('\nContact endpoint')
const origin = process.env.FRONTEND_ORIGIN || 'http://localhost:5173'
const contactResponse = await fetch(`${base}/wp-json/4way/v1/contact`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', Origin: origin },
  body: JSON.stringify({
    name: 'Smoke Test',
    email: 'smoke-test@example.com',
    phone: '+91 90000 00000',
    facility: 'Verification Plant',
    message: 'Automated verification of the quote endpoint.',
    source: 'verify-live',
  }),
})

check(
  [200, 201].includes(contactResponse.status),
  `POST /contact accepted (HTTP ${contactResponse.status})`,
)
check(
  contactResponse.headers.get('access-control-allow-origin') === origin,
  'CORS allows the React origin',
)
const contactBody = await contactResponse.json()
check(Boolean(contactBody.id), 'stored lead returns an id')

console.log('\nSubscribe endpoint')
const subscribeResponse = await fetch(`${base}/wp-json/4way/v1/subscribe`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', Origin: origin },
  body: JSON.stringify({ email: 'smoke-test@example.com' }),
})

check(
  [200, 201].includes(subscribeResponse.status),
  `POST /subscribe accepted (HTTP ${subscribeResponse.status})`,
)

console.log('')
if (failures.length) {
  console.error(`${failures.length} check(s) failed`)
  process.exit(1)
}

console.log('All live checks passed.')
