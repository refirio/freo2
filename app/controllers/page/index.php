<?php

import('app/services/entry.php');

// 表示対象を取得
if (isset($_params[1])) {
    $_GET['code'] = implode('/', array_slice($_params, 1));
}
if (!isset($_GET['code'])) {
    error('不正なアクセスです。');
}

// ページを取得
$pages = service_entry_select_published('page', [
    'where' => [
        'entries.code = :code',
        [
            'code' => $_GET['code'],
        ],
    ],
]);
if (empty($pages)) {
    warning('ページが見つかりません。');
} else {
    $_view['page'] = $pages[0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正なアクセスです。');
    }

    if (isset($_POST['exec']) && $_POST['exec'] === 'password') {
        // パスワード認証
        if ($_POST['password'] === $pages[0]['password']) {
            $_SESSION['entry_passwords'][$pages[0]['id']] = true;

            // リダイレクト
            redirect('/page/' . $pages[0]['code']);
        } else {
            warning('パスワードが違います。');
        }
    } elseif (isset($_POST['exec']) && $_POST['exec'] === 'comment') {
        // コメントの受付を確認
        if ($pages[0]['comment'] !== 'opened' && ($pages[0]['comment'] !== 'user' || empty($_SESSION['auth']['user']['id']))) {
            error('不正なアクセスです。');
        }

        // reCAPTCHA
        if ($GLOBALS['config']['recaptcha_enable'] == true && empty($_SESSION['recaptcha'])) {
            $recaptcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : null;
            if (!$recaptcha){
                error('reCAPTCHAでの認証を行ってください。');
            }
            $response = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $GLOBALS['config']['recaptcha_secret_key'] . '&response=' . $recaptcha), true);
            if (intval($response['success']) === 1 && $response['score'] >= 0.5) {
                $_SESSION['recaptcha'] = true;
            } else {
                error('reCAPTCHAでの認証に失敗しました。');
            }
        }

        // 入力データを整理
        $post = array(
            'comment' => model('normalize_comments', [
                'entry_id' => isset($_POST['entry_id']) ? $_POST['entry_id'] : '',
                'name'     => isset($_POST['name'])     ? $_POST['name']     : '',
                'url'      => isset($_POST['url'])      ? $_POST['url']      : '',
                'message'  => isset($_POST['message'])  ? $_POST['message']  : '',
            ]),
        );

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

                // リダイレクト
                redirect('/comment/preview');
            } else {
                $_view['comment'] = $post['comment'];

                $_view['warnings'] = $warnings;
            }
        }
    }
} elseif (isset($_GET['referer']) && $_GET['referer'] === 'preview') {
    // 入力データを復元
    $_view['comment'] = $_SESSION['post']['comment'];
} else {
    // 初期データを取得
    $_view['comment'] = model('default_comments');

    if (!empty($_SESSION['auth']['user']['id'])) {
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
    }
}

// コメントを取得
$_view['comments'] = model('select_comments', [
    'where' => [
        'entries.code = :code',
        [
            'code' => $_GET['code'],
        ],
    ],
    'order_by' => 'comments.id',
], [
    'associate' => true,
]);

// タイトル
if (isset($_view['page']['title'])) {
    $_view['title'] = $_view['page']['title'];
} else {
    $_view['title'] = 'ページ';
}
