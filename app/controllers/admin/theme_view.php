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

// 設定内容
if (empty($themes[0]['setting'])) {
    $_view['setting_sets'] = [];
} else {
    $_view['setting_sets'] = json_decode($themes[0]['setting'], true);
}
if (isset($GLOBALS['theme'][$_GET['code']]['setting_default'])) {
    foreach ($GLOBALS['theme'][$_GET['code']]['setting_default'] as $key => $value) {
        if (!isset($_view['setting_sets'][$key])) {
            $_view['setting_sets'][$key] = $value;
        }
    }
}

// 設定項目
if (isset($GLOBALS['theme'][$_GET['code']]['setting_define'])) {
    $_view['contents'] = $GLOBALS['theme'][$_GET['code']]['setting_define'];
} else {
    $_view['contents'] = [];
}

// タイトル
$_view['title'] = 'テーマ詳細';
