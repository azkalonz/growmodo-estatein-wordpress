# Wasmer preview deployment

The project deploys to the existing managed WordPress Starter at [estatein-preview.wasmer.app](https://estatein-preview.wasmer.app/). GitHub Actions publishes the custom theme and companion plugin directly to the app's persistent `wp-content` volume after every successful push to `main`.

This preserves the Wasmer-managed runtime and data:

| Setting             | Value                                              |
| ------------------- | -------------------------------------------------- |
| Wasmer app          | `azkalonz/estatein-preview`                        |
| Public URL          | `https://estatein-preview.wasmer.app/`             |
| PHP                 | 8.3.31                                             |
| WordPress           | 7.0.4                                              |
| Automatic trigger   | Successful quality checks on a push to `main`      |
| CI deployment scope | `themes/estatein` and `plugins/estatein-core` only |

The workflow does not deploy WordPress core, replace Wasmer's platform plugin, modify the database, or touch media uploads and unrelated plugins. Do not add a generic Wasmer `app.yaml` to this repository for this target: redeploying the Starter as a different package could change its managed WordPress/PHP runtime.

## How the pipeline works

The `static-analysis` job:

1. runs the PHP, JavaScript, CSS, dependency, Docker Compose, and formatting checks;
2. builds `dist/estatein-theme.zip` and `dist/estatein-core.zip`;
3. validates package layout, checksums, secret exclusions, WXR attachment URLs, and size;
4. stores the verified `dist/` handoff as a 30-day GitHub Actions artifact.

On a push to `main`, the dependent `deploy-wasmer-preview` job then:

1. downloads that exact verified artifact;
2. installs pinned Wasmer CLI 7.2.1 after checking its SHA-256 digest;
3. asks Wasmer for short-lived S3-compatible volume credentials using `WASMER_TOKEN`;
4. confirms that the app exposes the expected `wp-content` volume;
5. syncs only the packaged Estatein plugin and theme directories;
6. checks the public theme stylesheet and site URL over HTTPS.

Concurrent deployments are serialized. The generated rclone credentials live only in the runner's temporary directory and are deleted when the deployment step exits.

## One-time GitHub setup

Create a Wasmer access token at [Wasmer access tokens](https://wasmer.io/settings/access-tokens). Treat it like a password and never put it in this repository, an issue, a workflow file, or chat.

In `azkalonz/growmodo-estatein-wordpress`:

1. Open **Settings → Secrets and variables → Actions**.
2. Select **New repository secret**.
3. Use the exact name `WASMER_TOKEN`.
4. Paste the token as its value and save it.

No WordPress, database, or Wasmer S3 credentials belong in GitHub. The Wasmer token is the only CI secret required.

## First automatic deployment

After `WASMER_TOKEN` exists, merge or push these workflow changes to `main`. Open **Actions → Quality checks** and confirm both jobs are green:

- `static-analysis`
- `Deploy Wasmer preview`

The theme and plugin will then exist on the WordPress server without uploading ZIP files in wp-admin.

If the deploy job reports that `wp-content` was not found, stop and verify the volume name in the Wasmer app's **Storage** page. Do not change the script to point at an unverified volume.

## One-time WordPress configuration

File delivery is automatic, but the new Starter's database still needs a one-time setup. In `https://estatein-preview.wasmer.app/wp-admin/`:

1. Under **Plugins**, activate **Estatein Core**. Optionally install and activate **Advanced Custom Fields** for the richer editing interface; the frontend has native-meta fallbacks.
2. Under **Appearance → Themes**, activate **Estatein**.
3. Under **Tools → Import**, install the WordPress importer if prompted and import `dist/estatein-demo-content.xml` once. Assign imported content to the deployment owner and enable attachment downloads.
4. Under **Settings → Reading**, select the imported **Home** page as the static front page.
5. Under **Settings → Permalinks**, choose **Post name** and save once.
6. Under **Appearance → Menus**, confirm the Primary and Footer menu assignments.
7. Verify `/properties/` and `/properties/seaside-serenity-villa/`.

This is initial content configuration, not a recurring code-upload process. Do not re-import the WXR on every deployment: it is not database synchronization and repeated imports can skip or duplicate content.

## Normal updates

For every later code change:

1. open a pull request and let the quality checks run;
2. merge it to `main`;
3. GitHub Actions deploys the verified theme and plugin automatically;
4. check the deployment URL recorded in the workflow's `wasmer-preview` environment.

No Wasmer dashboard action or WordPress ZIP upload is needed. Database content, WordPress settings, form submissions, and media uploads remain in Wasmer.

## Rollback

The safest rollback is to revert the offending Git commit on `main`. The revert runs the same checks and syncs the previous tracked theme/plugin state back to the volume. The per-commit handoff artifact is also retained for 30 days for inspection.

The sync uses deletion only inside the two owned deployment directories, so it removes obsolete files from the Estatein theme/plugin without deleting uploads or third-party plugins. Wasmer documents daily volume backups with a recovery point of up to 24 hours; contact Wasmer support if a platform-level volume restoration is required.

## Public acceptance checks

Run these against the final URL after the initial content setup and after risky releases:

```bash
ESTATEIN_BASE_URL=https://estatein-preview.wasmer.app npm run test:e2e
ESTATEIN_BASE_URL=https://estatein-preview.wasmer.app npm run screenshots
ESTATEIN_BASE_URL=https://estatein-preview.wasmer.app npm run lighthouse
```

Verify that all page types and property filters return HTTPS 200 responses, forms create private dashboard records, there are no PHP notices or mixed-content failures, axe has no serious/critical findings, and Lighthouse Accessibility, SEO, and Best Practices scores remain at least 90.

Platform references: [Wasmer volume access](https://docs.wasmer.io/edge/learn/volumes/), [WordPress volumes](https://docs.wasmer.io/edge/wordpress-hosting/volumes/), [database management](https://docs.wasmer.io/edge/wordpress-hosting/database-management/), and [GitHub deployments](https://docs.wasmer.io/edge/git/wasmer-for-github/).
