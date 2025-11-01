<?php

// 表示対象を取得
if (!isset($_GET['code']) || !preg_match('/^[\w\-]+$/', $_GET['code'])) {
    error('不正なアクセスです。');
}

// プラグインを取得
$plugins = model('select_plugins', [
    'where' => [
        'code = :code',
        [
            'code' => $_GET['code'],
        ],
    ],
]);
if (empty($plugins)) {
    $_view['plugin'] = [];
} else {
    $_view['plugin'] = $plugins[0];
}

// プラグインファイルを読み込み
$target_dir = MAIN_PATH . $GLOBALS['config']['plugin_path'] . $_GET['code'] . '/';

if (!file_exists($target_dir . 'config.php')) {
    error('不正なアクセスです。');
}

import($target_dir . 'config.php');

// タイトル
$_view['title'] = 'プラグイン詳細';
