<?php

// 設定項目を定義
$GLOBALS['setting_title'] = [
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
        'entry_use_approve' => [
            'name'     => '記事の承認',
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
