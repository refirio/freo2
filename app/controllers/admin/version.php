<?php

// タイトル
$_view['title'] = 'バージョン情報';

// Webサーバー
$server_version = $_SERVER['SERVER_SOFTWARE'];

if (preg_match('/^(\S+)/', $server_version, $matches)) {
    $server_version = str_replace('/', ' ', $matches[1]);
}

// PHP
$php_version = 'PHP ' . PHP_VERSION;

// 画像処理（メディアのサムネイルを作成できる形式を確認する）
$gd_formats = [];

foreach ([
    'GIF'  => ['imagecreatefromgif', 'imagegif'],
    'JPEG' => ['imagecreatefromjpeg', 'imagejpeg'],
    'PNG'  => ['imagecreatefrompng', 'imagepng'],
] as $format => $functions) {
    $available = true;

    foreach (array_merge(['imagecreatetruecolor', 'imagecopyresampled'], $functions) as $function) {
        if (!function_exists($function)) {
            $available = false;

            break;
        }
    }

    if ($available) {
        $gd_formats[] = $format;
    }
}

if (empty($gd_formats)) {
    $gd_version = '利用できません';
} else {
    $gd_info = [];
    if (function_exists('gd_info')) {
        $gd_info = gd_info();
    }

    if (empty($gd_info['GD Version'])) {
        $gd_version = 'GD';
    } else {
        $gd_version = 'GD ' . $gd_info['GD Version'];
    }

    $gd_version .= ' / ' . implode('・', $gd_formats);
}

// データベース
$resource = db_query('SELECT VERSION() AS version;');
$results  = db_result($resource);

$database_version = $results[0]['version'];

if (preg_match('/^([^\-]+)\-MariaDB/', $database_version, $matches)) {
    $database_version = 'MariaDB ' . $matches[1];
} else {
    $database_version = 'MySQL ' . $database_version;
}

// バージョン情報
$_view['version_info'] = [
    'server_version'   => $server_version,
    'php_version'      => $php_version,
    'gd_version'       => $gd_version,
    'database_version' => $database_version,
];

// AWS SDK（S3 を使っているときだけ表示する）
$_view['aws_info'] = null;

if ($GLOBALS['config']['storage_type'] === 's3') {
    import('app/services/storage.php');

    // 記事の画像を1つ選び、署名を付けずに開けるかを確かめる（実在するファイルでないと、無いのか塞がれているのか区別できないため）
    $entries = model('select_entries', [
        'select'   => 'id, thumbnail, pictures',
        'where'    => 'thumbnail IS NOT NULL OR pictures IS NOT NULL',
        'order_by' => 'id DESC',
        'limit'    => 1,
    ]);

    $direct_key    = null;
    $direct_status = null;

    if (!empty($entries)) {
        $pictures = explode("\n", (string)$entries[0]['pictures']);
        $name     = $entries[0]['thumbnail'] ? $entries[0]['thumbnail'] : $pictures[0];

        $direct_key    = $GLOBALS['config']['file_target']['entry'] . $entries[0]['id'] . '/' . $name;
        $direct_status = service_storage_status($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $entries[0]['id'] . '/' . rawurlencode($name));
    }

    // 確認の結果（設定と食い違っているときは注意を出す）
    if ($direct_key === null) {
        $direct_level = 'secondary';
        $direct_label = '確認できません';
        $direct_text  = '画像のある記事がありません。';
    } elseif ($direct_status === null) {
        $direct_level = 'secondary';
        $direct_label = '確認できません';
        $direct_text  = 'バケットに接続できません。';
    } elseif ($direct_status === 200 && $GLOBALS['config']['storage_protect']) {
        $direct_level = 'warning';
        $direct_label = '注意';
        $direct_text  = '記事の画像を直接開けます。バケットポリシーで、メディア以外の公開を止めてください。';
    } elseif ($direct_status === 200) {
        $direct_level = 'info';
        $direct_label = '設定どおり';
        $direct_text  = '記事の画像を直接開けます。';
    } elseif ($direct_status === 403 && $GLOBALS['config']['storage_protect']) {
        $direct_level = 'success';
        $direct_label = '問題なし';
        $direct_text  = '記事の画像は直接開けません。（署名付きURLで表示しています。）';
    } elseif ($direct_status === 403) {
        $direct_level = 'warning';
        $direct_label = '注意';
        $direct_text  = '記事の画像を直接開けないため、画面に表示されません。設定 APP_STORAGE_PROTECT を有効にするか、バケットポリシーを見直してください。';
    } else {
        $direct_level = 'secondary';
        $direct_label = '確認できません';
        $direct_text  = 'HTTP ' . $direct_status . ' が返りました。';
    }

    $_view['aws_info'] = [
        'sdk_version'   => class_exists('Aws\Sdk') ? 'AWS SDK for PHP ' . Aws\Sdk::VERSION : '不明',
        'bucket'        => $GLOBALS['config']['aws_bucket'],
        'region'        => $GLOBALS['config']['aws_region'],
        'protect'       => $GLOBALS['config']['storage_protect'],
        'direct_key'    => $direct_key,
        'direct_status' => $direct_status,
        'direct_level'  => $direct_level,
        'direct_label'  => $direct_label,
        'direct_text'   => $direct_text,
    ];
}
