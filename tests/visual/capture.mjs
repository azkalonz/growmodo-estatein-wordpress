import { chromium } from '@playwright/test';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import { estateinRoutes } from '../e2e/helpers.mjs';

const baseURL = process.env.ESTATEIN_BASE_URL || 'http://127.0.0.1:8080';
const chromiumExecutablePath = process.env.ESTATEIN_CHROMIUM_EXECUTABLE_PATH;
const outputRoot = path.resolve('reports/screenshots');
const viewports = [
  { width: 390, height: 844 },
  { width: 1440, height: 1000 },
  { width: 1920, height: 1080 },
];

const browser = await chromium.launch(
  chromiumExecutablePath ? { executablePath: chromiumExecutablePath } : {},
);

async function primeLazyMedia(page, viewportHeight) {
  const documentHeight = await page.evaluate(() => document.documentElement.scrollHeight);
  const scrollStep = Math.max(400, Math.floor(viewportHeight * 0.8));

  for (let scrollTop = 0; scrollTop < documentHeight; scrollTop += scrollStep) {
    await page.evaluate((position) => globalThis.scrollTo(0, position), scrollTop);
    await page.waitForTimeout(80);
  }

  await page.evaluate(() => globalThis.scrollTo(0, 0));
  await page.waitForLoadState('networkidle');
  await page.waitForFunction(
    () =>
      [...document.images].every(
        (image) => !image.currentSrc || (image.complete && image.naturalWidth > 0),
      ),
    undefined,
    { timeout: 15_000 },
  );
}

try {
  for (const viewport of viewports) {
    const context = await browser.newContext({
      colorScheme: 'dark',
      deviceScaleFactor: 1,
      locale: 'en-US',
      viewport,
    });
    const page = await context.newPage();
    const widthDirectory = path.join(outputRoot, String(viewport.width));
    await mkdir(widthDirectory, { recursive: true });

    for (const route of estateinRoutes) {
      const slug = route.path === '/' ? 'home' : route.path.split('/').filter(Boolean).join('--');
      await page.goto(new URL(route.path, baseURL).href, { waitUntil: 'networkidle' });
      await page.emulateMedia({ reducedMotion: 'reduce' });
      await primeLazyMedia(page, viewport.height);
      await page.screenshot({
        animations: 'disabled',
        fullPage: true,
        path: path.join(widthDirectory, `${slug}.png`),
      });
    }

    await context.close();
  }
} finally {
  await browser.close();
}

console.log(`Captured ${estateinRoutes.length * viewports.length} screenshots in ${outputRoot}`);
