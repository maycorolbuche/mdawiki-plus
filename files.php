<?php

$views = [];

foreach (array_filter(glob(DIR_VIEW . DIRECTORY_SEPARATOR . '*'), 'is_dir') as $dir) {

    $folder = basename($dir);

    $metaFile = $dir . DIRECTORY_SEPARATOR . 'meta.json';

    $meta = [];

    if (file_exists($metaFile)) {

        $json = file_get_contents($metaFile);

        $meta = json_decode($json, true);

        if (!is_array($meta)) {
            $meta = [];
        }
    }

    $views[] = [
        'folder' => $folder,
        'meta'   => $meta
    ];
}

usort($views, function ($a, $b) {
    return strcmp($a['folder'], $b['folder']);
});

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Views</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            color: #333;
        }

        h1 {
            margin-bottom: 30px;
            font-size: 25px;
        }

        .cards {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;

            border: 1px solid #e5e7eb;

            box-shadow:
                0 2px 4px rgba(0, 0, 0, .03),
                0 8px 24px rgba(0, 0, 0, .04);

            transition: all .2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow:
                0 4px 8px rgba(0, 0, 0, .05),
                0 12px 28px rgba(0, 0, 0, .08);
        }

        .content {
            flex: 1;
            min-width: 0;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .title a {
            color: #111827;
            text-decoration: none;
        }

        .title a:hover {
            color: #2563eb;
        }

        .desc {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 14px;
            font-size: 14px;
        }

        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .badge {
            background: #eef2ff;
            color: #4338ca;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: bold;
        }

        .reference {
            margin-top: 12px;
            font-size: 13px;
        }

        .reference a {
            color: #2563eb;
            text-decoration: none;
        }

        .reference a:hover {
            text-decoration: underline;
        }

        .thumb {
            flex-shrink: 0;
            font-size: 10px;
            text-align: center;
            display: flex;
            align-items: center;
            flex-direction: column;
        }

        .thumb img {
            width: 95px;
            height: 95px;
            object-fit: cover;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            display: block;
            background: #fff;
        }

        @media (max-width: 700px) {

            .card {
                flex-direction: column-reverse;
                align-items: flex-start;
            }

            .thumb img {
                width: 100%;
                height: auto;
                max-height: 220px;
            }
        }
    </style>
</head>

<body>

    <h1>
        Views (<?= count($views) ?>)
    </h1>

    <div class="cards">

        <?php foreach ($views as $index => $item): ?>

            <?php

            $view = $item['folder'];
            $meta = $item['meta'];

            $link = htmlspecialchars(URL . $view, ENT_QUOTES, 'UTF-8');

            ?>

            <div class="card">

                <div class="content">

                    <div class="title">
                        <?= ($index + 1) ?>.

                        <a href="<?= $link ?>">
                            <?= htmlspecialchars($meta['title'] ?? $view, ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </div>

                    <?php if (!empty($meta['description'])): ?>
                        <div class="desc">
                            <?= htmlspecialchars($meta['description'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <div class="meta">

                        <?php if (!empty($meta['code'])): ?>
                            <div class="badge">
                                <?= htmlspecialchars($meta['code']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($meta['area'])): ?>
                            <div class="badge">
                                <?= htmlspecialchars($meta['area']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($meta['type'])): ?>
                            <div class="badge">
                                <?= htmlspecialchars($meta['type']) ?>
                            </div>
                        <?php endif; ?>

                    </div>

                    <?php if (!empty($meta['reference-link'])): ?>

                        <div class="reference">
                            Referência:
                            <a href="<?= htmlspecialchars($meta['reference-link']) ?>"
                                target="_blank">
                                <?= htmlspecialchars($meta['reference-link']) ?>
                            </a>
                        </div>

                    <?php endif; ?>

                </div>

                <?php if (!empty($meta['image'])): ?>

                    <?php
                    $img = URL . 'views/' . $view . '/' . ltrim($meta['image'], '/');
                    ?>

                    <div class="thumb">
                        <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="">
                        <legend><?= ltrim($meta['image']) ?></legend>
                    </div>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

</body>

</html>