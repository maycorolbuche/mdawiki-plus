<?php
$metaFiles = [];
if (is_dir(DIR_VIEW)) {
    $subdirs = array_filter(glob(DIR_VIEW . DIRECTORY_SEPARATOR . '*'), 'is_dir');
    foreach ($subdirs as $subdir) {
        $metaFile = $subdir . DIRECTORY_SEPARATOR . 'meta.json';
        if (file_exists($metaFile)) {
            $parts = explode(DIRECTORY_SEPARATOR, $subdir);

            $file = [];
            $file["path"] = $metaFile;
            $file["view"] = end($parts);
            $metaFiles[] = $file;
        }
    }
}
$metaData = [];

foreach ($metaFiles as $file) {
    $content = file_get_contents($file["path"]);
    if ($content !== false) {
        $data = json_decode($content, true);
        if ($data !== null) {
            $data["view"] = $file["view"];
            $data["url"] = URL . $file["view"] . '/';
            $metaData[$data['code']] = $data;
        }
    }
}

header('Content-Type: application/json');
if (ACTION !== "") {
    echo json_encode($metaData[ACTION] ?? []);
    exit;
}
echo json_encode($metaData);
