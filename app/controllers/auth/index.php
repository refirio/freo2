<?php

import('app/services/user.php');

// ログイン処理の結果
$success      = false;
$id           = null;
$keep         = 0;
$trusted      = false;
$twostep_send = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !token('check')) {
    // ワンタイムトークン（画面を開いたまま期限が切れることがあるので、エラー画面にせずにログイン画面へ戻す）
    unset($_SESSION['auth']['twostep']);

    $_view['user'] = [
        'username' => isset($_POST['username']) && is_string($_POST['username']) ? $_POST['username'] : '',
        'password' => '',
    ];

    $_view['warnings'] = ['もう一度ログインしてください。'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exec']) && ($_POST['exec'] === 'twostep' || $_POST['exec'] === 'twostep_resend')) {
    // 2段階認証（パスワード認証が済み、コードの入力を待っている状態）
    if (empty($_SESSION['auth']['twostep']['id'])) {
        $users = [];
    } else {
        $users = model('select_users', [
            'select' => 'id, twostep_email',
            'where'  => [
                'id = :id AND enabled = 1 AND twostep = 1',
                [
                    'id' => $_SESSION['auth']['twostep']['id'],
                ],
            ],
        ]);
    }
    if (empty($users)) {
        // 認証待ちの状態が無い（セッション切れなど）
        unset($_SESSION['auth']['twostep']);

        $_view['user'] = [
            'username' => '',
            'password' => '',
        ];

        $_view['warnings'] = ['もう一度ログインしてください。'];
    } else {
        $id            = $users[0]['id'];
        $keep          = $_SESSION['auth']['twostep']['keep'];
        $twostep_email = $users[0]['twostep_email'];

        $_view['user']    = $_POST;
        $_view['twostep'] = true;

        if ($_POST['exec'] === 'twostep_resend') {
            // 2段階認証用コードを再送信
            $twostep_send = true;
        } else {
            // 2段階認証用コードを確認（間違えた回数の記録は、エラーを表示する場合も残す）
            db_transaction();

            $message = service_user_twostep_verify($id, isset($_POST['twostep_code']) ? $_POST['twostep_code'] : '');

            db_commit();

            if ($message === null) {
                $success = true;
            } else {
                $_view['warnings'] = [$message];
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 前回の認証待ちの状態を破棄
    unset($_SESSION['auth']['twostep']);

    // アカウントの凍結を確認
    if (service_user_frozen($_POST['username'])) {
        error('パスワードを連続して間違えたため、このアカウントは凍結されています。5分後以降にお試しください。');
    }

    // パスワード認証（失敗した回数の記録は、エラーを表示する場合も残す）
    db_transaction();

    $user = service_user_authenticate($_POST['username'], $_POST['password']);

    db_commit();

    if ($user === null) {
        // パスワード認証失敗
        $_view['user'] = $_POST;
        $_view['user']['password'] = '';

        $_view['warnings'] = ['ユーザー名もしくはパスワードが違います。'];
    } else {
        // パスワード認証成功
        $id            = $user['id'];
        $twostep_email = $user['twostep_email'];

        if (isset($_POST['session']) && $_POST['session'] === 'keep') {
            $keep = 1;
        } else {
            $keep = 0;
        }

        // コードの入力を省略するブラウザか
        if ($user['twostep'] == 1) {
            $trusted = service_user_twostep_trusted($id, isset($_COOKIE['auth']['session']) ? $_COOKIE['auth']['session'] : null);
        }

        if (!$user['enabled']) {
            // アカウント有効化前
            $_view['user'] = $_POST;
            $_view['user']['password'] = '';

            $_view['warnings'] = ['アカウントが有効化されていません。'];
        } elseif ($user['twostep'] == 1 && !$trusted) {
            // 2段階認証が必要
            $_view['user'] = $_POST;
            $_view['user']['password'] = '';

            if (empty($twostep_email)) {
                $_view['warnings'] = ['2段階認証用メールアドレスが登録されていません。'];
            } else {
                // パスワード認証が済んだことをセッションに記録し、コードの入力を待つ（コードの画面ではユーザー名・パスワードを受け渡さない）
                $_SESSION['auth']['twostep'] = [
                    'id'   => $id,
                    'keep' => $keep,
                ];

                $_view['user']    = [];
                $_view['twostep'] = true;

                $twostep_send = true;
            }
        } else {
            $success = true;
        }
    }
} else {
    $_view['user'] = [
        'username' => '',
        'password' => '',
        'session'  => null,
    ];
}

// 2段階認証用コードを送信
if ($twostep_send) {
    // トランザクションを開始
    db_transaction();

    // 2段階認証用コードを発行して送信
    service_user_twostep_mail($twostep_email, service_user_twostep_issue($id));

    // トランザクションを終了
    db_commit();
}

// 認証成功
if ($success) {
    // トランザクションを開始
    db_transaction();

    // ログインを完了（信頼済みのブラウザか、今回「次回からこのブラウザではコードを入力しない」を選んだら、省略を続ける）
    service_user_login_complete($id, $keep, ($trusted || (isset($_POST['twostep_session']) && $_POST['twostep_session'] === 'keep')) ? 1 : 0);

    // トランザクションを終了
    db_commit();
}

// ログイン確認
if (!empty($_SESSION['auth']['user']['id'])) {
    if ($_REQUEST['_work'] === 'index') {
        if (isset($_GET['referer']) && is_string($_GET['referer']) && preg_match('#^/(?![/\\\\])#', $_GET['referer'])) {
            $url = $_GET['referer'];
        } else {
            $url = '/auth/home';
        }

        // セッションを再作成
        session_regenerate_id(true);

        // リダイレクト
        redirect($url);
    } else {
        error('不正なアクセスです。');
    }
}

// タイトル
$_view['title'] = $GLOBALS['string']['heading_auth'];
