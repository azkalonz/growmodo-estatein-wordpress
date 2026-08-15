import { expect, test } from '@playwright/test';
import { estateinRoutes } from './helpers.mjs';

const viewports = [
  { width: 320, height: 720 },
  { width: 390, height: 844 },
  { width: 768, height: 1024 },
  { width: 1024, height: 768 },
  { width: 1440, height: 1000 },
  { width: 1920, height: 1080 },
];

test.beforeEach(({ browserName }) => {
  test.skip(browserName !== 'chromium', 'The viewport matrix runs once in Chromium.');
});

for (const viewport of viewports) {
  test(`all routes reflow without overflow at ${viewport.width}px`, async ({ page }) => {
    await page.setViewportSize(viewport);

    for (const route of estateinRoutes) {
      await page.goto(route.path, { waitUntil: 'networkidle' });
      const dimensions = await page.evaluate(() => ({
        clientWidth: document.documentElement.clientWidth,
        scrollWidth: document.documentElement.scrollWidth,
      }));

      expect(
        dimensions.scrollWidth,
        `${route.path} overflows by ${dimensions.scrollWidth - dimensions.clientWidth}px`,
      ).toBeLessThanOrEqual(dimensions.clientWidth + 1);
      await expect(page.locator('h1')).toBeVisible();
    }
  });
}
