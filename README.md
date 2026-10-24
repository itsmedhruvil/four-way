# 4Way Safety Solutions — React + Headless WordPress

The original single-file HTML homepage has been converted into a production-ready
**React (Vite + Tailwind) frontend** backed by a **headless WordPress content model**.
WordPress owns the copy, imagery, collections and enquiries; React renders the exact
same visual design and hydrates it from a single REST endpoint.

```
Browser ──► React SPA (Vite, Tailwind, Font Awesome)
                │  GET /wp-json/4way/v1/home
                │  POST /wp-json/4way/v1/contact
                │  POST /wp-json/4way/v1/subscribe
                ▼
          WordPress (headless)
            ├── Custom post types + custom fields (meta boxes, REST exposed)
            ├── Homepage settings screen (every headline / CTA / footer link)
            ├── Custom tables: wp_4way_leads, wp_4way_subscribers
            └── MySQL
```

Content is authored once in `frontend/src/data/site-content.json`. That file is the
React fallback **and** is mirrored into the WordPress plugin by
`npm run wp:defaults`, so the CMS seeder and the offline fallback can never drift.

---

## Project structure

```
.
├── docker-compose.yml          # WordPress + MySQL + wp-cli + Vite dev server
├── docker/
│   ├── wp-setup.sh             # install WP, activate theme, seed, set front page
│   └── nginx.conf              # SPA config for the production frontend image
├── scripts/
│   ├── export-wp-defaults.mjs  # JSON → WordPress defaults.json
│   └── verify-live.mjs         # REST/DB smoke test against a running WordPress
├── frontend/
│   ├── Dockerfile              # multi-stage build → nginx
│   ├── index.html              # fonts + Font Awesome, same as the design
│   ├── tailwind.config.js      # brand palette / typography
│   └── src/
│       ├── App.jsx             # composes the homepage sections
│       ├── components/         # Header, Hero, TrustBadges, About, Services,
│       │                       # WhyChooseUs, Process, Projects, Testimonials,
│       │                       # Faq, CtaBanner, Blog, Newsletter, Footer,
│       │                       # QuoteModal, SectionTitle
│       ├── context/            # QuoteModal provider (CTA → lead form)
│       ├── data/               # source-of-truth content JSON
│       ├── hooks/              # useSiteContent (fallback → live WP payload)
│       ├── lib/                # api client + content merge rules
│       └── test/               # unit tests + live WordPress integration test
└── wordpress/wp-content/
    ├── mu-plugins/
    │   ├── 4way-core.php       # loader (always active)
    │   └── 4way-core/
    │       ├── content/defaults.json   # generated from the frontend JSON
    │       └── includes/
    │           ├── helpers.php      # settings access, meta helpers, plain text
    │           ├── post-types.php   # 7 CPTs + sector taxonomy
    │           ├── fields.php       # meta registration, meta boxes, sanitising
    │           ├── settings.php     # auto-generated homepage settings screen
    │           ├── leads.php        # custom tables + CRM admin + CSV export
    │           ├── rest.php         # /4way/v1/* endpoints
    │           ├── headless.php     # front-end redirect, CORS, headless options
    │           └── seed.php         # WP-CLI: wp 4way seed / wp 4way status
    └── themes/4way-headless/   # minimal shell theme + fallback page
```

---

## Quick start (Docker)

Requirements: Docker Desktop / Docker Engine with Compose v2. Ports `8080` and `5173`
must be free.

```bash
cp .env.example .env

# 1. Start the database + WordPress
docker compose up -d db wordpress

# 2. Install WordPress, activate the headless theme, seed all content,
#    import images and register the React app as the site homepage
docker compose run --rm wpcli sh /scripts/wp-setup.sh

# 3. Start the React dev server (hot reload)
docker compose up -d frontend
```

Then open:

| URL | What |
| --- | --- |
| http://localhost:5173 | React homepage |
| http://localhost:8080/wp-admin | WordPress admin (`admin` / `4way-admin-password`) |
| http://localhost:8080/wp-json/4way/v1/home | Aggregated homepage payload |

The seeder is idempotent — re-run it any time:

