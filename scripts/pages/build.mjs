import { copyFile, cp, mkdir, readdir, rm, stat, writeFile } from 'node:fs/promises';
import { join } from 'node:path';
import { readOptional, renderCatalog, renderPage } from './render.mjs';

const output = 'dist';
const local = process.argv.includes('--local');
const localUrl = 'http://localhost:8006';
const maxAssetSize = 25 * 1024 * 1024;
const maxAssetCount = 20_000;
const origin = new URL(
  local ? localUrl : process.env.CF_PAGES_BRANCH === 'main'
    ? process.env.PUBLIC_SITE_URL || process.env.CF_PAGES_URL || localUrl
    : process.env.CF_PAGES_URL || process.env.PUBLIC_SITE_URL || localUrl,
).origin;
const catalog = [];
const manifest = { byCode: Object.create(null), bySlug: Object.create(null) };

async function listFiles(dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  return (await Promise.all(entries.map((entry) => {
    const path = join(dir, entry.name);
    return entry.isDirectory() ? listFiles(path) : path;
  }))).flat();
}

await rm(output, { recursive: true, force: true });
await mkdir(output);
await cp('views', join(output, 'views'), {
  recursive: true,
  filter: (path) => !path.split(/[\\/]/).some((part) => part.startsWith('.') || /\.(?:php\d*|phtml|phar)$/i.test(part)),
});
await copyFile('favicon.png', join(output, 'favicon.png'));

const dirs = await readdir('views', { withFileTypes: true });
for (const dir of dirs.sort((a, b) => a.name.localeCompare(b.name))) {
  if (!dir.isDirectory() || dir.name.startsWith('.')) continue;
  const slug = dir.name;
  const html = await readOptional(join('views', slug, 'index.html'));
  const rawMeta = await readOptional(join('views', slug, 'meta.json'));
  const meta = rawMeta === null ? null : JSON.parse(rawMeta);
  if (meta?.code != null) {
    manifest.byCode[meta.code] = { ...meta, view: slug };
  }
  if (html === null) continue;
  if (['meta', 'views', '_worker.js'].includes(slug)) throw new Error('Slug reservado: ' + slug);
  if (local) catalog.push({ slug, meta: meta ?? {} });
  manifest.bySlug[slug] = meta ?? [];
  await mkdir(join(output, slug));
  await writeFile(join(output, slug, 'index.html'), renderPage(html, slug, meta ?? {}, origin));
}

if (local) await writeFile(join(output, 'index.html'), await renderCatalog(catalog));
await copyFile(new URL('./404.html', import.meta.url), join(output, '404.html'));
await writeFile(join(output, '_metadata.json'), JSON.stringify(manifest));
await copyFile(new URL('./worker.js', import.meta.url), join(output, '_worker.js'));
await copyFile(new URL('./headers', import.meta.url), join(output, '_headers'));

const files = await listFiles(output);
const assets = files.filter((path) => !['_worker.js', '_headers'].includes(path.slice(output.length + 1)));
const sizes = await Promise.all(assets.map(async (path) => ({ path, size: (await stat(path)).size })));
sizes.sort((a, b) => b.size - a.size);
console.log(assets.length + ' arquivos publicáveis. Maior arquivo: ' + sizes[0].path + ' (' + sizes[0].size + ' bytes).');
const oversized = sizes.filter(({ size }) => size > maxAssetSize);
if (oversized.length) {
  throw new Error('Arquivos acima de 25 MiB. Mova-os para o R2 antes de publicar:\n' +
    oversized.map(({ path }) => path).join('\n'));
}
if (assets.length > maxAssetCount) {
  throw new Error('O build excede 20.000 arquivos, limite do plano gratuito do Pages.');
}
