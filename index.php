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
    $h = explode(':', $host);

    if ($h[0] !== 'localhost' && $h[0] !== '127.0.0.1') {
        header('Location: https://mda.wiki.br' . ($_SERVER['REQUEST_URI'] ?? '/'));
        exit;
    }

    $views = [];
    foreach (array_filter(glob(DIR_VIEW . DIRECTORY_SEPARATOR . '*'), 'is_dir') as $file) {
        $views[] = basename($file, '.php');
    }
    sort($views);

    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Views</title></head><body>';
    echo '<h1>Views</h1><ul>';
    foreach ($views as $indx => $view) {
        $link = htmlspecialchars(URL . $view, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($view, ENT_QUOTES, 'UTF-8');
        echo '<li><b>' . ($indx + 1) . '.</b> <a href="' . $link . '">' . $label . '</a></li>';
    }
    echo '</ul></body></html>';
    exit;
}

if (VIEW === "meta") {
    include DIR . DIRECTORY_SEPARATOR . 'meta.php';
    exit;
}

include DIR . DIRECTORY_SEPARATOR . 'view.php';
