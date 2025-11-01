<?php

import('app/version.php');
import('app/config.php');
import('app/setting.php');

// 設定内容を取得
$settings = model('select_settings', []);
$GLOBALS['setting'] = [];
foreach ($settings as $setting) {
    $GLOBALS['setting'][$setting['id']] = $setting['value'];
}

// 読み込み
import('app/services/user.php');
import('app/services/storage.php');
import('app/services/entry.php');
import('libs/modules/loader.php');

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

// ユーザを確認
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
                if (!preg_match('/^(index|entry|page|category|field|attribute|menu|widget|contact|file)(_|$)/', $_REQUEST['_work'])) {
                    error('不正なアクセスです。');
                }
            }
        } elseif ($GLOBALS['authority']['power'] == 1) {
            // 閲覧者
            if (preg_match('/^(admin)$/', $_REQUEST['_mode'])) {
                if (!preg_match('/^(index|contact|contact_view)(_|$)/', $_REQUEST['_work'])) {
                    error('不正なアクセスです。');
                }
            }
        } else {
            // ゲスト
            if (preg_match('/^(admin)$/', $_REQUEST['_mode'])) {
                error('不正なアクセスです。');
            } elseif (preg_match('/^(auth)$/', $_REQUEST['_mode'])) {
                if (!preg_match('/^(index|home|email|modify|logout|leave)(_|$)/', $_REQUEST['_work'])) {
                    error('不正なアクセスです。');
                }
            }
        }

        // 属性を取得
        $attribute_sets = model('select_attribute_sets', [
            'where' => [
                'user_id = :user_id',
                [
                    'user_id' => $_SESSION['auth']['user']['id'],
                ],
            ],
        ]);
        $GLOBALS['attributes'] = array_column($attribute_sets, 'attribute_id');

        // ユーザ情報を取得
        $_view['_user'] = $users[0];
    }
}

