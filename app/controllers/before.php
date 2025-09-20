<?php

import('app/config.php');

// 設定項目を定義
$GLOBALS['setting_titles'] = [
    'basis' => '基本設定',
    'entry' => '記事設定',
    'page'  => 'ページ設定',
    'mail'  => 'メール設定',
];
$GLOBALS['setting_contents'] = [
    'basis' => [
        'title' => [
            'name'     => 'タイトル',
            'type'     => 'text',
            'required' => true,
        ],
        'description' => [
            'name'     => '概要',
            'type'     => 'text',
            'required' => false,
        ],
    ],
    'entry' => [
        'entry_use_text' => [
            'name'     => '本文の入力',
            'type'     => 'boolean',
            'required' => false,
        ],
        'entry_use_picture' => [
            'name'     => '写真の入力',
            'type'     => 'boolean',
            'required' => false,
        ],
        'entry_use_thumbnail' => [
            'name'     => 'サムネイルの入力',
            'type'     => 'boolean',
            'required' => false,
        ],
        'entry_default_code' => [
            'name'     => 'コードの初期値',
            'type'     => 'text',
            'required' => false,
        ],
    ],
    'page'  => [
        'page_use_text' => [
            'name'     => '本文の入力',
            'type'     => 'boolean',
            'required' => false,
        ],
        'page_use_picture' => [
            'name'     => '写真の入力',
            'type'     => 'boolean',
            'required' => false,
        ],
        'page_use_thumbnail' => [
            'name'     => 'サムネイルの入力',
            'type'     => 'boolean',
            'required' => false,
        ],
        'page_default_code' => [
            'name'     => 'コードの初期値',
            'type'     => 'text',
            'required' => false,
        ],
        'page_home_code' => [
            'name'     => 'ホームページに表示するページのコード',
            'type'     => 'text',
            'required' => false,
        ],
        'page_url_omission' => [
            'name'     => 'ページURLの省略',
            'type'     => 'boolean',
            'required' => false,
        ],
    ],
    'mail'  => [
        'mail_to' => [
            'name'     => 'メールの送信先',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_subject_admin' => [
            'name'     => 'メールの件名（管理者用）',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_subject_user' => [
            'name'     => 'メールの件名（自動返信）',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_from' => [
            'name'     => 'メールの送信元',
            'type'     => 'text',
            'required' => true,
        ],
    ],
];

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
if (!preg_match('/^(admin)$/', $_REQUEST['_mode']) || !preg_match('/^(index|logout)$/', $_REQUEST['_work'])) {
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
