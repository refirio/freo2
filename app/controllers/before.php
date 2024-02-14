<?php

import('app/config.php');

if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/config.local.php')) {
    import('app/config.local.php');
}

// 設定
$settings = model('select_settings', [
]);
$GLOBALS['setting'] = [];
foreach ($settings as $setting) {
    $GLOBALS['setting'][$setting['id']] = $setting['value'];
}

// 読み込み
import('app/services/user.php');
import('app/services/storage.php');
import('app/services/page.php');
import('libs/plugins/loader.php');

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
if (!preg_match('/^(index|logout)$/', $_REQUEST['_work'])) {
    if (empty($_SESSION['auth']['session']) && !empty($_COOKIE['auth']['session'])) {
        list($session, $user_id) = service_user_login($_COOKIE['auth']['session']);
        if ($session === true) {
            $_SESSION['auth']['session'] = $session;
            $_SESSION['auth']['user']    = [
                'id'   => $user_id,
                'time' => localdate(),
            ];
        }
    }
}

// ユーザ存在確認
if (!empty($_SESSION['auth']['user']['id'])) {
    $users = model('select_users', [
        'where' => [
            'id = :id',
            [
                'id' => $_SESSION['auth']['user']['id'],
            ],
        ],
    ]);
    if (empty($users)) {
        unset($_SESSION['auth']['user']);

        // リダイレクト
        redirect('/admin/');
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
        if ($GLOBALS['authority']['power'] <= 2) {
            if (preg_match('/^(admin)$/', $_REQUEST['_mode']) && !preg_match('/^modify(_|$)/', $_REQUEST['_work'])) {
                if (preg_match('/^user(_|$)/', $_REQUEST['_work']) || preg_match('/^log$/', $_REQUEST['_work'])) {
                    error('不正なアクセスです。');
                }
            }
        }
        if ($GLOBALS['authority']['power'] <= 1) {
            if (preg_match('/^(admin)$/', $_REQUEST['_mode']) && !preg_match('/^modify(_|$)/', $_REQUEST['_work'])) {
                if (preg_match('/^setting(_|$)/', $_REQUEST['_work']) || preg_match('/_/', $_REQUEST['_work'])) {
                    error('不正なアクセスです。');
                }
            }
        }

        // ユーザ情報を取得
        $_view['_user'] = $users[0];
    }
}

if (!preg_match('/^(admin)$/', $_REQUEST['_mode'])) {
    // ページURLの省略
    if ($GLOBALS['setting']['page_url_omission']) {
        if ($_params[0] !== 'page') {
            $pages = service_page_select_published([
                'select' => 'id',
                'where'  => [
                    'pages.code = :code',
                    [
                        'code' => implode('/', $_params),
                    ],
                ],
            ], [
                'associate' => true,
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
