<?php

// 表示対象を取得
if (!isset($_GET['code']) || !preg_match('/^[\w\-]+$/', $_GET['code'])) {
    error('不正なアクセスです。');
}

// テーマを取得
$themes = model('select_themes', [
    'where' => [
        'code = :code',
        [
            'code' => $_GET['code'],
        ],
    ],
]);
if (empty($themes)) {
    $_view['theme'] = [];
} else {
    $_view['theme'] = $themes[0];
}

// テーマファイルを読み込み
$target_dir = MAIN_PATH . $GLOBALS['config']['theme_path'] . $_GET['code'] . '/';

if (!file_exists($target_dir . 'config.php')) {
    error('不正なアクセスです。');
}

import($target_dir . 'config.php');

// タイトル
$_view['title'] = 'テーマ詳細';
