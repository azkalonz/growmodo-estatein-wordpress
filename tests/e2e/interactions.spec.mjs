import { expect, test } from '@playwright/test';

test('mobile navigation is keyboard operable and restores its state', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/');

  const toggle = page.locator('[data-menu-toggle]');
  const navigation = page.locator('[data-primary-navigation]');

  await expect(toggle).toBeVisible();
  await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  await toggle.focus();
  await page.keyboard.press('Enter');
  await expect(toggle).toHaveAttribute('aria-expanded', 'true');
  await expect(navigation).toBeVisible();
  await page.keyboard.press('Escape');
  await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  await expect(toggle).toBeFocused();
});

test('FAQ accordion exposes its panel relationship', async ({ page }) => {
  await page.goto('/');

  const accordion = page.locator('[data-accordion]').first();
  const closedTrigger = accordion
    .locator('[data-accordion-trigger][aria-expanded="false"]')
    .first();
  const panelId = await closedTrigger.getAttribute('aria-controls');
  const triggerId = await closedTrigger.getAttribute('id');

  expect(panelId).toBeTruthy();
  expect(triggerId).toBeTruthy();

  const pinnedTrigger = page.locator(`#${triggerId}`);
  await pinnedTrigger.click();
  await expect(pinnedTrigger).toHaveAttribute('aria-expanded', 'true');
  await expect(page.locator(`#${panelId}`)).toBeVisible();
});

test('property gallery opens and closes a native dialog', async ({ page }) => {
  await page.goto('/properties/seaside-serenity-villa/');

  const opener = page.locator('[data-gallery-open]').first();
  const dialog = page.locator('dialog[data-gallery-dialog]');

  await expect(opener).toBeVisible();
  await opener.click();
  await expect(dialog).toHaveAttribute('open', '');
  await page.keyboard.press('Escape');
  await expect(dialog).not.toHaveAttribute('open', '');
  await expect(opener).toBeFocused();
});

test('contact form reports invalid fields before submission', async ({ page }) => {
  await page.goto('/contact/');

  const form = page.locator('form[data-validate-form]').first();
  await form.getByRole('button', { name: /send your message/i }).click();

  await expect(form.locator('[aria-invalid="true"]').first()).toBeVisible();
  await expect(
    form
      .locator('.field__error')
      .filter({ hasText: /required|enter|select|agree/i })
      .first(),
  ).toBeVisible();
});

test('properties inquiry exposes the Figma preference controls', async ({ page }) => {
  await page.goto('/properties/');

  const form = page.locator('#estatein-property-contact');
  await expect(form.getByLabel('Preferred Location')).toBeVisible();
  await expect(form.getByLabel('Property Type')).toBeVisible();
  await expect(form.getByLabel('No. of Bathrooms')).toBeVisible();
  await expect(form.getByLabel('No. of Bedrooms')).toBeVisible();
  await expect(form.getByLabel('Budget')).toBeVisible();
  await expect(form.getByRole('radio', { name: 'Phone' })).toBeChecked();
  await expect(form.getByRole('radio', { name: 'Email' })).not.toBeChecked();
});

test('valid contact submission survives the redirect and reports success', async ({
  page,
  browserName,
}) => {
  test.skip(browserName !== 'chromium', 'Only one browser should create the fixture inquiry.');

  await page.goto('/contact/');
  const form = page.locator('form[data-validate-form]').first();
  const uniqueEmail = `playwright-${Date.now()}@example.test`;

  await form.getByLabel('First Name').fill('Playwright');
  await form.getByLabel('Last Name').fill('Test');
  await form.getByLabel('Email').fill(uniqueEmail);
  await form.getByLabel('Phone').fill('+1 202 555 0147');
  await form.getByLabel('Inquiry Type').selectOption('buying');
  await form.getByLabel('Message').fill('Automated acceptance-test inquiry.');
  await form.getByRole('checkbox').check();

  await Promise.all([
    page.waitForURL(/estatein_(status|form_status)=success|contact=success/),
    form.getByRole('button', { name: /send your message/i }).click(),
  ]);

  await expect(
    page
      .locator('[role="status"], .form-status')
      .filter({ hasText: /thank|received|success/i })
      .first(),
  ).toBeVisible();
});
