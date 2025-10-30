<?php

// プラグインを取得
if (!isset($_GET['id']) || !preg_match('/^[\w\-]+$/', $_GET['id'])) {
    error('不正なアクセスです。');
}

$target_dir = MAIN_PATH . $GLOBALS['config']['plugin_path'] . $_GET['id'] . '/';

if (!file_exists($target_dir . 'config.php')) {
    error('不正なアクセスです。');
}

import($target_dir . 'config.php');

$_view['plugin'] = $GLOBALS['plugin'][$_GET['id']];

// タイトル
$_view['title'] = 'プラグイン詳細';
