import { expect, test } from '@playwright/test';
import { capturePageErrors, estateinRoutes, localPathname } from './helpers.mjs';

for (const route of estateinRoutes) {
  test(`${route.name} renders one useful document`, async ({ page }) => {
    const errors = capturePageErrors(page);
    const response = await page.goto(route.path, { waitUntil: 'networkidle' });

    expect(response, `${route.path} should return a document response`).not.toBeNull();
    expect(response.ok(), `${route.path} returned ${response.status()}`).toBeTruthy();
    await expect(page.locator('main')).toBeVisible();
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.locator('h1')).not.toHaveText('');
    await expect(page.locator('header nav')).toBeAttached();
    await expect(page.locator('footer')).toBeAttached();
    expect(errors, errors.join('\n')).toEqual([]);
  });
}

test('internal links resolve without HTTP errors', async ({ page, request, baseURL }) => {
  await page.goto('/', { waitUntil: 'networkidle' });

  const hrefs = await page
    .locator('a[href]')
    .evaluateAll((links) => links.map((link) => link.getAttribute('href')).filter(Boolean));
  const paths = [...new Set(hrefs.map((href) => localPathname(baseURL, href)).filter(Boolean))];

  expect(paths.length).toBeGreaterThan(5);

  for (const path of paths) {
    const response = await request.get(path, { failOnStatusCode: false });
    expect(response.status(), `${path} returned ${response.status()}`).toBeLessThan(400);
  }
});
