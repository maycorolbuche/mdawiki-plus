<?php

$view_file = DIR_VIEW . VIEW . DIRECTORY_SEPARATOR . 'index.html';
$meta_file = DIR_VIEW . VIEW . DIRECTORY_SEPARATOR . 'meta.json';

define('URL_PAGE', URL . VIEW . '/');
define('URL_ASSETS', URL . 'views/' . VIEW . '/');

if (file_exists($view_file)) {
    $html = file_get_contents($view_file);
    $meta = [];

    if (file_exists($meta_file)) {
        $meta = json_decode(file_get_contents($meta_file), true);
    }

    if (ACTION === "meta") {
        header('Content-Type: application/json');
        echo json_encode($meta);
        exit;
    }

    $head = "";
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

    $footer = "";
    $footer .= "<style>";
    $footer .= "/* RESET COMPLETO para o footer e seus filhos */";
    $footer .= ".main-footer,.main-footer *{";
    $footer .= "    all: initial !important;"; /* Reseta todas as propriedades CSS */
    $footer .= "    display: block !important;";
    $footer .= "    margin: 0 !important;";
    $footer .= "    padding: 0 !important;";
    $footer .= "    box-sizing: border-box !important;";
    $footer .= "    color: #999 !important;";
    $footer .= "    font-family: inter, sans-serif !important;";
    $footer .= "    font-size: 12px !important;";
    $footer .= "    line-height: 1.5 !important;";
    $footer .= "}";
    $footer .= ".main-footer {";
    $footer .= "    background: #000 !important;";
    $footer .= "    text-align: center !important;";
    $footer .= "    padding: 20px !important;";
    $footer .= "    font-size: 0.9em !important;";
    $footer .= "    color: #999 !important;";
    $footer .= "    font-family: Arial, sans-serif !important;";
    $footer .= "    width: 100% !important;";
    $footer .= "    position: relative !important;";
    $footer .= "    z-index: 9999 !important;";
    $footer .= "}";
    $footer .= ".main-footer div {";
    $footer .= "    display: flex !important;";
    $footer .= "    text-align: center !important;";
    $footer .= "    justify-content: center !important;";
    $footer .= "}";
    $footer .= ".main-footer a {";
    $footer .= "    text-decoration: underline !important;";
    $footer .= "    background: transparent !important;";
    $footer .= "    border: none !important;";
    $footer .= "    cursor: pointer !important;";
    $footer .= "}";
    $footer .= "";
    $footer .= ".main-footer a:hover {";
    $footer .= "    text-decoration: none !important;";
    $footer .= "    opacity: 0.8 !important;";
    $footer .= "}";
    $footer .= "</style>";
    $footer .= "<footer class='main-footer'>";
    $footer .= "<div><span>" . @$meta["code"] . " · &nbsp;</span><a href='" . @$meta["reference-link"] . "' target='_blank'>" . @$meta["title"] . "</a></div>";
    $footer .= "<div><span>Descubra mais em  &nbsp;</span><a href='https://mda.wiki.br' target='_blank'>MDA Wiki</a></div>";
    $footer .= "</footer>";
    $html = preg_replace(
        '/<\/body>/i',
        $footer . PHP_EOL . '</body>',
        $html,
        1
    );



    $meta_info = "";
    $meta_info .= "<span><b>Cód:</b> " . @$meta["code"] . "</span> ·  ";
    $meta_info .= "<span><b>Área:</b> " . @$meta["area"] . "</span>";

    $html = preg_replace(
        '/<([^>]*data-id="meta"[^>]*)>/i',
        '<$1>' . PHP_EOL . $meta_info,
        $html,
        1
    );

    $html = preg_replace(
        '/<([^>]*data-id="meta-area"[^>]*)>/i',
        '<$1>' . PHP_EOL . @$meta["area"],
        $html,
        1
    );

    echo $html;
} else {
    include("not-found.php");
}
