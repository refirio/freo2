<?php

import('app/services/theme.php');

// ワンタイムトークン
if (!token('check')) {
    error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
}

// アクセス元
if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
    error('不正なアクセスです。');
}

// 権限
if ($GLOBALS['authority']['power'] < 3) {
    error('不正なアクセスです。');
}

// テーマを取得
import(MAIN_PATH . $GLOBALS['config']['theme_path'] . $_POST['code'] . '/' . 'config.php');

// トランザクションを開始
db_transaction();

if ($_POST['exec'] == 'install') {
    // テーマをインストール
    $resource = service_theme_insert([
        'values' => [
            'code'    => $GLOBALS['theme'][$_POST['code']]['code'],
            'version' => $GLOBALS['theme'][$_POST['code']]['version'],
            'enabled' => 0,
            'setting' => isset($GLOBALS['theme'][$_POST['code']]['default']) ? json_encode($GLOBALS['theme'][$_POST['code']]['default']) : null,
        ],
    ]);
    if (!$resource) {
        error('データを登録できません。');
    }
} elseif ($_POST['exec'] == 'uninstall') {
    // テーマをアンインストール
    $resource = service_theme_delete([
        'where' => [
            'code = :code',
            [
                'code' => $_POST['code'],
            ],
        ],
    ]);
    if (!$resource) {
        error('データを削除できません。');
    }
} elseif ($_POST['exec'] == 'enable') {
    // 他のテーマを無効化
    $resource = service_theme_update([
        'set'   => [
            'enabled' => 0,
        ],
        'where' => 'enabled = 1',
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }

    // テーマを有効化
    $resource = service_theme_update([
        'set'   => [
            'enabled' => 1,
        ],
        'where' => [
            'code = :code',
            [
                'code' => $_POST['code'],
            ],
        ],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }
} elseif ($_POST['exec'] == 'disable') {
    // テーマを無効化
    $resource = service_theme_update([
        'set'   => [
            'enabled' => 0,
        ],
        'where' => [
            'code = :code',
            [
                'code' => $_POST['code'],
            ],
        ],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }
} else {
    error('不正なアクセスです。');
}

// トランザクションを終了
db_commit();

// リダイレクト
redirect('/admin/theme?ok=' . $_POST['exec']);