```bash
docker compose run --rm wpcli wp 4way seed              # add missing items
docker compose run --rm wpcli wp 4way seed --force      # refresh existing items
docker compose run --rm wpcli wp 4way status            # counts + CRM totals
```

`wp 4way seed` also:

* creates the **Home** page and sets `show_on_front = page`, `page_on_front = Home`
  (so the React app is the homepage),
* sets pretty permalinks (`/%postname%/`),
* stores the full homepage copy in the `fourway_settings` option,
* removes WordPress' "Hello world!" / "Sample Page" demo content,
* writes the React app URL to `fourway_frontend_url`.

### Why WordPress never shows its own homepage

The mu-plugin redirects every public WordPress page view (`template_redirect`) to the
React app URL, while leaving `/wp-admin`, `/wp-json`, `robots.txt`, sitemaps, feeds and
`?fourway_preview=1` on WordPress. Turn the redirect off under
**4Way Safety → Homepage Content → Headless front end** if you want to preview a
WordPress template.

### Local development without Docker

Frontend only (uses the bundled content, no WordPress required):

```bash
cd frontend
npm install
cp .env.example .env      # VITE_WP_URL=http://localhost:8080
npm run dev               # http://localhost:5173
```

Against any WordPress install (Local, WP Engine, cPanel…):

1. Copy `wordpress/wp-content/mu-plugins/*` into `wp-content/mu-plugins/`.
2. Copy `wordpress/wp-content/themes/4way-headless` into `wp-content/themes/` and activate it.
3. Run `wp 4way seed --import-images --frontend-url=https://your-react-app.example.com`.
4. Set `VITE_WP_URL=https://cms.example.com` for the frontend build.

---

## Content model

### Homepage settings (option `fourway_settings`)

**4Way Safety → Homepage Content** renders every field automatically from the shipped
defaults: brand, navigation, header, hero, about, every section heading, the dark CTA,
blog heading, newsletter bar, footer (address, phones, socials, link columns, legal
links) and SEO title/description. List fields (navigation, footer columns, socials,
avatars) accept JSON arrays.

### Collections (custom post types)

| Post type | Admin menu | Title field | Custom fields (`_fourway_*`) | Frontend section |
| --- | --- | --- | --- | --- |
| `trust_badge` | Trust Badges | Badge title | `icon`, `description` | 4-up badge row under the hero |
| `service` | Services | Service name | `description`, `href`, `image_url`, `image_alt` | 6-card "What we do" grid |
| `reason` | Why Choose Us Cards | Card title | `icon`, `description`, `column` (left/right) | flanking cards around the centre image |
| `process_step` | Process Steps | Step title | `number`, `icon`, `description` | 01–04 workflow strip |
| `project` | Projects | Project title | `category`, `description`, `href`, `image_url`, `image_alt` | recent-work gallery |
| `testimonial` | Testimonials | Client name | `role`, `quote`, `rating` (1–5), `avatar_url` | testimonial cards |
| `faq` | FAQs | Question | `answer`, `open` (expanded by default) | 2-column accordion |
| `post` | Posts | Article title | `_fourway_image_url` (fallback featured image) | 3-card blog strip |
| `page` | Pages | — | — | Front page record ("Home") |

Ordering is controlled by **Page Attributes → Order** (`menu_order`) for every
collection; blog cards are ordered newest first. Images use the **featured image**
first and fall back to the `image_url` field. A `sector` taxonomy is registered for
projects/services so the SECTORS navigation can grow into archive pages.

No ACF or other plugin is required: fields are registered with `register_post_meta()`
(so they are available in the WordPress REST API too) and edited through native meta
boxes with nonce + capability + sanitisation checks.

---

## REST API

Base: `/wp-json/4way/v1`

