export default {
  async fetch(request, env) {
    const url = new URL(request.url);
    if (url.pathname === '/') {
      if (env.LOCAL_CATALOG === 'true') return env.ASSETS.fetch(request);
      return Response.redirect('https://mda.wiki.br/' + url.search, 302);
    }
    if (url.pathname === '/_metadata.json') {
      return new Response('Not Found', { status: 404 });
    }

    const [, slug, action] = url.pathname.split('/');
    if (slug !== 'meta' && action !== 'meta') {
      return env.ASSETS.fetch(request);
    }

    let key;
    try {
      key = decodeURIComponent(slug === 'meta' ? action ?? '' : slug);
    } catch {
      return new Response('Bad Request', { status: 400 });
    }

    const response = await env.ASSETS.fetch(new URL('/_metadata.json', url));
    const { byCode, bySlug } = await response.json();
    let data;
    if (slug === 'meta') {
      const withUrl = (meta) => ({ ...meta, url: url.origin + '/' + encodeURIComponent(meta.view) + '/' });
      data = key
        ? (Object.hasOwn(byCode, key) ? withUrl(byCode[key]) : [])
        : Object.fromEntries(Object.entries(byCode).map(([code, meta]) => [code, withUrl(meta)]));
    } else if (Object.hasOwn(bySlug, key)) {
      data = bySlug[key];
    } else {
      return env.ASSETS.fetch(request);
    }

    return request.method === 'HEAD'
      ? new Response(null, { headers: { 'Content-Type': 'application/json' } })
      : Response.json(data);
  },
};
