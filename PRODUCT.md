# Estatein WordPress Trial

## Product truth

Estatein is a six-page demonstration website for a fictional real-estate business. It lets a prospective buyer understand the agency, browse and filter properties, inspect a property in detail, review services, and submit an inquiry. The implementation exists to demonstrate production-quality WordPress theme development and faithful design-to-code execution; its property counts, prices, testimonials, awards, and client names are sample content from the supplied design, not real commercial claims.

## Audience and jobs

- Prospective buyers and investors need to discover relevant properties and request help.
- Property owners need to understand the agency's management and selling services.
- Evaluators need to verify responsive fidelity, maintainable WordPress architecture, accessibility, and working content workflows.
- Site editors need portable structured properties, team members, inquiries, pages, menus, and media without depending on the active theme.

## Required experience

- Six public page families: Home, About Us, Properties, Property Details, Services, and Contact.
- Public property archive at `/properties/` and detail URLs at `/properties/{slug}/`.
- Search and filtering by keyword, location, type, price, and area with persistent pagination.
- Keyboard-operable navigation, horizontal content controls, FAQ accordions, and gallery lightbox.
- Contact, property-inquiry, and newsletter forms that persist valid submissions before attempting email.
- Useful generic page, post, archive, search, and 404 fallbacks.

## Platform and constraints

- WordPress 7.0.4, PHP 8.3, MySQL 8.4, and `theme.json` v3.
- Hybrid classic theme plus a companion content plugin; ACF Free is optional at runtime because native post-meta fallbacks remain functional.
- Vanilla CSS and small ES modules. No page builder, Tailwind, jQuery, icon font, carousel package, analytics, or remote production assets.
- The local stack is isolated on `127.0.0.1:8080`; database access is internal and Mailpit is exposed only at `127.0.0.1:8026`.
- The user owns the final Wasmer deployment and live HTTPS URL.

## Success criteria

Every supplied Figma section is implemented at the 390, 1440, and 1920px reference widths, all CMS and form flows work without PHP notices or console errors, serious/critical accessibility findings are zero, the installable packages reproduce the demo, and the public repository contains no credentials or transient data.

## Attribution

The visual design and sample copy are adapted from **Estatein – Real Estate Website Template** by Praha / Produce UI, licensed under [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/). Source: [Figma Community file](https://www.figma.com/community/file/1314076616839640516/real-estate-business-website-ui-template-dark-theme-produce-ui).
