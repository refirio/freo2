<?php

// 設定項目を定義
$GLOBALS['setting_title'] = [
    'basis'      => '基本設定',
    'entry'      => 'エントリー設定',
    'page'       => 'ページ設定',
    'restricted' => '制限設定',
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
    ],
    'entry' => [
        'entry_use_approve' => [
            'name'     => 'エントリーの承認',
            'type'     => 'boolean',
            'required' => false,
        ],
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
        'page_use_approve' => [
            'name'     => 'ページの承認',
            'type'     => 'boolean',
            'required' => false,
        ],
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
    'menu' => [
        'menu_auth' => [
            'name'     => 'ユーザメニューに「ログイン」を表示',
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
        'mail_verify_subject' => [
            'name'     => 'メールアドレス存在確認',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_password_subject' => [
            'name'     => 'パスワード再設定',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_contact_subject' => [
            'name'     => 'お問い合わせありがとうございます',
            'type'     => 'text',
            'required' => true,
        ],
        'mail_contact_subject_admin' => [
            'name'     => 'お問い合わせがありました',
            'type'     => 'text',
            'required' => true,
        ],
    ],
];
