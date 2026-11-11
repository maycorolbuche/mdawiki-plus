import { createReadStream, watch } from 'node:fs';
import { readFile, readdir, realpath, stat } from 'node:fs/promises';
import { createServer } from 'node:http';
import { extname, join, sep } from 'node:path';
import { Readable } from 'node:stream';
import { pipeline } from 'node:stream/promises';
import { readOptional, renderCatalog, renderPage } from './render.mjs';
import worker from './worker.js';

const origin = 'http://localhost:8006';
const viewsDir = await realpath('views');
const notFound = await readFile(new URL('./404.html', import.meta.url), 'utf8');
const clients = new Set();
const contentTypes = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.mjs': 'text/javascript; charset=utf-8',
  '.json': 'application/json',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.webp': 'image/webp',
  '.avif': 'image/avif',
  '.gif': 'image/gif',
  '.ico': 'image/x-icon',
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
  '.ttf': 'font/ttf',
  '.pdf': 'application/pdf',
  '.mp3': 'audio/mpeg',
  '.ogg': 'audio/ogg',
  '.wav': 'audio/wav',
  '.mp4': 'video/mp4',
  '.webm': 'video/webm',
};

function htmlResponse(html, status = 200) {
  const script = '<script>new EventSource("/__reload").addEventListener("change", () => location.reload());</script>';
  const body = /<\/body>/i.test(html)
    ? html.replace(/<\/body>/i, (tag) => script + tag)
    : html + script;
  return new Response(body, { status, headers: { 'Content-Type': contentTypes['.html'] } });
}

async function loadPage(slug) {
  const [html, rawMeta] = await Promise.all([
    readOptional(join(viewsDir, slug, 'index.html')),
    readOptional(join(viewsDir, slug, 'meta.json')),
  ]);
  return { slug, html, meta: rawMeta === null ? null : JSON.parse(rawMeta) };
}

async function loadPages() {
  const entries = await readdir(viewsDir, { withFileTypes: true });
  return Promise.all(entries
    .filter((entry) => entry.isDirectory() && !entry.name.startsWith('.'))
    .sort((a, b) => a.name.localeCompare(b.name))
    .map((entry) => loadPage(entry.name)));
}

async function metadataResponse() {
  const pages = await loadPages();
  return Response.json({
    byCode: Object.fromEntries(pages
      .filter(({ meta }) => meta?.code != null)
      .map(({ slug, meta }) => [meta.code, { ...meta, view: slug }])),
    bySlug: Object.fromEntries(pages
      .filter(({ html }) => html !== null)
      .map(({ slug, meta }) => [slug, meta ?? []])),
  });
}

async function fileResponse(path, request) {
  const file = await stat(path);
  if (!file.isFile()) return htmlResponse(notFound, 404);
  const headers = {
    'Content-Type': contentTypes[extname(path).toLowerCase()] ?? 'application/octet-stream',
    'Content-Length': String(file.size),
  };
  const body = request.method === 'HEAD' ? null : Readable.toWeb(createReadStream(path));
  return new Response(body, { headers });
}

async function fetchAsset(input) {
  const url = new URL(input.url ?? input);
  if (url.pathname === '/_metadata.json') return metadataResponse();
  if (url.pathname === '/') {
    const pages = await loadPages();
    return htmlResponse(await renderCatalog(pages.filter(({ html }) => html !== null)));
  }
  try {
    const segments = url.pathname.split('/').filter(Boolean).map(decodeURIComponent);
    if (segments.some((part) => part.startsWith('.') || /[\\/:\x00]/.test(part))) {
      return htmlResponse(notFound, 404);
    }
    const [slug] = segments;
    if (slug === 'favicon.png' && segments.length === 1) return await fileResponse(slug, input);
    if (slug === 'views' && segments.length > 1) {
      const path = await realpath(join(viewsDir, ...segments.slice(1)));
      if (path.startsWith(viewsDir + sep) && !/\.(?:php\d*|phtml|phar)$/i.test(path)) {
        return await fileResponse(path, input);
      }
    } else if (segments.length === 1 || (segments.length === 2 && segments[1] === 'index.html')) {
      const { html, meta } = await loadPage(slug);
      if (html !== null) return htmlResponse(renderPage(html, slug, meta ?? {}, origin));
    }
  } catch (error) {
    if (!['ENOENT', 'ENOTDIR'].includes(error.code) && !(error instanceof URIError)) throw error;
  }
  return htmlResponse(notFound, 404);
}

const server = createServer(async (incoming, outgoing) => {
  try {
    const url = new URL(incoming.url, origin);
    if (url.pathname === '/__reload' && incoming.method === 'GET') {
      outgoing.writeHead(200, {
        'Content-Type': 'text/event-stream',
        'Cache-Control': 'no-store',
      });
      outgoing.write(': connected\n\n');
      clients.add(outgoing);
      outgoing.on('close', () => clients.delete(outgoing));
      return;
    }

    const request = new Request(url, { method: incoming.method });
    const response = await worker.fetch(request, { LOCAL_CATALOG: 'true', ASSETS: { fetch: fetchAsset } });
    const headers = Object.fromEntries(response.headers);
    outgoing.writeHead(response.status, { ...headers, 'Cache-Control': 'no-store' });
    if (response.body && incoming.method !== 'HEAD') {
      await pipeline(Readable.fromWeb(response.body), outgoing);
    } else {
      outgoing.end();
    }
  } catch (error) {
    if (outgoing.destroyed) return;
    console.error(error);
    if (!outgoing.headersSent) outgoing.writeHead(500, { 'Content-Type': 'text/plain; charset=utf-8' });
    outgoing.end('Erro ao ler os arquivos locais. Confira o terminal.');
  }
});

let pendingReload;
const watcher = watch(viewsDir, { recursive: true }, () => {
  clearTimeout(pendingReload);
  pendingReload = setTimeout(() => {
    for (const client of clients) client.write('event: change\ndata: reload\n\n');
  }, 150);
});
server.on('close', () => watcher.close());
server.on('error', () => watcher.close());
server.listen(8006, 'localhost', () => console.log('MDAWiki Plus: ' + origin));
