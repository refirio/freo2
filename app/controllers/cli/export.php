<?php

import('app/services/export.php');

// HTML出力（設定で有効にしたときだけ使える）
if (!$GLOBALS['setting']['export_use_html']) {
    error('設定「HTML出力」が有効ではありません。');
}

// 出力先（ZIP ファイル）
if (empty($_GET['zip']) || !is_string($_GET['zip']) || !preg_match('/\.zip$/i', $_GET['zip'])) {
    error('出力先の ZIP ファイルを指定してください。（例: php index.php "/cli/export?zip=/path/to/export.zip"）');
}
if (!is_dir(dirname($_GET['zip'])) || !is_writable(dirname($_GET['zip']))) {
    error('出力先のディレクトリに書き込めません。');
}

// 出力する前の確認
$check = service_export_check();
if (!empty($check['errors'])) {
    error(implode(' ', $check['errors']));
}
foreach ($check['notices'] as $notice) {
    echo 'NOTICE: ' . $notice . "\n";
}

// 出力するURLの一覧を作成
$export   = service_export_start();
$replaces = service_export_replaces();
$failures = [];

echo 'Pages: ' . count($export['urls']) . "\n";

// ページを1つずつ描画して保存
foreach ($export['urls'] as $index => $url) {
    $body = service_export_render($export['token'], $url['url']);

    if ($body === null) {
        $failures[] = $url['url'] . '（描画できません）';
    } elseif (!service_export_save($export['token'], $url['file'], service_export_convert($body, $replaces))) {
        $failures[] = $url['url'] . '（保存できません）';
    }

    echo '[' . ($index + 1) . '/' . count($export['urls']) . '] ' . $url['url'] . "\n";
}

// 静的なファイルを複製して、ZIP にまとめる
if (!service_export_static($export['token'])) {
    error('静的なファイルを複製できません。');
}
if (!service_export_zip($export['token'], $_GET['zip'])) {
    error('ZIP ファイルを作成できません。');
}

// 作業用のディレクトリを削除
directory_rmdir(service_export_dir($export['token']));

foreach ($failures as $failure) {
    echo 'FAILURE: ' . $failure . "\n";
}

ok((count($export['urls']) - count($failures)) . ' / ' . count($export['urls']) . ' ページを出力しました（' . $_GET['zip'] . '）。');