| Method | Route | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/home` | public | Everything the homepage needs in one request |
| GET | `/settings` | public | Homepage settings only |
| POST | `/contact` | public, throttled | Store a quote request |
| POST | `/subscribe` | public, throttled | Store a newsletter subscriber |

`GET /home` returns:

```jsonc
{
  "settings": { "brand": {…}, "hero": {…}, "footer": {…}, "seo": {…} },
  "badges":       [{ "icon": "fa-solid fa-shield-halved", "title": "Licensed & Certified", "description": "…" }],
  "services":     [{ "slug": "…", "title": "…", "description": "…", "image": "https://…", "imageAlt": "…", "href": "#contact" }],
  "reasons":      [{ "icon": "…", "title": "…", "description": "…", "column": "left" }],
  "steps":        [{ "number": "01", "icon": "…", "title": "…", "description": "…" }],
  "projects":     [{ "category": "Industrial • Gujarat GIDC", "title": "…", "image": "https://…", … }],
  "testimonials": [{ "quote": "…", "name": "…", "role": "…", "rating": 5, "avatar": "…" }],
  "faqs":         [{ "question": "…", "answer": "…", "open": true }],
  "posts":        [{ "slug": "…", "category": "…", "date": "August 15, 2024", "title": "…", "excerpt": "…", "image": "…", "href": "#contact" }]
}
```

WordPress returns HTML-encoded titles (`&#038;`); the plugin decodes them so the React
components receive plain text.

Contact request:

```bash
curl -X POST http://localhost:8080/wp-json/4way/v1/contact \
  -H 'Content-Type: application/json' \
  -H 'Origin: http://localhost:5173' \
  -d '{"name":"Anita Shah","email":"anita@example.com","phone":"+91 99999 00000",
       "facility":"Vapi GIDC","message":"Hydrant AMC quote"}'
# 201 {"ok":true,"id":1,"message":"Thank you — our fire safety engineer will contact you within one working day."}
```

`/contact` requires `name`, a valid `email` and `message`; `/subscribe` requires a valid
`email` and is idempotent per address. Both endpoints are IP-throttled through
transients (8 / 15 requests per 10 minutes) and sanitise every field. A new lead also
emails the site admin (filter `fourway_send_lead_email` to disable).

CORS for the React origin is added through `allowed_http_origins` using the React app
URL plus any origins listed under **Headless front end → Extra CORS origins**.


---

## Custom database tables

Created with `dbDelta()` on first load (`fourway_db_version` option) and managed from
**4Way Safety → Quote Requests / Subscribers**:

```sql
CREATE TABLE wp_4way_leads (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  created_at datetime NOT NULL,
  name varchar(190) NOT NULL,
  email varchar(190) NOT NULL,
  phone varchar(60) NOT NULL,
  facility varchar(190) NOT NULL,
  message text NOT NULL,
  source varchar(100) NOT NULL,
  status varchar(20) NOT NULL DEFAULT 'new',
  ip_hash char(64) NOT NULL,          -- sha256 of the request IP, never the IP itself
  PRIMARY KEY (id), KEY created_at (created_at), KEY email (email)
);

CREATE TABLE wp_4way_subscribers (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  email varchar(190) NOT NULL,
  source varchar(100) NOT NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY (id), UNIQUE KEY email (email)
);
```

The Quote Requests screen shows the received time, contact details, facility and
requirement, with **Mark contacted**, **Delete** and an email notification per lead.
Subscribers can be exported as CSV.

---

## Editing content

1. **Global copy / CTAs / footer / SEO** → *4Way Safety → Homepage Content*.
2. **Cards** → the matching collection menu (Services, Projects, Testimonials, FAQs,
   Process Steps, Trust Badges, Why Choose Us Cards). Title = card heading, the meta
   box below the editor holds description/icon/link/image. Set *Order* to control the
   sequence.
3. **Images** → set a featured image, or paste a URL in *Image URL*. The REST payload
   always returns one `image` URL so React needs no branching.
4. **Blog** → normal posts: featured image, excerpt, category; the three newest posts
   are shown.
5. Frontend caching: React fetches once per page load. Use a caching plugin / CDN in
   front of `/wp-json/4way/v1/home` if traffic requires it.

The React app also works with **no WordPress at all**: `src/data/site-content.json`
renders the complete designed homepage, and failed requests silently fall back to it.

---

## Setup as the homepage (what was wired up)

* `mu-plugins/4way-core.php` loads the content model on every request — no activation step.
* `wp 4way seed` creates the **Home** page, sets it as the static front page and imports
  all collections and settings.
* `headless.php` sends public page views to `fourway_frontend_url` (React), so
  `http://localhost:8080/` behaves as the homepage entry point while React renders it.
