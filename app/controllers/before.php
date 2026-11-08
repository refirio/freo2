<?php

import('app/version.php');
import('app/config.php');

import('app/services/user.php');
import('app/services/storage.php');
import('app/services/entry.php');
import('app/services/attribute.php');
import('app/services/update.php');

import('libs/modules/loader.php');

// セキュリティヘッダーを送る（PHPのバージョンは知らせない）
header_remove('X-Powered-By');

foreach ($GLOBALS['config']['security_headers'] as $header_name => $header_value) {
    header($header_name . ': ' . $header_value);
}
if ($GLOBALS['config']['hsts_max_age'] > 0 && ssl(true)) {
    header('Strict-Transport-Security: max-age=' . intval($GLOBALS['config']['hsts_max_age']));
}

// DB の更新待ち（未実行のマイグレーションがあれば、更新が済むまで通常の画面を出さない。テーブルが新しいコードと合わないことがあるため、設定などを読む前に判定する）
if (!empty(service_update_pending()) && !($_REQUEST['_mode'] === 'auth' && $_REQUEST['_work'] === 'index')) {
    if (service_update_admin()) {
        if ($_REQUEST['_mode'] === 'admin' && $_REQUEST['_work'] === 'update') {
            // 更新画面（プラグインを読み込む after.php は通さず、ここで表示して終了する）
            import('app/controllers/admin/update.php');

            $_view['token'] = token('create');

            import('app/views/admin/update.php');

            exit;
        }

        // 管理者のほかのアクセスは、更新画面へ
        redirect('/admin/update');
    } elseif ($_REQUEST['_mode'] === 'admin' && empty($_SESSION['auth']['user']['id'])) {
        // 未ログインで管理画面を開いたら、ログインしてから更新画面へ
        redirect('/auth/?referer=' . rawurlencode('/admin/update'));
    }

    // それ以外は、メンテナンス中
    header('HTTP/1.1 503 Service Unavailable');
    header('Retry-After: 600');
    header('Cache-Control: no-store');

    import('app/views/admin/update_maintenance.php');

    exit;
}

// 設定内容を取得
$settings = model('select_settings', []);
$GLOBALS['setting'] = [];
foreach ($settings as $setting) {
    $GLOBALS['setting'][$setting['id']] = $setting['value'];
}

// ストレージ利用準備
$config = [];
if ($GLOBALS['config']['storage_type'] === 's3') {
    $config = [
        'url'               => $GLOBALS['config']['storage_url'],
        'credential_key'    => $GLOBALS['config']['aws_credential_key'],
        'credential_secret' => $GLOBALS['config']['aws_credential_secret'],
        'region'            => $GLOBALS['config']['aws_region'],
        'version'           => $GLOBALS['config']['aws_version'],
        'bucket'            => $GLOBALS['config']['aws_bucket'],
    ];
}
service_storage_init($config);

// オートログイン
if (!preg_match('/^(auth)$/', $_REQUEST['_mode']) || !preg_match('/^(index|logout)$/', $_REQUEST['_work'])) {
    if (empty($_SESSION['auth']['user']) || localdate() - $_SESSION['auth']['user']['time'] > $GLOBALS['config']['login_expire']) {
        if (!empty($_COOKIE['auth']['session'])) {
            list($session, $user_id) = service_user_login($_COOKIE['auth']['session']);
            if ($session === true) {
                $_SESSION['auth']['user'] = [
                    'id'   => $user_id,
                    'time' => localdate(),
                ];
            }
        }
    }
}

