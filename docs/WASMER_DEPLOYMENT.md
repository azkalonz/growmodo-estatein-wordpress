# Wasmer deployment checklist

The repository deliberately keeps production account ownership and credentials out of source control. The final deployer should perform these steps from their own Wasmer and WordPress accounts.

## 1. Build the handoff artifacts

From a seeded local checkout:

```bash
make export
make package
(cd dist && shasum -a 256 -c SHA256SUMS)
```

Keep these files together:

- `dist/estatein-theme.zip`
- `dist/estatein-core.zip`
- `dist/estatein-demo-content.xml` (also retained under `demo-content/`)
- the repository commit SHA used to build them

## 2. Create the WordPress app

1. Open Wasmer’s official [WordPress Starter](https://wasmer.io/templates/wordpress-starter) while signed in.
2. Choose the app name and supported region; select WordPress 7.0.4 and PHP 8.3 where the starter offers those exact versions.
3. Deploy and save the resulting HTTPS `*.wasmer.app` URL.
4. Confirm the app’s managed MySQL database is attached. Wasmer exposes the provisioned database through `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USERNAME`, and `DB_PASSWORD`; do not copy them into this repository. The [database dashboard](https://docs.wasmer.io/edge/wordpress-hosting/database-management/) can be used for inspection.

If the starter’s available WordPress patch is newer than 7.0.4, deploy the current secure patch, record it in the handoff, and rerun the acceptance checks. Do not downgrade a production security release only to match local development.

## 3. Install code and content

1. In WordPress admin, install **Advanced Custom Fields** version 6.8.7.
2. Upload and activate `estatein-core.zip`, then upload and activate `estatein-theme.zip`.
3. Confirm the public repository’s `main` branch is available; the WXR downloads its exact seeded media from those committed source assets.
4. Use **Tools → Import → WordPress** to import `estatein-demo-content.xml`, assigning content to the deployment owner. Visual fallbacks remain bundled with the theme if a host blocks remote media downloads.
5. If WP-CLI is available in the host shell, `wp estatein seed` is the preferred equivalent and is safe to rerun.
6. In **Settings → Reading**, confirm the seeded Home page is the static front page.
7. In **Settings → Permalinks**, choose **Post name** and save once. Verify `/properties/` and `/properties/seaside-serenity-villa/`.
8. Confirm the Primary and Footer menus are assigned, and set the privacy-policy page.

## 4. Production configuration

- Keep `WP_DEBUG` and `SCRIPT_DEBUG` off for public traffic; retain protected server logs.
- Confirm WordPress Address and Site Address use the final HTTPS host with no mixed-content requests.
- Configure the hosting provider’s SMTP/email facility and send a real test inquiry. The app’s database record remains the source of truth if delivery fails. Wasmer apps can opt into email support with `enable_email`; follow the current [app configuration documentation](https://docs.wasmer.io/edge/configuration/) for the deployed starter.
- Protect the administrator account with a unique password and MFA where the account provider supports it. Remove unused administrator accounts and development-only QA plugins.
- Verify uploads persist across a redeploy. Wasmer volumes are region-bound; renaming or removing a volume can purge its data, so review the [persistent-volume guidance](https://docs.wasmer.io/edge/learn/volumes/) before changing storage configuration.
- Take a database and uploads backup before imports, plugin changes, or a new release.

## 5. Live acceptance gate

Run the suite against the public URL:

```bash
ESTATEIN_BASE_URL=https://your-app.wasmer.app npm run test:e2e
ESTATEIN_BASE_URL=https://your-app.wasmer.app npm run screenshots
```

Then verify:

- all six routes and property pagination/filter URLs return HTTPS 200 responses;
- no console errors, PHP notices, broken internal links, or mixed content;
- a contact request and newsletter signup create private dashboard entries and attempt email;
- keyboard navigation, focus return, menu, FAQs, gallery, and form errors work;
- axe has no serious/critical findings and Lighthouse Accessibility, SEO, and Best Practices are at least 90;
- Chrome and Safari match the reference frames; complete the Edge checklist in [TESTING.md](../TESTING.md).

Add the final public URL to the repository README and the submission only after this gate passes.
