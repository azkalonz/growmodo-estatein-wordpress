# Estatein — custom WordPress theme

[![Quality checks](https://github.com/azkalonz/growmodo-estatein-wordpress/actions/workflows/quality.yml/badge.svg)](https://github.com/azkalonz/growmodo-estatein-wordpress/actions/workflows/quality.yml)

A responsive, content-managed implementation of the six-page **Estatein** real-estate design: Home, About Us, Properties, Property Details, Services, and Contact. It is built as a custom hybrid classic theme, backed by a portable companion plugin rather than a page builder.

Repository: <https://github.com/azkalonz/growmodo-estatein-wordpress>

Live demo: [estatein-preview.wasmer.app](https://estatein-preview.wasmer.app/)

## What is included

- `wp-content/themes/estatein/` — PHP templates, WordPress Loops, `theme.json` v3 tokens, reusable template parts, responsive CSS, small ES modules, and bundled visual/font assets.
- `wp-content/plugins/estatein-core/` — property, team, and inquiry content types; property taxonomies and ACF definitions; filters; secure form handling; metadata, Open Graph, and property JSON-LD; and the idempotent `wp estatein seed` fixture.
- `docker-compose.yml` — an isolated WordPress 7.0.4 / PHP 8.3 / MySQL 8.4 environment. The website binds only to `127.0.0.1:8080`, Mailpit to `127.0.0.1:8026`, and MySQL has no host port.
- `demo-content/` — a WXR export workflow for hosts without WP-CLI.
- `tests/` — Playwright smoke, accessibility, interaction, link, and responsive checks plus full-page screenshot capture.
- `dist/` — verified installable theme and companion-plugin ZIPs produced by `make package`.

## Development process and choices

The Figma frames were treated as the visual authority. Large pages were inspected section by section at the supplied 390, 1440, and 1920 pixel frames, with the design variables mapped into shared CSS custom properties and `theme.json`. Urbanist is self-hosted; original Figma exports remain in the repository, and optimized WebP variants are used where practical. Layout is mobile-first with deliberate changes at 768 and 1600 pixels and fluid behavior in between.

The theme stays presentation-focused. Repeated headings, property cards, sliders, forms, FAQs, CTA, header, and footer are template parts. Native WordPress menus, thumbnails, excerpts, canonical URLs, and sitemaps remain available. JavaScript progressively enhances the mobile menu, scroll-snap controls, accessible accordion, validation, and native-dialog gallery; the content remains readable if JavaScript is unavailable.

The companion plugin keeps structured content portable when the theme changes. It registers `estatein_property`, `estatein_team_member`, and private `estatein_inquiry` records plus location/property-type taxonomies. ACF Free 6.8.7 improves the editor but is not a frontend runtime dependency: accessors fall back to post meta and fixture defaults. Property search uses sanitized GET values and normal `WP_Query` pagination.

Contact, inquiry, and newsletter requests use nonces, a honeypot, server-side validation, rate limiting, and post/redirect/get. A valid request is stored before email is attempted, so an unavailable host mail service does not lose the lead. Local messages are captured by Mailpit. Output escaping, prepared queries/core APIs, least-privilege content visibility, editor-file locking, and no committed credentials cover the main security boundaries.

## Local setup

Requirements: Docker with Compose, Node.js 24+, npm 11+, Composer 2, and `zip`.

```bash
cp .env.example .env
# Replace every placeholder in .env with unique local values.
npm ci
composer install
npx playwright install
make install
```

Open <http://127.0.0.1:8080>, WordPress admin at <http://127.0.0.1:8080/wp-admin/>, and Mailpit at <http://127.0.0.1:8026>. The admin identity comes from the ignored `.env` file. `make install` is safe to rerun; `make seed` refreshes the fixture without duplicating content.

Useful commands:

```bash
make lint          # PHP syntax, WPCS/WordPress-Extra/PHPCompatibility, JS, CSS
make test-e2e      # Chromium, Firefox, and WebKit acceptance suite
make screenshots   # 18 full-page captures at 390, 1440, and 1920 px
make lighthouse    # Enforce 90+ Accessibility, SEO, and Best Practices
make qa-plugins    # Install WordPress Theme Check and Plugin Check locally
make export        # Write demo-content/estatein-demo-content.xml
make package       # Create and integrity-check the installable ZIPs
```

See [TESTING.md](TESTING.md) for the complete matrix and manual browser checklist. `WP_DEBUG` and debug logging are enabled in Docker while browser display is disabled; production should use the host’s normal non-debug configuration.

## Content and deployment

Editors manage the principal pages through normal WordPress pages, properties under **Properties**, team members under **Team**, and saved form submissions under the private **Inquiries** screen. Menus and core site settings use standard WordPress controls. The fixture preserves the Estatein demo copy for design fidelity; it is sample content, not a claim about a real brokerage.

For delivery, run `make export && make wasmer`. The production ZIP keeps referenced runtime assets and omits only the unreferenced raw Figma capture archive, which remains committed for traceability. Initial database content, theme/plugin activation, menus, and permalinks follow [the Wasmer checklist](docs/WASMER_DEPLOYMENT.md). Production email requires host SMTP configuration; saved inquiries remain authoritative.

After the one-time `WASMER_TOKEN` secret is configured, every successful push to `main` automatically syncs the verified Estatein theme and companion plugin to [estatein-preview.wasmer.app](https://estatein-preview.wasmer.app/). No recurring WordPress ZIP upload is required. GitHub also retains the exact `wasmer-wordpress-<commit>` handoff for 30 days. The workflow does not touch the database, media, unrelated plugins, or WordPress core; Wasmer's managed Starter continues to supply the PHP/WordPress runtime, database integration, platform plugin, and persistent `wp-content` volume.

## Attribution

Design and demo content are adapted from **Estatein Real Estate Business Website UI Template — Dark Theme** by Produce UI, used under [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/). See [the original Figma community resource](https://www.figma.com/community/file/1314076616839640516) and [ATTRIBUTION.md](docs/ATTRIBUTION.md). Urbanist is distributed under the SIL Open Font License included with the font files.
