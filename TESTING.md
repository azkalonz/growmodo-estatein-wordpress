# Testing and acceptance

Run all checks from the repository root against a seeded Docker install unless a command says otherwise.

## Automated checks

| Area                | Command                                         | Pass condition                                                                                         |
| ------------------- | ----------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| Compose             | `docker compose --env-file .env config --quiet` | Resolved model is valid; only 8080 and 8026 bind to loopback; MySQL has no host port.                  |
| PHP syntax          | `make lint-php`                                 | Every theme/plugin PHP file reports no syntax errors.                                                  |
| WordPress standards | `composer phpcs`                                | WordPress, WordPress-Extra, and PHPCompatibilityWP for PHP 8.3+ pass.                                  |
| JavaScript          | `npm run lint:js`                               | ES modules and browser tests pass ESLint.                                                              |
| CSS                 | `npm run lint:css`                              | Theme styles pass Stylelint.                                                                           |
| Routes/links        | `npm run test:e2e`                              | Six routes, one H1, landmarks, internal links, and console are clean in Chromium, Firefox, and WebKit. |
| Accessibility       | `npm run test:e2e`                              | No serious/critical axe findings for WCAG 2.2 AA tags.                                                 |
| Responsive          | `npm run test:e2e`                              | No horizontal overflow at 320, 390, 768, 1024, 1440, or 1920 pixels.                                   |
| Interaction         | `npm run test:e2e`                              | Keyboard menu, FAQ, native dialog, validation, and a stored contact submission pass.                   |
| Lighthouse          | `make lighthouse`                               | Accessibility, SEO, and Best Practices are each at least 90 on three representative routes.            |
| Packages            | `make package`                                  | Both ZIPs pass `unzip -t`; SHA-256 hashes are generated.                                               |

Install Playwright’s browsers once with `npx playwright install`. Run only Chromium during a fast edit loop with `npm run test:e2e:chromium`.

If the Playwright CDN is temporarily unavailable, the Chromium project can use an installed Chrome binary without changing CI defaults:

```bash
ESTATEIN_CHROMIUM_EXECUTABLE_PATH="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" npm run test:e2e:chromium
```

This fallback does not replace the required Firefox and WebKit release run.

## WordPress runtime checks

1. Run `make install`, then confirm `./scripts/wp.sh core version` reports 7.0.4 and `./scripts/wp.sh eval 'echo PHP_VERSION;'` reports PHP 8.3.x.
2. Visit all six routes while tailing `wp-content/debug.log`; the log must contain no warnings/notices from Estatein.
3. Confirm property filters preserve `keyword`, `estatein_location`, `estatein_property_type`, min/max price, min/max area, and page values. Test a real result and the designed no-results state.
4. Create/edit/publish a property and a team member using ACF. Deactivate ACF temporarily, set a Property Image plus Additional Property Images with the native Media Library controls, and verify public templates render the saved post-meta values.
5. Run `make seed` twice and compare post/term/menu counts; no fixture item may duplicate.
6. Run `make qa-plugins`. Execute Plugin Check from WP-CLI and WordPress Theme Check from **Appearance → Theme Check**; classify any development-only warning in the handoff instead of hiding it.

## Form matrix

Test contact, property inquiry, and newsletter independently:

- empty, malformed, too-long, and valid values;
- unchecked terms, invalid nonce, and a filled honeypot;
- repeated submissions inside the rate-limit window;
- contextual error copy, `aria-invalid`, focus to the first error, and retained safe values;
- post/redirect/get (refresh must not resubmit);
- one private `estatein_inquiry` record for each valid unique request;
- Mailpit notification at <http://127.0.0.1:8026> and graceful success if mail is unavailable.

Do not test nonce or honeypot behavior by changing production records. Use local fixture identities such as `qa+timestamp@example.test`.

## Visual and manual browser pass

Run `npm run screenshots`, then compare all 18 files described in [tests/visual/README.md](tests/visual/README.md) to the matching Figma nodes. Also inspect 200% zoom, reduced motion, Windows high contrast where available, keyboard-only use, and touch targets at 390 pixels.

Installed Chrome and Safari receive a hands-on pass. Playwright covers Firefox and WebKit. Before submission, complete this Edge checklist on Windows or BrowserStack:

- navigation opens/closes and returns focus;
- property scroll-snap and gallery dialog have no clipped controls;
- selects, number inputs, checkboxes, and validation messages match the intended dark theme;
- `position: sticky`, focus rings, SVG masks, and WebP fallbacks render correctly;
- 390, 768, 1024, 1440, and 1920 widths have no horizontal scroll;
- contact and newsletter redirects end on an announced status message;
- DevTools console and Network tabs contain no errors, 404s, or mixed content.

## Performance/SEO release gate

Run Lighthouse in a production-like non-debug build on Home, Properties, and one property detail page. Accessibility, SEO, and Best Practices must each be at least 90. Confirm only the hero is preloaded, below-fold images are lazy, local fonts use `font-display`, canonical/core sitemap tags are not duplicated, property JSON-LD validates, and every meaningful image has useful alternative text.
