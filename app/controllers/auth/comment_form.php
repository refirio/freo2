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
        'comment' => model('normalize_comments', [
            'id'         => isset($_POST['id'])         ? $_POST['id']         : '',
            'contact_id' => isset($_POST['contact_id']) ? $_POST['contact_id'] : '',
            'name'       => isset($_POST['name'])       ? $_POST['name']       : '',
            'url'        => isset($_POST['url'])        ? $_POST['url']        : '',
            'message'    => isset($_POST['message'])    ? $_POST['message']    : '',
            'memo'       => isset($_POST['memo'])       ? $_POST['memo']       : '',
        ]),
    ];

    if (isset($_POST['view']) && $_POST['view'] === 'preview') {
        // プレビュー
        $_view['comment'] = $post['comment'];
    } else {
        // 入力データを検証＆登録
        $warnings = model('validate_comments', $post['comment']);
        if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
            if (empty($warnings)) {
                ok();
            } else {
                warning($warnings);
            }
        } else {
            if (empty($warnings)) {
                $_SESSION['post']['comment'] = $post['comment'];

                // フォワード
                forward('/auth/comment_post');
            } else {
                $_view['comment'] = $post['comment'];

                $_view['warnings'] = $warnings;
            }
        }
    }
} else {
    // 初期データを取得
    if (empty($_GET['id'])) {
        $contacts = model('select_contacts', [
            'where' => [
                'contacts.id = :contact_id AND contacts.user_id = :user_id',
                [
                    'contact_id' => $_GET['contact_id'],
                    'user_id'    => $_SESSION['auth']['user']['id'],
                ],
            ],
        ], [
            'associate' => true,
        ]);
        if (empty($contacts)) {
            warning('表示データが見つかりません。');
        }

        $_view['comment'] = model('default_comments');
        $_view['comment']['contact_id'] = $_GET['contact_id'];

        // ユーザーを取得
        $users = model('select_users', [
            'where' => [
                'id = :id AND enabled = 1',
                [
                    'id' => $_SESSION['auth']['user']['id'],
                ],
            ],
        ]);

        $_view['comment']['name'] = $users[0]['name'];
        $_view['comment']['url']  = $users[0]['url'];
    } else {
        $comments = model('select_comments', [
            'where' => [
                'id = :id AND user_id = :user_id',
                [
                    'id'      => $_GET['id'],
                    'user_id' => $_SESSION['auth']['user']['id'],
                ],
            ],
        ]);
        if (empty($comments)) {
            warning('編集データが見つかりません。');
        } else {
            $_view['comment'] = $comments[0];
        }
    }

    // 投稿セッションを初期化
    unset($_SESSION['post']);

    // 編集開始日時を記録
    if (!empty($_GET['id'])) {
        $_SESSION['update']['comment'] = localdate('Y-m-d H:i:s');
    }
}

// タイトル
if (empty($_GET['id'])) {
    $_view['title'] = 'コメント登録';
} else {
    $_view['title'] = 'コメント編集';
}
