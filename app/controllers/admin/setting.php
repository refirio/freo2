<?php

// 設定項目
$targets = [];
if (isset($_GET['target']) && $_GET['target'] == 'basis') {
    $targets = [
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
    ];
} elseif (isset($_GET['target']) && $_GET['target'] == 'page') {
    $targets = [
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
    ];
} elseif (isset($_GET['target']) && $_GET['target'] == 'mail') {
    $targets = [
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
    ];
} else {
    error('不正なアクセスです。');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
    }

    // アクセス元
    if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
        error('不正なアクセスです。');
    }

    // 入力データを整理
    $settings = [];
    $warnings = [];
    foreach ($targets as $key => $data) {
        $settings[$key] = isset($_POST[$key]) ? $_POST[$key] : '';
        if ($data['required'] && $settings[$key] === '') {
            $warnings[$key] = $data['name'] . 'が入力されていません。';
        }
    }
    $post = [
        'setting_sets' => $settings,
    ];

    // 入力データを検証＆登録
    if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
        if (empty($warnings)) {
            ok();
        } else {
            warning($warnings);
        }
    } else {
        if (empty($warnings)) {
            $_SESSION['post']['setting'] = $post['setting_sets'];

            // フォワード
            forward('/admin/setting_post');
        } else {
            $_view['setting_sets'] = $post['setting_sets'];

            $_view['warnings'] = $warnings;
        }
    }
} else {
    // 初期データを取得
    $where = [];
    foreach ($targets as $key => $data) {
        $where[] = '\'' . $key . '\'';
    }
    $settings = model('select_settings', [
        'where' => 'id IN(' . implode(',', $where) . ')',
    ]);
    if (empty($settings)) {
        warning('編集データが見つかりません。');
    }

    $_view['setting_sets'] = [];
    foreach ($settings as $setting) {
        $_view['setting_sets'][$setting['id']] = $setting['value'];
    }

    // 投稿セッションを初期化
    unset($_SESSION['post']);
}

// 設定項目
$_view['targets'] = $targets;

// タイトル
if (isset($_GET['target']) && $_GET['target'] == 'basis') {
    $_view['title'] = '基本設定';
} elseif (isset($_GET['target']) && $_GET['target'] == 'page') {
    $_view['title'] = 'ページ設定';
} elseif (isset($_GET['target']) && $_GET['target'] == 'mail') {
    $_view['title'] = 'メール設定';
}
