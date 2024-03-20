<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if ((empty($_POST['view']) || $_POST['view'] !== 'preview') && !token('check')) {
        error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
    }

    // アクセス元
    if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
        error('不正なアクセスです。');
    }

    // 入力データを整理
    $post = [
        'contact' => model('normalize_contacts', [
            'id'      => isset($_POST['id'])      ? $_POST['id']      : '',
            'name'    => isset($_POST['name'])    ? $_POST['name']    : '',
            'email'   => isset($_POST['email'])   ? $_POST['email']   : '',
            'message' => isset($_POST['message']) ? $_POST['message'] : '',
            'memo'    => isset($_POST['memo'])    ? $_POST['memo']    : '',
        ]),
    ];

    if (isset($_POST['view']) && $_POST['view'] === 'preview') {
        // プレビュー
        $_view['contact'] = $post['contact'];
    } else {
        // 入力データを検証＆登録
        $warnings = model('validate_contacts', $post['contact']);
        if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
            if (empty($warnings)) {
                ok();
            } else {
                warning($warnings);
            }
        } else {
            if (empty($warnings)) {
                $_SESSION['post']['contact'] = $post['contact'];

                // フォワード
                forward('/admin/contact_post');
            } else {
                $_view['contact'] = $post['contact'];

                $_view['warnings'] = $warnings;
            }
        }
    }
} else {
    // 初期データを取得
    if (empty($_GET['id'])) {
        $_view['contact'] = model('default_contacts');
    } else {
        $contacts = model('select_contacts', [
            'where' => [
                'id = :id',
                [
                    'id' => $_GET['id'],
                ],
            ],
        ]);
        if (empty($contacts)) {
            warning('編集データが見つかりません。');
        } else {
            $_view['contact'] = $contacts[0];
        }
    }

    if (isset($_GET['_type']) && $_GET['_type'] === 'json') {
        // お問い合わせ情報を取得
        header('Content-Type: application/json; charset=' . MAIN_CHARSET);

        echo json_encode([
            'status' => 'OK',
            'data'   => $_view,
        ]);

        exit;
    } else {
        // 投稿セッションを初期化
        unset($_SESSION['post']);
    }

    // 編集開始日時を記録
    if (!empty($_GET['id'])) {
        $_SESSION['update']['contact'] = localdate('Y-m-d H:i:s');
    }
}

if ((empty($_POST['view']) || $_POST['view'] !== 'preview')) {
    // お問い合わせの表示用データ作成
    $_view['contact'] = model('view_contacts', $_view['contact']);
}

// タイトル
if (empty($_GET['id'])) {
    $_view['title'] = 'お問い合わせ登録';
} else {
    $_view['title'] = 'お問い合わせ編集';
}