// ユーザーを確認
if (!empty($_SESSION['auth']['user']['id'])) {
    $users = model('select_users', [
        'where' => [
            'id = :id AND enabled = 1',
            [
                'id' => $_SESSION['auth']['user']['id'],
            ],
        ],
    ]);
    if (empty($users)) {
        unset($_SESSION['auth']['user']);

        // リダイレクト
        redirect('/auth/home');
    } else {
        // ユーザー情報を取得
        $_view['_user'] = $users[0];

        // 権限を取得
        $authorities = model('select_authorities', [
            'where' => [
                'id = :id',
                [
                    'id' => $users[0]['authority_id'],
                ],
            ],
        ]);
        $GLOBALS['authority'] = $authorities[0];

        // 権限を確認
        if ($GLOBALS['authority']['power'] == 3) {
            // 管理者（全権限を持つ）
        } elseif ($GLOBALS['authority']['power'] == 2) {
            // 投稿者
            if (preg_match('/^(admin)$/', $_REQUEST['_mode'])) {
                if (preg_match('/^(attribute|export|field|log|menu|plugin|setting|theme|update|user|version|widget)(_|$)/', $_REQUEST['_work'])) {
                    error('不正なアクセスです。');
                }
            }
        } elseif ($GLOBALS['authority']['power'] == 1) {
            // 閲覧者
            if (preg_match('/^(admin)$/', $_REQUEST['_mode'])) {
                if (preg_match('/^(attribute|category|entry|export|field|file|log|media|menu|page|plugin|setting|theme|update|user|version|widget)(_|$)/', $_REQUEST['_work'])) {
                    error('不正なアクセスです。');
                }
            }
        } else {
            // ゲスト
            if (preg_match('/^(admin)$/', $_REQUEST['_mode'])) {
                error('不正なアクセスです。');
            }
        }

        // 属性を確認
        if ($GLOBALS['authority']['power'] === 0 && (($users[0]['attribute_begin'] && localdate(null, $users[0]['attribute_begin']) > localdate()) || ($users[0]['attribute_end'] && localdate(null, $users[0]['attribute_end']) < localdate()))) {
            $GLOBALS['attributes'] = [];
        } else {
            $attribute_sets = model('select_attribute_sets', [
                'where' => [
                    'user_id = :user_id',
                    [
                        'user_id' => $_SESSION['auth']['user']['id'],
                    ],
                ],
            ]);
            $GLOBALS['attributes'] = array_column($attribute_sets, 'attribute_id');
        }

        // フィルターを適用（ゲストが表示を選択していないフィルター対象の属性を除く）
        $GLOBALS['attribute_filterables'] = [];
        if ($GLOBALS['authority']['power'] === 0 && !empty($GLOBALS['attributes'])) {
            $GLOBALS['attribute_filterables'] = service_attribute_filterable($GLOBALS['attributes']);
            $GLOBALS['attributes']            = service_attribute_filter($GLOBALS['attributes'], array_column($GLOBALS['attribute_filterables'], 'id'), service_attribute_filter_get($users[0]['id']));
        }
    }
}

// HTML出力（設定で有効にしたときだけ使える）
if (preg_match('/^(admin)$/', $_REQUEST['_mode']) && preg_match('/^export(_|$)/', $_REQUEST['_work']) && !$GLOBALS['setting']['export_use_html']) {
    error('不正なアクセスです。');
}

if (!preg_match('/^(auth|admin)$/', $_REQUEST['_mode'])) {
    // ページURLの省略
    if ($GLOBALS['setting']['page_url_omission']) {
        if (isset($_params) && is_array($_params) && $_params[0] !== 'page') {
            $pages = service_entry_select_published('page', [
                'select' => 'entries.id, entries.public',
                'where'  => [
                    'entries.code = :code',
                    [
                        'code' => implode('/', $_params),
                    ],
                ],
            ]);
            if (!empty($pages)) {
                array_unshift($_params, 'page');
                $_REQUEST['_mode'] = $_params[0];
                $_REQUEST['_work'] = $_params[1];
            }
        }
    }

    // メニューを取得
    $menus = model('select_menus', [
        'where'    => 'enabled = 1',
        'order_by' => 'sort, id',
    ]);
    $_view['menus'] = [];
    foreach ($menus as $menu) {
        // HTML出力では、静的なページで使えない画面（お問い合わせ・ログイン）へのメニューを出さない
        if (app_export() && preg_match('/^\/(auth|contact)(\/|$)/', $menu['url'])) {
            continue;
        }

        if (preg_match('/^\//', $menu['url'])) {
            $menu['url'] = MAIN_FILE . $menu['url'];
        }
        $_view['menus'][] = $menu;
    }
}

