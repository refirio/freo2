<?php

if (isset($_GET['target'])) {
    $setting_contents = $GLOBALS['setting_contents'][$_GET['target']];
} else {
    $setting_contents = [];
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
    foreach ($setting_contents as $key => $data) {
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
} elseif (!empty($setting_contents)) {
    // 初期データを取得
    $where = [];
    foreach ($setting_contents as $key => $data) {
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
} else {
    $_view['setting_sets'] = [];
}

// 設定項目
$_view['contents'] = $setting_contents;

// タイトル
$_view['title'] = isset($_GET['target']) ? $GLOBALS['setting_title'][$_GET['target']] : '設定';
