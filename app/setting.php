<?php

// 設定項目を定義
$GLOBALS['setting_title'] = [
    'basis'      => '基本設定',
    'entry'      => 'エントリー設定',
    'page'       => 'ページ設定',
    'comment'    => 'コメント設定',
    'user'       => 'ユーザー設定',
    'restricted' => '制限設定',
    'number'     => '表示件数設定',
    'menu'       => 'メニュー設定',
    'mail'       => 'メール設定',
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
        'admin_title' => [
            'name'     => '管理ページ タイトル',
            'type'     => 'text',
            'required' => true,
        ],
        'admin_description' => [
            'name'     => '管理ページ 概要',
            'type'     => 'text',
            'required' => false,
        ],
    ],
    'entry' => [
        'entry_use_approve' => [
            'name'     => 'エントリーの承認',
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
        'entry_text_type' => [
            'name'     => '本文の入力項目',
            'type'     => 'select',
            'required' => false,
            'kind'     => [
                'none'     => 'なし',
                'textarea' => '複数行入力',
                'html'     => 'HTML直接入力',
                'wysiwyg'  => 'WYSIWYGエディタ',
            ],
        ],
    ],
    'page'  => [
        'page_use_approve' => [
            'name'     => 'ページの承認',
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
        'page_text_type' => [
            'name'     => '本文の入力項目',
            'type'     => 'select',
            'required' => false,
            'kind'     => [
                'none'     => 'なし',
                'textarea' => '複数行入力',
                'html'     => 'HTML直接入力',
                'wysiwyg'  => 'WYSIWYGエディタ',
            ],
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
    'comment' => [
        'comment_use_approve' => [
            'name'     => 'コメントの承認',
            'type'     => 'boolean',
            'required' => false,
        ],
    ],
    'user' => [
        'user_use_register' => [
            'name'     => '訪問者によるユーザー新規登録',
            'type'     => 'boolean',
            'required' => false,
        ],
        'user_use_approve' => [
            'name'     => 'ユーザーの承認',
            'type'     => 'boolean',
            'required' => false,
        ],
    ],
    'restricted'  => [
        'restricted_password_title' => [
            'name'     => 'パスワード認証で制限されたタイトルの先頭に付与する文字列',
            'type'     => 'text',
            'required' => false,
        ],
        'restricted_password_text' => [
            'name'     => 'パスワード認証で制限された本文に表示する文言',
            'type'     => 'text',
            'required' => true,
        ],
    ],
    'number' => [
        'number_limit_entry' => [
            'name'     => 'エントリーの表示件数',
            'type'     => 'number',
            'required' => true,
        ],
        'number_limit_contact' => [
            'name'     => 'お問い合わせの表示件数',
            'type'     => 'number',
            'required' => true,
        ],
        'number_limit_comment' => [
            'name'     => 'コメントの表示件数',
            'type'     => 'number',
            'required' => true,
        ],
        'number_limit_admin_entry' => [
            'name'     => 'エントリーの表示件数（管理ページ）',
            'type'     => 'number',
            'required' => true,
        ],
        'number_limit_admin_contact' => [
            'name'     => 'お問い合わせの表示件数（管理ページ）',
            'type'     => 'number',
            'required' => true,
        ],
        'number_limit_admin_comment' => [
            'name'     => 'コメントの表示件数（管理ページ）',
            'type'     => 'number',
            'required' => true,
        ],
        'number_limit_admin_user' => [
            'name'     => 'ユーザーの表示件数（管理ページ）',
            'type'     => 'number',
            'required' => true,
        ],
        'number_limit_admin_log' => [
            'name'     => 'ログの表示件数（管理ページ）',
            'type'     => 'number',
            'required' => true,
        ],
        'number_width_entry' => [
            'name'     => 'エントリーのページャー幅',
            'type'     => 'number',
            'required' => true,
        ],
        'number_width_contact' => [
            'name'     => 'お問い合わせのページャー幅',
            'type'     => 'number',
            'required' => true,
        ],
        'number_width_comment' => [
            'name'     => 'コメントのページャー幅',
            'type'     => 'number',
            'required' => true,
        ],
        'number_width_admin_entry' => [
            'name'     => 'エントリーのページャー幅（管理ページ）',
            'type'     => 'number',
            'required' => true,
        ],
        'number_width_admin_contact' => [
            'name'     => 'お問い合わせのページャー幅（管理ページ）',
            'type'     => 'number',
            'required' => true,
        ],
        'number_width_admin_comment' => [
            'name'     => 'コメントのページャー幅（管理ページ）',
            'type'     => 'number',
            'required' => true,
        ],
        'number_width_admin_user' => [
            'name'     => 'ユーザーのページャー幅（管理ページ）',
            'type'     => 'number',
            'required' => true,
        ],
        'number_width_admin_log' => [
            'name'     => 'ログのページャー幅（管理ページ）',
            'type'     => 'number',
            'required' => true,
        ],
    ],
    'menu' => [
        'menu_auth' => [
            'name'     => 'ユーザーメニューに「ログイン」を表示',
            'type'     => 'boolean',
            'required' => false,
        ],
        'menu_admin_field' => [
            'name'     => '管理メニューに「フィールド」を表示',
            'type'     => 'boolean',
            'required' => false,
        ],
        'menu_admin_menu' => [
            'name'     => '管理メニューに「メニュー」を表示',
            'type'     => 'boolean',
            'required' => false,
        ],
        'menu_admin_widget' => [
            'name'     => '管理メニューに「ウィジェット」を表示',
            'type'     => 'boolean',
            'required' => false,
        ],
    ],
    'mail'  => [
        'mail_from' => [
            'name'     => 'メールの送信元',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_to' => [
            'name'     => 'メールの送信先',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_body_begin' => [
            'name'     => 'メール本文の冒頭',
            'type'     => 'textarea',
            'required' => false,
        ],
        'mail_body_end' => [
            'name'     => 'メール本文の末尾',
            'type'     => 'textarea',
            'required' => false,
        ],
        'mail_register_subject' => [
            'name'     => 'ユーザー登録完了メールの件名',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_verify_subject' => [
            'name'     => 'メールアドレス存在確認メールの件名',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_password_subject' => [
            'name'     => 'パスワード再設定メールの件名',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_leave_subject' => [
            'name'     => 'ユーザー削除完了メールの件名',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_contact_subject' => [
            'name'     => 'お問い合わせメールの件名',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_contact_subject_admin' => [
            'name'     => 'お問い合わせメールの件名（管理者用）',
            'type'     => 'text',
            'required' => true,
        ],
    ],
];
