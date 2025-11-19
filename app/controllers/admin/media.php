<?php

import('app/services/storage.php');

// ディレクトリを取得
if (!isset($_GET['directory'])) {
    $_GET['directory'] = '';
}

// 入力データを検証
if (!preg_match('/^[\w\-\.\/]+$/', $_GET['directory']) || preg_match('/\.\./', $_GET['directory']) || preg_match('/\/\//', $_GET['directory']) || preg_match('/\/$/', $_GET['directory'])) {
    $_GET['directory'] = '';
}
if ($GLOBALS['authority']['power'] < 3 && ($_GET['directory'] === '' || preg_match('/^' . preg_quote($GLOBALS['config']['media_author_dir'], '/') . '$/', $_GET['directory']))) {
    $_GET['directory'] = $GLOBALS['config']['media_author_dir'];

    if ($GLOBALS['config']['storage_type'] === 'file' && !is_dir($GLOBALS['config']['file_target']['media'] . $_GET['directory'])) {
        $result = directory_mkdir($GLOBALS['config']['file_target']['media'] . $_GET['directory']);
    }

    $_view['media_author_dir'] = true;
} else {
    $_view['media_author_dir'] = false;
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
