import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';

const exportPath = path.resolve(process.argv[2] || 'demo-content/estatein-demo-content.xml');
const defaultAssetBase =
  'https://raw.githubusercontent.com/azkalonz/growmodo-estatein-wordpress/main/wp-content/themes/estatein/';
const configuredAssetBase = process.env.ESTATEIN_WXR_ASSET_BASE || defaultAssetBase;
const assetBase = configuredAssetBase.endsWith('/')
  ? configuredAssetBase
  : `${configuredAssetBase}/`;
const themePathMarker = '/wp-content/themes/estatein/';
const sourceMetaPattern =
  /<wp:meta_key>_estatein_source_path<\/wp:meta_key>\s*<wp:meta_value><!\[CDATA\[([^\]]+)\]\]><\/wp:meta_value>/;

let exportXML = await readFile(exportPath, 'utf8');
let rewrittenAttachments = 0;

exportXML = exportXML.replace(/<item>[\s\S]*?<\/item>/g, (item) => {
  if (!item.includes('<wp:post_type>attachment</wp:post_type>')) {
    return item;
  }

  const sourceMatch = item.match(sourceMetaPattern);
  const sourcePath = sourceMatch?.[1] || '';
  const markerPosition = sourcePath.indexOf(themePathMarker);

  if (markerPosition < 0) {
    return item;
  }

  const relativePath = sourcePath.slice(markerPosition + themePathMarker.length);
  const publicAssetURL = new URL(relativePath, assetBase).href;
  const withPortableGUID = item.replace(
    /<guid isPermaLink="false">[^<]*<\/guid>/,
    `<guid isPermaLink="false">${publicAssetURL}</guid>`,
  );
  const withPortableAttachment = withPortableGUID.replace(
    /<wp:attachment_url>[^<]*<\/wp:attachment_url>/,
    `<wp:attachment_url>${publicAssetURL}</wp:attachment_url>`,
  );

  if (withPortableAttachment !== item) {
    rewrittenAttachments += 1;
  }

  return withPortableAttachment;
});

if (rewrittenAttachments === 0) {
  throw new Error(
    `No seeded attachment URLs were rewritten in ${exportPath}. Refusing to publish a localhost-only WXR.`,
  );
}

await writeFile(exportPath, exportXML, 'utf8');
console.log(`Rewrote ${rewrittenAttachments} WXR attachment URLs to ${assetBase}`);
