import { readFile } from 'node:fs/promises';

const footerStyle = await readFile(new URL('./footer.css', import.meta.url), 'utf8');

function escapeHtml(value = '') {
  return String(value).replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  })[char]);
}

export function renderPage(html, slug, meta, origin) {
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

function renderCatalogCard(slug, meta, position) {
  const path = encodeURIComponent(slug);
  const title = escapeHtml(meta.title ?? slug);
  const badges = [meta.code, meta.area, meta.type]
    .filter(Boolean)
    .map((value) => '<span class="badge">' + escapeHtml(value) + '</span>')
    .join('');
  const reference = escapeHtml(meta['reference-link']);
  const image = meta.image ? String(meta.image).replace(/^\/+/, '') : '';

  return [
    '<article class="card">',
    '<div class="content">',
    '<div class="title">' + position + '. <a href="/' + path + '/">' + title + '</a></div>',
    meta.description && '<div class="desc">' + escapeHtml(meta.description) + '</div>',
    badges && '<div class="meta">' + badges + '</div>',
    reference && '<div class="reference">Referência: <a href="' + reference + '" target="_blank" rel="noopener">' + reference + '</a></div>',
    '</div>',
    image && '<div class="thumb"><img loading="lazy" src="/views/' + path + '/' + escapeHtml(image) + '" alt=""><span>' + escapeHtml(image) + '</span></div>',
    '</article>',
  ].filter(Boolean).join('\n');
}

export async function readOptional(path) {
  try {
    return await readFile(path, 'utf8');
  } catch (error) {
    if (error.code === 'ENOENT') return null;
    throw error;
  }
}

export async function renderCatalog(catalog) {
  const template = await readFile(new URL('./catalog.html', import.meta.url), 'utf8');
  const cards = catalog.map(({ slug, meta }, index) => renderCatalogCard(slug, meta, index + 1));
  return template.replace('{{count}}', String(cards.length)).replace('{{cards}}', () => cards.join('\n'));
}
