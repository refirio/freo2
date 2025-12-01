<?php

import('app/services/storage.php');

// ディレクトリを取得
if (!isset($_GET['directory'])) {
    $_GET['directory'] = '';
}

// 入力データを整理
if (!preg_match('/^[\w\-\.\/]+$/', $_GET['directory']) || preg_match('/\.\./', $_GET['directory']) || preg_match('/\/\//', $_GET['directory']) || preg_match('/\/$/', $_GET['directory'])) {
    $_GET['directory'] = '';
}
if ($GLOBALS['authority']['power'] == 3 && $_GET['directory'] === '') {
    $_view['current_dir'] = null;
    $_view['parent_dir']  = null;
} elseif ($GLOBALS['authority']['power'] < 3 && ($_GET['directory'] === '' || preg_match('/^' . preg_quote($GLOBALS['config']['media_author_dir'], '/') . '$/', $_GET['directory']))) {
    $_GET['directory'] = $GLOBALS['config']['media_author_dir'];

    if ($GLOBALS['config']['storage_type'] === 'file' && !is_dir($GLOBALS['config']['file_target']['media'] . $_GET['directory'])) {
        $result = directory_mkdir($GLOBALS['config']['file_target']['media'] . $_GET['directory']);
    }

    $_view['current_dir'] = $_GET['directory'];
    $_view['parent_dir']  = null;
} else {
    $_view['current_dir'] = $_GET['directory'];
    $_view['parent_dir']  = dirname($_GET['directory']);

    if ($_view['current_dir'] === '.') {
        $_GET['directory']    = '';
        $_view['current_dir'] = null;
        $_view['parent_dir']  = null;
    }
}

// メディアを取得
$_view['medias'] = service_storage_list($GLOBALS['config']['file_target']['media'] . ($_GET['directory'] ? $_GET['directory'] . '/' : ''));

// タイトル
$_view['title'] = 'メディア管理';
