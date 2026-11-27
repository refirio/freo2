<?php

import('app/services/export.php');

// HTML出力（設定で有効にしたときだけ使える）
if (!$GLOBALS['setting']['export_use_html']) {
    error('設定「HTML出力」が有効ではありません。');
}

// サーバーに出力したページを削除
if (isset($_GET['clear'])) {
    if (!service_export_clear()) {
        error('出力したページを削除できません。');
    }

    ok('出力したページを削除しました（' . $GLOBALS['config']['export_directory'] . '）。');
}

// 出力先（サーバーか、ZIPファイル）
if (isset($_GET['directory'])) {
    $target = 'directory';
} else {
    $target = 'zip';

    if (empty($_GET['zip']) || !is_string($_GET['zip']) || !preg_match('/\.zip$/i', $_GET['zip'])) {
        error('出力先を指定してください。（例: サーバーに出力するときは php index.php "/cli/export?directory"、ZIPファイルに出力するときは php index.php "/cli/export?zip=/path/to/export.zip"）');
    }
    if (!is_dir(dirname($_GET['zip'])) || !is_writable(dirname($_GET['zip']))) {
        error('出力先のディレクトリに書き込めません。');
    }
}

// 出力する前の確認
$check = service_export_check($target);
if (!empty($check['errors'])) {
    error(implode(' ', $check['errors']));
}
foreach ($check['notices'] as $notice) {
    echo 'NOTICE: ' . $notice . "\n";
}

// 出力するURLの一覧を作成
$export   = service_export_start($target);
$replaces = $target === 'directory' ? null : service_export_replaces();
$failures = [];
$skips    = [];

echo 'Pages: ' . count($export['urls']) . "\n";

// ページを1つずつ描画して保存（サーバーに出力するときは、URLを置き換えない。フォームのあるページは出力しない）
foreach ($export['urls'] as $index => $url) {
    $body = service_export_render($export['token'], $url['url']);

    if ($body === null) {
        $failures[] = $url['url'] . '（描画できません）';
    } elseif ($target === 'directory' && service_export_dynamic($body)) {
        $skips[] = $url['url'];
    } elseif (!service_export_save($export['token'], $url['file'], $target === 'directory' ? $body : service_export_convert($body, $replaces))) {
        $failures[] = $url['url'] . '（保存できません）';
    }

    echo '[' . ($index + 1) . '/' . count($export['urls']) . '] ' . $url['url'] . "\n";
}

if ($target === 'directory') {
    // サーバーに出力する
    if (!service_export_publish($export['token'])) {
        error('ページを出力できません。');
    }
} else {
    // 静的なファイルを複製して、ZIPにまとめる
    if (!service_export_static($export['token'])) {
        error('静的なファイルを複製できません。');
    }
    if (!service_export_zip($export['token'], $_GET['zip'])) {
        error('ZIPファイルを作成できません。');
    }
}

// 作業用のディレクトリを削除
directory_rmdir(service_export_dir($export['token']));

foreach ($skips as $skip) {
    echo 'SKIP: ' . $skip . '（フォームがあるので、PHPで表示します）' . "\n";
}
foreach ($failures as $failure) {
    echo 'FAILURE: ' . $failure . "\n";
}

if ($target === 'directory') {
    ok((count($export['urls']) - count($failures) - count($skips)) . ' / ' . count($export['urls']) . ' ページを出力しました（' . $GLOBALS['config']['export_directory'] . '）。');
} else {
    ok((count($export['urls']) - count($failures)) . ' / ' . count($export['urls']) . ' ページを出力しました（' . $_GET['zip'] . '）。');
}
