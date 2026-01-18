<?php

// メニューを定義
$GLOBALS['menu_group'] = [
    'public' => [
        'home' => [
            'name' => $GLOBALS['string']['menu_public_home'],
            'show' => true,
        ],
    ],
    'auth' => [
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
                'name'   => $GLOBALS['string']['menu_public_entry'],
                'link'   => '/entry/',
                'active' => '/^entry$/',
                'icon'   => null,
                'show'   => $GLOBALS['setting']['menu_entry'],
            ],
            'contact' => [
                'name'   => $GLOBALS['string']['menu_public_contact'],
                'link'   => '/contact/',
                'active' => '/^contact$/',
                'icon'   => null,
                'show'   => $GLOBALS['setting']['menu_contact'],
            ],
            'auth' => [
                'name'   => $GLOBALS['string']['menu_public_auth'],
                'link'   => '/auth/',
                'active' => '/^auth$/',
                'icon'   => null,
                'show'   => $GLOBALS['setting']['menu_auth'],
            ],
        ],
    ],
    'auth' => [
        'home' => [
            'contact' => [
                'name'   => $GLOBALS['string']['menu_auth_contact'],
                'link'   => '/auth/contact',
                'active' => '/^contact$/',
                'icon'   => null,
                'show'   => $GLOBALS['setting']['menu_auth_contact'],
            ],
            'comment' => [
                'name'   => $GLOBALS['string']['menu_auth_comment'],
                'link'   => '/auth/comment',
                'active' => '/^comment$/',
                'icon'   => null,
                'show'   => $GLOBALS['setting']['menu_auth_comment'],
            ],
            'modify' => [
                'name'   => $GLOBALS['string']['menu_auth_modify'],
                'link'   => '/auth/modify',
                'active' => '/^modify$/',
                'icon'   => null,
                'show'   => true,
            ],
            'leave' => [
                'name'   => $GLOBALS['string']['menu_auth_leave'],
                'link'   => '/auth/leave',
                'active' => '/^leave$/',
                'icon'   => null,
                'show'   => isset($GLOBALS['authority']) && $GLOBALS['authority']['power'] == 0 && !empty($GLOBALS['setting']['user_use_register']),
            ],
            'logout' => [
                'name'   => $GLOBALS['string']['menu_auth_logout'],
                'link'   => '/auth/logout',
                'active' => '/^logout$/',
                'icon'   => null,
                'show'   => true,
            ],
        ],
    ],
    'admin' => [
        'home' => [
            'index' => [
                'name'   => 'ホーム',
                'link'   => '/admin/',
                'active' => '/^index$/',
                'icon'   => '#symbol-clipboard-data',
                'show'   => true,
            ],
        ],
        'contents' => [
            'entry' => [
                'name'   => 'エントリー管理',
                'link'   => '/admin/entry',
                'active' => '/^entry(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
            'category' => [
                'name'   => 'カテゴリー管理',
                'link'   => '/admin/category',
                'active' => '/^category(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
            'field' => [
                'name'   => 'フィールド管理',
                'link'   => '/admin/field',
                'active' => '/^field(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => isset($GLOBALS['authority']) && $GLOBALS['authority']['power'] >= 3 && $GLOBALS['setting']['menu_admin_field'],
            ],
            'page' => [
                'name'   => 'ページ管理',
                'link'   => '/admin/page',
                'active' => '/^page(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
            'menu' => [
                'name'   => 'メニュー管理',
                'link'   => '/admin/menu',
                'active' => '/^menu(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => isset($GLOBALS['authority']) && $GLOBALS['authority']['power'] >= 3 && $GLOBALS['setting']['menu_admin_menu'],
            ],
            'widget' => [
                'name'   => 'ウィジェット管理',
                'link'   => '/admin/widget',
                'active' => '/^widget(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => isset($GLOBALS['authority']) && $GLOBALS['authority']['power'] >= 3 && $GLOBALS['setting']['menu_admin_widget'],
            ],
            'media' => [
                'name'   => 'メディア管理',
                'link'   => '/admin/media',
                'active' => '/^media(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
        ],
        'communication' => [
            'contact' => [
                'name'   => 'お問い合わせ管理',
                'link'   => '/admin/contact',
                'active' => '/^contact(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
            'comment' => [
                'name'   => 'コメント管理',
                'link'   => '/admin/comment',
                'active' => '/^comment(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
        ],
        'system' => [
            'user' => [
                'name'   => 'ユーザー管理',
                'link'   => '/admin/user',
                'active' => '/^user(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
            'attribute' => [
                'name'   => '属性管理',
                'link'   => '/admin/attribute',
                'active' => '/^attribute(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
            'theme' => [
                'name'   => 'テーマ管理',
                'link'   => '/admin/theme',
                'active' => '/^theme(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
            'plugin' => [
                'name'   => 'プラグイン管理',
                'link'   => '/admin/plugin',
                'active' => '/^plugin(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
            'log' => [
                'name'   => 'ログ',
                'link'   => '/admin/log',
                'active' => '/^log(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
            'setting' => [
                'name'   => '設定',
                'link'   => '/admin/setting',
                'active' => '/^setting(_|$)/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
            'version' => [
                'name'   => 'バージョン情報',
                'link'   => '/admin/version',
                'active' => '/^version$/',
                'icon'   => '#symbol-list-ul',
                'show'   => true,
            ],
        ],
    ],
];
