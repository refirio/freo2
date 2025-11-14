<?php

import('app/services/storage.php');

// ディレクトリを取得
if (!isset($_GET['directory'])) {
    $_GET['directory'] = '';
}

// 入力データを検証
if (!preg_match('/^[\w\-\/]+$/', $_GET['directory']) || preg_match('/\/\//', $_GET['directory']) || preg_match('/\/$/', $_GET['directory'])) {
    $_GET['directory'] = '';
}
if ($_GET['directory'] === '') {
    $directory = '';
} else {
    $directory = $_GET['directory'] . '/';
}

// メディアを取得
$_view['medias'] = service_storage_list($GLOBALS['config']['file_target']['media'] . $directory);

// タイトル
$_view['title'] = 'メディア管理';
