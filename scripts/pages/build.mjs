import { copyFile, cp, mkdir, readFile, readdir, rm, stat, writeFile } from 'node:fs/promises';
import { join } from 'node:path';

const output = 'dist';
const maxAssetSize = 25 * 1024 * 1024;
const maxAssetCount = 20_000;
const origin = new URL(
  process.env.CF_PAGES_BRANCH === 'main'
    ? process.env.PUBLIC_SITE_URL || process.env.CF_PAGES_URL || 'http://localhost:8788'
    : process.env.CF_PAGES_URL || process.env.PUBLIC_SITE_URL || 'http://localhost:8788',
).origin;
const footerStyle = await readFile(new URL('./footer.css', import.meta.url), 'utf8');
const manifest = { byCode: Object.create(null), bySlug: Object.create(null) };

function escapeHtml(value = '') {
  return String(value).replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  })[char]);
}

function renderPage(html, slug, meta) {
  const assetPath = '/views/' + encodeURIComponent(slug) + '/';
  const title = escapeHtml(meta.title);
  const description = escapeHtml(meta.description);
  const image = meta.image ? escapeHtml(new URL(assetPath + meta.image, origin).href) : '';
  const code = escapeHtml(meta.code);
  const area = escapeHtml(meta.area);
  const head = [
    '<meta name="viewport" content="width=device-width, initial-scale=1.0">',
    title && '<title>' + title + '</title>',
    description && '<meta name="description" content="' + description + '">',
    '<meta name="author" content="MDA Wiki">',
    '<meta name="reply-to" content="contato@mda.wiki.br">',
    '<meta property="og:type" content="article">',
    title && '<meta property="og:title" content="' + title + '">',
    description && '<meta property="og:description" content="' + description + '">',
    image && '<meta property="og:image" content="' + image + '">',
    '<meta property="og:url" content="' + escapeHtml(origin) + '/' + encodeURIComponent(slug) + '/">',
    '<meta property="og:site_name" content="MDA Wiki">',
    '<meta property="og:locale" content="pt_BR">',
    '<meta name="og:email" content="contato@mda.wiki.br">',
    '<meta name="twitter:card" content="summary_large_image">',
    title && '<meta name="twitter:title" content="' + title + '">',
    description && '<meta name="twitter:description" content="' + description + '">',
    image && '<meta name="twitter:image" content="' + image + '">',
    '<link rel="shortcut icon" href="/favicon.png">',
    '<base href="' + assetPath + '">',
  ].filter(Boolean).join('\n');
  const footer = [
    '<style>' + footerStyle + '</style>',
    '<footer class="main-footer">',
    '<div><span>' + code + ' · &nbsp;</span><a href="' + escapeHtml(meta['reference-link']) + '" target="_blank" rel="noopener">' + title + '</a></div>',
    '<div><span>Descubra mais em &nbsp;</span><a href="https://mda.wiki.br" target="_blank" rel="noopener">MDA Wiki</a></div>',
    '</footer>',
  ].join('\n');
  const tags = '<span class="meta-tag"><span class="meta-key">Cód</span> <span class="meta-value">' + code +
    '</span></span><span class="meta-tag"><span class="meta-key">Área</span> <span class="meta-value">' + area + '</span></span>';

  return html
    .replace(/<head\b[^>]*>/i, (tag) => tag + '\n' + head)
    .replace(/<\/body>/i, (tag) => footer + '\n' + tag)
    .replace(/<[^>]*\bdata-id="meta"[^>]*>/i, (tag) => tag + '\n' + tags)
    .replace(/<[^>]*\bdata-id="meta-area"[^>]*>/i, (tag) => tag + '\n' + area);
}

async function readOptional(path) {
  try {
    return await readFile(path, 'utf8');
  } catch (error) {
    if (error.code === 'ENOENT') return null;
    throw error;
  }
}

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
  manifest.bySlug[slug] = meta ?? [];
  await mkdir(join(output, slug));
  await writeFile(join(output, slug, 'index.html'), renderPage(html, slug, meta ?? {}));
}

const notFound = await readFile('not-found.php', 'utf8');
await writeFile(join(output, '404.html'), notFound.replace('<?= URL ?>', '/'));
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