// 設定項目定義を取得
import('app/setting.php');

// 文字列定義を取得
import('app/string.php');

// メニュー定義を取得
import('app/menu.php');

// HTML出力では、静的なページで使えない画面（お問い合わせ・ログイン）をメニューに出さない
if (app_export()) {
    foreach (['contact', 'auth'] as $key) {
        if (isset($GLOBALS['menu_contents']['public']['home'][$key])) {
            $GLOBALS['menu_contents']['public']['home'][$key]['show'] = false;
        }
    }
}

// ウィジェットを取得
$widgets = model('select_widgets', []);
$_view['widget_sets'] = [];
foreach ($widgets as $widget) {
    $_view['widget_sets'][$widget['code']] = $widget['text'];
}

// テーマを取得
$themes = model('select_themes', [
    'where'    => 'enabled = 1',
    'order_by' => 'code, id',
]);

// テーマファイルを読み込み
if (!empty($themes)) {
    $GLOBALS['_target'] = $GLOBALS['config']['theme_path'] . $themes[0]['code'];

    import('app/config.php');

    $GLOBALS['theme'][$themes[0]['code']]['setting'] = $themes[0]['setting'] ? json_decode($themes[0]['setting'], true) : [];

    import('app/bootstrap.php');
}

// プラグインを取得
$plugins = model('select_plugins', [
    'where'    => 'enabled = 1',
    'order_by' => 'code, id',
]);

// プラグインファイルを読み込み
foreach ($plugins as $plugin) {
    $target_dir = MAIN_PATH . $GLOBALS['config']['plugin_path'] . $plugin['code'] . '/';

    if (!file_exists($target_dir . 'config.php')) {
        continue;
    }

    import($target_dir . 'config.php');

    $GLOBALS['plugin'][$plugin['code']]['setting'] = $plugin['setting'] ? json_decode($plugin['setting'], true) : [];

    if (is_file($target_dir . 'app/bootstrap.php')) {
        import($target_dir . 'app/bootstrap.php');
    }
}
foreach ($plugins as $plugin) {
    if (empty($GLOBALS['plugin'][$plugin['code']])) {
        continue;
    }

    $target_dir = MAIN_PATH . $GLOBALS['config']['plugin_path'] . $plugin['code'] . '/';

    if (is_file($target_dir . 'before.php')) {
        import($target_dir . 'before.php');
    }

    $controller_dir = $target_dir . 'app/controllers/';
    $view_dir       = $target_dir . 'app/views/';
    $file           = $_REQUEST['_work'] . '.php';
    $flag           = false;

    if (isset($_params[0]) && is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/before_' . $_params[0] . '.php')) {
        import('app/controllers/before_' . $_params[0] . '.php');
    }

    if (is_file($controller_dir . 'before.php')) {
        import($controller_dir . 'before.php');
    }
    if (isset($_params[0]) && is_file($controller_dir . 'before_' . $_params[0] . '.php')) {
        import($controller_dir . 'before_' . $_params[0] . '.php');
    }

    if (is_file($controller_dir . $_REQUEST['_mode'] . '/' . $file)) {
        import($controller_dir . $_REQUEST['_mode'] . '/' . $file);

        $flag = true;
    } elseif (is_file($controller_dir . $_REQUEST['_mode'] . '/' . MAIN_DEFAULT_WORK . '.php')) {
        import($controller_dir . $_REQUEST['_mode'] . '/' . MAIN_DEFAULT_WORK . '.php');

        $flag = true;
    }

    if ($flag === true) {
        if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/after.php')) {
            import('app/controllers/after.php');
        }
    }

    if (is_file($view_dir . $_REQUEST['_mode'] . '/' . $file)) {
        import($view_dir . $_REQUEST['_mode'] . '/' . $file);
    } elseif (is_file($view_dir . $_REQUEST['_mode'] . '/' . MAIN_DEFAULT_WORK . '.php')) {
        import($view_dir . $_REQUEST['_mode'] . '/' . MAIN_DEFAULT_WORK . '.php');
    }

    if ($flag === true) {
        exit;
    }
}
