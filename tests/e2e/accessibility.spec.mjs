import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';
import { estateinRoutes } from './helpers.mjs';

for (const route of estateinRoutes) {
  test(`${route.name} has no serious or critical axe findings`, async ({ page }) => {
    await page.goto(route.path, { waitUntil: 'networkidle' });

    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa'])
      .analyze();
    const blocking = results.violations.filter(({ impact }) =>
      ['serious', 'critical'].includes(impact),
    );

    expect(
      blocking,
      blocking
        .map(
          ({ id, impact, help, nodes }) =>
            `[${impact}] ${id}: ${help}\n${nodes.map((node) => node.target.join(' ')).join('\n')}`,
        )
        .join('\n\n'),
    ).toEqual([]);
  });
}
