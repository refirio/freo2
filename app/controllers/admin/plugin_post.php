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
//db_transaction();

if ($_POST['exec'] == 'install') {
    // プラグインをインストール
    if (isset($GLOBALS['plugin'][$_POST['code']]['sql']['install'])) {
        foreach ($GLOBALS['plugin'][$_POST['code']]['sql']['install'] as $sql) {
            $resource = db_query($sql);
            if (!$resource) {
                error('プラグイン用SQLを実行できません。');
            }
        }
    }

    if (isset($GLOBALS['plugin'][$_POST['code']]['sql']['update'])) {
        foreach ($GLOBALS['plugin'][$_POST['code']]['sql']['update'] as $version => $update) {
            if (version_compare($GLOBALS['plugin'][$_POST['code']]['version'], $version, '>=')) {
                foreach ($update as $sql) {
                    $resource = db_query($sql);
                    if (!$resource) {
                        error('プラグイン用SQLを実行できません。');
                    }
                }
            }
        }
    }

    $resource = service_plugin_insert([
        'values' => [
            'code'    => $GLOBALS['plugin'][$_POST['code']]['code'],
            'version' => $GLOBALS['plugin'][$_POST['code']]['version'],
            'enabled' => 0,
            'setting' => isset($GLOBALS['plugin'][$_POST['code']]['setting_default']) ? json_encode($GLOBALS['plugin'][$_POST['code']]['setting_default']) : null,
        ],
    ]);
    if (!$resource) {
        error('プラグインをインストールできません。');
    }
} elseif ($_POST['exec'] == 'uninstall') {
    // プラグインをアンインストール
    if (isset($GLOBALS['plugin'][$_POST['code']]['sql']['uninstall'])) {
        foreach ($GLOBALS['plugin'][$_POST['code']]['sql']['uninstall'] as $sql) {
            $resource = db_query($sql);
            if (!$resource) {
                error('プラグイン用SQLを実行できません。');
            }
        }
    }

    $resource = service_plugin_delete([
        'where' => [
            'code = :code',
            [
                'code' => $_POST['code'],
            ],
        ],
    ]);
    if (!$resource) {
        error('プラグインをアンインストールできません。');
    }
} elseif ($_POST['exec'] == 'upgrade') {
    // プラグインをアップグレード
    $plugins = model('select_plugins', [
        'where' => [
            'code = :code',
            [
                'code' => $_POST['code'],
            ],
        ],
    ]);
    if (empty($plugins)) {
        error('プラグイン情報を取得できません。');
    }

    if (isset($GLOBALS['plugin'][$_POST['code']]['sql']['upgrade'])) {
        foreach ($GLOBALS['plugin'][$_POST['code']]['sql']['upgrade'] as $version => $upgrade) {
            if (version_compare($GLOBALS['plugin'][$_POST['code']]['version'], $version, '>=') && version_compare($plugins[0]['version'], $version, '<')) {
                foreach ($upgrade as $sql) {
                    $resource = db_query($sql);
                    if (!$resource) {
                        error('プラグイン用SQLを実行できません。');
                    }
                }
            }
        }
    }

    if (isset($GLOBALS['plugin'][$_POST['code']]['setting_default'])) {
        $setting = json_decode($plugins[0]['setting'], true);

        $flag = false;
        foreach ($GLOBALS['plugin'][$_POST['code']]['setting_default'] as $key => $value) {
            if (!isset($setting[$key])) {
                $setting[$key] = $value;

                $flag = true;
            }
        }

        if ($flag === true) {
            $resource = service_plugin_update([
                'set'   => [
                    'setting' => json_encode($setting),
                ],
                'where' => [
                    'code = :code',
                    [
                        'code' => $_POST['code'],
                    ],
                ],
            ]);
            if (!$resource) {
                error('プラグイン設定を更新できません。');
            }
        }
    }

    $resource = service_plugin_update([
        'set'   => [
            'version' => $GLOBALS['plugin'][$_POST['code']]['version'],
        ],
        'where' => [
            'code = :code',
            [
                'code' => $_POST['code'],
            ],
        ],
    ]);
    if (!$resource) {
        error('プラグインのバージョンを更新できません。');
    }
} elseif ($_POST['exec'] == 'setting') {
    // プラグイン設定を更新
    $setting = [];
    foreach ($GLOBALS['plugin'][$_POST['code']]['setting_define'] as $key => $value) {
        if (isset($_POST['setting'][$key])) {
            if ($value['required'] && $_POST['setting'][$key] === '') {
                error($value['name'] . 'が入力されていません。');
            } elseif ($value['type'] == 'number' && !is_numeric($_POST['setting'][$key])) {
                error($value['name'] . 'は半角数字で入力してください。');
            }
        }
        if (isset($_POST['setting'][$key])) {
            $setting[$key] = $_POST['setting'][$key];
        } else {
            $setting[$key] = 0;
        }
    }

    $resource = service_plugin_update([
        'set'   => [
            'setting' => json_encode($setting),
        ],
        'where' => [
            'code = :code',
            [
                'code' => $_POST['code'],
            ],
        ],
    ]);
    if (!$resource) {
        error('プラグイン設定を更新できません。');
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
        error('プラグインを有効化できません。');
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
        error('プラグインを無効化できません。');
    }
} else {
    error('不正なアクセスです。');
}

// トランザクションを終了
//db_commit();

// リダイレクト
redirect('/admin/plugin?ok=' . $_POST['exec']);