* `App.jsx` renders the same section order and Tailwind classes as the original HTML:
  Header → Hero → Trust badges → Who we are → What we do → Why choose us → Process →
  Projects → Testimonials → FAQ → Dark CTA → Blog → Newsletter → Footer.
* Header/hero/banner “Get a Free Quote” buttons open an accessible quote modal that
  posts to the WordPress CRM; the newsletter posts to the subscribers table.

---

## Testing & verification

```bash
cd frontend

npm test          # 10 unit tests (content merging, design sections, FAQ, quote, newsletter)
npm run build     # production bundle
```

The unit tests render the real `App` with the fetch layer mocked, verifying the offline
fallback, CMS hydration, the FAQ accordion, quote submission payload and newsletter
subscription.

Live checks against a running WordPress:

```bash
# API + CORS + database writes (creates one test lead + subscriber)
WP_URL=http://localhost:8080 FRONTEND_ORIGIN=http://localhost:5173 \
  node scripts/verify-live.mjs

# Render the React app against real WordPress data
cd frontend
LIVE_WP_URL=1 LIVE_WP_URL_BASE=http://localhost:8080 npm run test:live
```

PHP syntax check (any PHP ≥ 7.4):

```bash
find wordpress -name '*.php' -print0 | xargs -0 -n1 php -l
```

This repository was verified end-to-end with WordPress 6.6 on SQLite:

* REST payload: 4 badges, 6 services, 6 reasons, 4 steps, 4 projects, 2 testimonials,
  8 FAQs, 3 blog posts, every image resolved.
* `POST /contact` → `201` with row in `wp_4way_leads`; invalid email → `400`.
* `POST /subscribe` → `201`, duplicate subscription idempotent.
* CORS response returns the React origin.
* `/` → `302` to the React app; `/wp-admin` stays on WordPress.
* Fresh install + seed produced an empty `debug.log` (no PHP notices).
* PHP lint clean; 10/10 unit tests + live integration test pass; production build OK.

---

## Production deployment

**Frontend**

```bash
docker build -f frontend/Dockerfile \
  --build-arg VITE_WP_URL=https://cms.4waysafety.com \
  -t 4way-web .
docker run -p 8081:80 4way-web
# or: cd frontend && VITE_WP_URL=https://cms.4waysafety.com npm run build
```

Serve `frontend/dist` from any static host/CDN. Keep the build-time `VITE_WP_URL`
pointing at the WordPress origin.

**WordPress**

* Set **Homepage Content → Headless front end → React app URL** to the public site URL
  (or `FOURWAY_FRONTEND_URL`).
* Add the production origin to *Extra CORS origins*; the React URL is always allowed.
* Keep the headless redirect enabled so `/` resolves to React.
* Harden: `WP_DEBUG=false`, `DISALLOW_FILE_EDIT=true`, HTTPS, backups of
  `wp_4way_leads` / `wp_4way_subscribers`, and a real mailer for lead notifications.
* If the CMS lives on a different domain, terminate HTTPS and cache `GET` REST requests
  at the edge (the payload is public and stable).

---

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| Homepage shows bundled copy, not CMS edits | Check `VITE_WP_URL`, open `/wp-json/4way/v1/home`, and look for `data-content-source` on the root div (`fallback` vs `wordpress`). |
| REST 404 | Re-save **Settings → Permalinks** (or `wp rewrite structure '/%postname%/'`). |
| CORS error in console | Add the exact origin (scheme + host + port) under *Extra CORS origins*; no trailing slash. |
| `wp 4way` command missing | The mu-plugin folder must exist at `wp-content/mu-plugins/4way-core...`; run `wp 4way status`. |
| Images missing | Remote design URLs can expire; re-run `wp 4way seed --force --import-images` to store them in the media library. |
| Redirect loop | The React app URL must not equal the WordPress URL. |
| Want the WordPress theme visible | Append `?fourway_preview=1` to a WordPress URL or disable the redirect in settings. |

---

Built for **4Way Safety Solutions LLP** — complete fire protection & engineering
systems, Vapi • Daman • Surat • Ahmedabad • Vadodara • Mumbai • Pune.

