<?php

$scheme = 'http';
if (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
) {
    $scheme = 'https';
}
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];

define('URL', $scheme . '://' . $host . '/');
define('DIR', __DIR__);
define('DIR_VIEW', __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);

$slug = $_SERVER['REQUEST_URI'];
$parts = explode('/', trim($slug, '/'));

define('VIEW', $parts[0] ?? "");
define('ACTION', $parts[1] ?? "");

if (VIEW === "") {
    echo "No view specified.";
} else {
    $view_file = DIR_VIEW . VIEW . DIRECTORY_SEPARATOR . 'index.html';
    $meta_file = DIR_VIEW . VIEW . DIRECTORY_SEPARATOR . 'meta.json';

    define('URL_PAGE', URL . VIEW . '/');
    define('URL_ASSETS', URL . 'views/' . VIEW . '/');

    if (file_exists($view_file)) {
        $html = file_get_contents($view_file);
        $head = "";
        $meta = [];

        if (file_exists($meta_file)) {
            $meta = json_decode(file_get_contents($meta_file), true);
        }

        if (ACTION === "meta") {
            header('Content-Type: application/json');
            echo json_encode($meta);
            exit;
        }

        $head .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . PHP_EOL;

        $head .= isset($meta['title']) ? "<title>" . htmlspecialchars($meta['title']) . "</title>" . PHP_EOL : "";
        $head .= isset($meta['description']) ? '<meta name="description" content="' . htmlspecialchars($meta['description']) . '">' . PHP_EOL : '';
        $head .= '<meta name="author" content="MDA Wiki">' . PHP_EOL;
        $head .= '<meta name="reply-to" content="contato@mda.wiki.br">' . PHP_EOL;

        //Open Graph (Facebook, WhatsApp, etc.)
        $head .= '<meta property="og:type" content="article">' . PHP_EOL;
        $head .= isset($meta['title']) ? '<meta property="og:title" content="' . htmlspecialchars($meta['title']) . '">' . PHP_EOL : '';
        $head .= isset($meta['description']) ? '<meta property="og:description" content="' . htmlspecialchars($meta['description']) . '">' . PHP_EOL : '';
        $head .= isset($meta['image']) ? '<meta property="og:image" content="' . URL_ASSETS . htmlspecialchars($meta['image']) . '">' . PHP_EOL : '';
        $head .= '<meta property="og:url" content="' . URL_PAGE . '">' . PHP_EOL;
        $head .= '<meta property="og:site_name" content="MDA Wiki">' . PHP_EOL;
        $head .= '<meta property="og:locale" content="pt_BR">' . PHP_EOL;
        $head .= '<meta name="og:email" content="contato@mda.wiki.br" />' . PHP_EOL;

        //Twitter Card
        $head .= '<meta name="twitter:card" content="summary_large_image">' . PHP_EOL;
        $head .= isset($meta['title']) ?  '<meta name="twitter:title" content="' . htmlspecialchars($meta['title']) . '">' . PHP_EOL : '';
        $head .= isset($meta['description']) ? '<meta name="twitter:description" content="' . htmlspecialchars($meta['description']) . '">' . PHP_EOL : '';
        $head .= isset($meta['image']) ? '<meta name="twitter:image" content="' . URL_ASSETS . htmlspecialchars($meta['image']) . '">' . PHP_EOL : '';

        $head .= '<link rel="shortcut icon" href="' . URL . 'favicon.png">' . PHP_EOL;
        $head .= '<base href="' . URL_ASSETS . '">' . PHP_EOL;

        $head = '<!-- Meta tags auto-generated -->' . PHP_EOL . $head . '<!-- End of meta tags auto-generated -->' . PHP_EOL;

        $html = preg_replace(
            '/<head(.*?)>/i',
            '<head$1>' . PHP_EOL . $head,
            $html,
            1
        );

        echo $html;
    } else {
        echo "File not found.";
    }
}
