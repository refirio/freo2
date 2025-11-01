<?php

// メニュー項目を定義
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