if (!preg_match('/^(admin)$/', $_REQUEST['_mode'])) {
    // ページURLの省略
    if ($GLOBALS['setting']['page_url_omission']) {
        if ($_params[0] !== 'page') {
            $pages = service_entry_select_published('page', [
                'select' => 'entries.id',
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
        if (preg_match('/^\//', $menu['url'])) {
            $menu['url'] = MAIN_FILE . $menu['url'];
        }
        $_view['menus'][] = $menu;
    }

    // ウィジェットを取得
    $widgets = model('select_widgets', []);
    $_view['widgets'] = [];
    foreach ($widgets as $widget) {
        $_view['widgets'][$widget['code']] = $widget['text'];
    }
}

// メニュー
$GLOBALS['menu_group'] = [
    'public' => [
        'home' => [
            'name' => 'ホーム',
            'show' => true,
        ],
    ],
    'admin' => [
        'home' => [
            'name' => 'ホーム',
            'show' => true,
        ],
        'contents' => [
            'name' => 'コンテンツ',
            'show' => isset($GLOBALS['authority']) && $GLOBALS['authority']['power'] >= 2,
        ],
        'communication' => [
            'name' => 'コミュニケーション',
            'show' => true,
        ],
        'system' => [
            'name' => 'システム',
            'show' => isset($GLOBALS['authority']) && $GLOBALS['authority']['power'] >= 3,
        ],
    ],
];
$GLOBALS['menu_contents'] = [
    'public' => [
        'home' => [
            'entry' => [
                'name'   => 'エントリー',
                'link'   => '/entry/',
                'active' => '/^entry$/',
                'icon'   => null,
                'show'   => true,
            ],
            'contact' => [
                'name'   => 'お問い合わせ',
                'link'   => '/contact/',
                'active' => '/^contact$/',
                'icon'   => null,
                'show'   => true,
            ],
            'auth' => [
                'name'   => 'ログイン',
                'link'   => '/auth/',
                'active' => '/^auth$/',
                'icon'   => null,
                'show'   => $GLOBALS['setting']['menu_auth'],
            ],
        ],
    ],
    'admin' => [
        'home' => [
            'index' => [
                'name'   => 'ホーム',
                'link'   => '/admin/',
                'active' => '/^index$/',
                'icon'   => '#symbol-file-text',
                'show'   => true,
            ],
        ],
        'contents' => [
            'entry' => [
                'name'   => 'エントリー管理',
                'link'   => '/admin/entry',
                'active' => '/^entry(_|$)/',
                'icon'   => '#symbol-file-text',
                'show'   => true,
            ],
            'page' => [
                'name'   => 'ページ管理',
                'link'   => '/admin/page',
                'active' => '/^page(_|$)/',
                'icon'   => '#symbol-file-text',
                'show'   => true,
            ],
            'category' => [
                'name'   => 'カテゴリ管理',
                'link'   => '/admin/category',
                'active' => '/^category(_|$)/',
                'icon'   => '#symbol-file-text',
                'show'   => true,
            ],
            'field' => [
                'name'   => 'フィールド管理',
                'link'   => '/admin/field',
                'active' => '/^field(_|$)/',
                'icon'   => '#symbol-file-text',
                'show'   => $GLOBALS['setting']['menu_admin_field'],
            ],
            'menu' => [
                'name'   => 'メニュー管理',
                'link'   => '/admin/menu',
                'active' => '/^menu(_|$)/',
                'icon'   => '#symbol-file-text',
                'show'   => $GLOBALS['setting']['menu_admin_menu'],
            ],
            'widget' => [
                'name'   => 'ウィジェット管理',
                'link'   => '/admin/widget',
                'active' => '/^widget(_|$)/',
                'icon'   => '#symbol-file-text',
                'show'   => $GLOBALS['setting']['menu_admin_widget'],
            ],
        ],
        'communication' => [
            'contact' => [
                'name'   => 'お問い合わせ管理',
                'link'   => '/admin/contact',
                'active' => '/^contact(_|$)/',
                'icon'   => '#symbol-file-text',
                'show'   => true,
            ],
        ],
        'system' => [
            'user' => [
                'name'   => 'ユーザ管理',
                'link'   => '/admin/user',
                'active' => '/^user(_|$)/',
                'icon'   => '#symbol-file-text',
                'show'   => true,
            ],
            'attribute' => [
                'name'   => '属性管理',
                'link'   => '/admin/attribute',
                'active' => '/^attribute(_|$)/',
                'icon'   => '#symbol-file-text',
                'show'   => true,
            ],
            'plugin' => [
                'name'   => 'プラグイン管理',
                'link'   => '/admin/plugin',
                'active' => '/^plugin(_|$)/',
                'icon'   => '#symbol-file-text',
                'show'   => true,
            ],
            'log' => [
                'name'   => 'ログ',
                'link'   => '/admin/log',
                'active' => '/^log$/',
                'icon'   => '#symbol-file-text',
                'show'   => true,
            ],
            'setting' => [
                'name'   => '設定',
                'link'   => '/admin/setting',
                'active' => '/^setting(_|$)/',
                'icon'   => '#symbol-file-text',
                'show'   => true,
            ],
            'version' => [
                'name'   => 'バージョン情報',
                'link'   => '/admin/version',
                'active' => '/^version$/',
                'icon'   => '#symbol-file-text',
                'show'   => true,
            ],
        ],
    ],
];

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

    if (is_file($view_dir . $_REQUEST['_mode'] . '/' . $file)) {
        import($view_dir . $_REQUEST['_mode'] . '/' . $file);

        $flag = true;
    } elseif (is_file($view_dir . $_REQUEST['_mode'] . '/' . MAIN_DEFAULT_WORK . '.php')) {
        import($view_dir . $_REQUEST['_mode'] . '/' . MAIN_DEFAULT_WORK . '.php');

        $flag = true;
    }

    if ($flag) {
        if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/after.php')) {
            import('app/controllers/after.php');
        }

        exit;
    }
}
