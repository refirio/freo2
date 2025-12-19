<?php

import('app/services/plugin.php');

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

// プラグインを取得
import(MAIN_PATH . $GLOBALS['config']['plugin_path'] . $_POST['code'] . '/' . 'config.php');

// トランザクションを開始
db_transaction();

if ($_POST['exec'] == 'install') {
    // プラグインをインストール
    $resource = service_plugin_insert([
        'values' => [
            'code'    => $GLOBALS['plugin'][$_POST['code']]['code'],
            'version' => $GLOBALS['plugin'][$_POST['code']]['version'],
            'enabled' => 0,
            'setting' => json_encode($GLOBALS['plugin'][$_POST['code']]['default']),
        ],
    ]);
    if (!$resource) {
        error('データを登録できません。');
    }
} elseif ($_POST['exec'] == 'uninstall') {
    // プラグインをアンインストール
    $resource = service_plugin_delete([
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
    // プラグインを有効化
    $resource = service_plugin_update([
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
    // プラグインを無効化
    $resource = service_plugin_update([
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
redirect('/admin/plugin?ok=' . $_POST['exec']);
