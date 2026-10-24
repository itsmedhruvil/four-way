#!/usr/bin/env node
/**
 * Copy the single source-of-truth content model into the WordPress plugin so
 * the seeder and the React fallback can never drift apart.
 *
 * Usage: node scripts/export-wp-defaults.mjs
 */
import { copyFileSync, mkdirSync, readFileSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..')
const source = resolve(root, 'frontend/src/data/site-content.json')
const target = resolve(
  root,
  'wordpress/wp-content/mu-plugins/4way-core/content/defaults.json',
)

const content = JSON.parse(readFileSync(source, 'utf8'))

const required = ['settings', 'badges', 'services', 'reasons', 'steps', 'projects', 'testimonials', 'faqs', 'posts']
const missing = required.filter((key) => !(key in content))
if (missing.length) {
  console.error(`Content model is missing: ${missing.join(', ')}`)
  process.exit(1)
}

mkdirSync(dirname(target), { recursive: true })
copyFileSync(source, target)

console.log(`Synced ${source}`)
console.log(`      → ${target}`)
