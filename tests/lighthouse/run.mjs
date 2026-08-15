import { spawn } from 'node:child_process';
import { mkdir, readFile } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';

const baseURL = process.env.ESTATEIN_BASE_URL || 'http://127.0.0.1:8080';
const lighthouseCLI = path.resolve('node_modules/lighthouse/cli/index.js');
const outputDirectory = path.resolve('reports/lighthouse');
const routes = [
  { name: 'home', path: '/' },
  { name: 'properties', path: '/properties/' },
  {
    name: 'property-details',
    path: '/properties/seaside-serenity-villa/',
  },
];
const categoryThresholds = {
  accessibility: 0.9,
  'best-practices': 0.9,
  seo: 0.9,
};

await mkdir(outputDirectory, { recursive: true });

function runLighthouse(url, outputPath) {
  return new Promise((resolve, reject) => {
    const command = spawn(
      process.execPath,
      [
        lighthouseCLI,
        url,
        '--chrome-flags=--headless --no-sandbox',
        '--only-categories=performance,accessibility,best-practices,seo',
        '--output=json',
        `--output-path=${outputPath}`,
        '--quiet',
      ],
      { stdio: 'inherit' },
    );

    command.on('error', reject);
    command.on('exit', (code) => {
      if (code === 0) {
        resolve();
      } else {
        reject(new Error(`Lighthouse exited with code ${code} for ${url}`));
      }
    });
  });
}

const failures = [];

for (const route of routes) {
  const url = new URL(route.path, baseURL).href;
  const outputPath = path.join(outputDirectory, `${route.name}.json`);
  await runLighthouse(url, outputPath);

  const report = JSON.parse(await readFile(outputPath, 'utf8'));
  for (const [category, threshold] of Object.entries(categoryThresholds)) {
    const score = report.categories[category]?.score ?? 0;
    console.log(`${route.name} ${category}: ${Math.round(score * 100)}`);

    if (score < threshold) {
      failures.push(
        `${route.name} ${category} scored ${Math.round(score * 100)}; expected ${threshold * 100}+`,
      );
    }
  }

  const performance = report.categories.performance?.score ?? 0;
  console.log(`${route.name} performance (advisory): ${Math.round(performance * 100)}`);
}

if (failures.length > 0) {
  throw new Error(failures.join('\n'));
}
