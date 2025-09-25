<?php

import('app/services/user.php');
import('app/services/session.php');
import('app/services/log.php');
import('libs/modules/hash.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ログイン失敗回数とパスワードのソルトを取得
    $users = model('select_users', [
        'select' => 'password_salt, failed, failed_last',
        'where'  => [
            'enabled = 1 AND username = :username',
            [
                'username' => $_POST['username'],
            ],
        ],
    ]);
    if (empty($users)) {
        $password_salt = null;
        $failed        = null;
        $failed_last   = null;
    } else {
        $password_salt = $users[0]['password_salt'];
        $failed        = $users[0]['failed'];
        $failed_last   = $users[0]['failed_last'];

        if (localdate(null, $failed_last) + 60 * 5 > localdate() && $failed >= 10) {
            error('パスワードを連続して間違えたため、このアカウントは凍結されています。5分後以降にお試しください。');
        }
    }

    // パスワード認証
    $users = model('select_users', [
        'select' => 'id',
        'where'  => [
            'enabled = 1 AND username = :username AND password = :password',
            [
                'username' => $_POST['username'],
                'password' => hash_crypt($_POST['password'], $password_salt . ':' . $GLOBALS['config']['hash_salt']),
            ],
        ],
    ]);
    if (empty($users)) {
        // パスワード認証失敗
        $_view['user'] = $_POST;

        $_view['warnings'] = ['ユーザ名もしくはパスワードが違います。'];

        // トランザクションを開始
        db_transaction();

        // 認証失敗回数を記録
        $resource = service_user_update([
            'set'   => [
                'failed'      => $failed + 1,
                'failed_last' => localdate('Y-m-d H:i:s'),
            ],
            'where' => [
                'enabled = 1 AND username = :username',
                [
                    'username' => $_POST['username'],
                ],
            ],
        ]);
        if (!$resource) {
            error('データを編集できません。');
        }

        // トランザクションを終了
        db_commit();
    } else {
        // パスワード認証成功
        $id      = $users[0]['id'];
        $success = true;

        if ($success) {
            // 認証成功
            $_SESSION['auth']['user'] = [
                'id'   => $id,
                'time' => localdate(),
            ];

            // トランザクションを開始
            db_transaction();

            // 認証失敗回数をリセット
            $resource = service_user_update([
                'set'   => [
                    'loggedin'    => localdate('Y-m-d H:i:s'),
                    'failed'      => null,
                    'failed_last' => null,
                ],
                'where' => [
                    'enabled = 1 AND username = :username',
                    [
                        'username' => $_POST['username'],
                    ],
                ],
            ]);
            if (!$resource) {
                error('データを編集できません。');
            }

            // ログイン状態を保持
            if (isset($_COOKIE['auth']['session'])) {
                $old_session = $_COOKIE['auth']['session'];
            } else {
                $old_session = null;
            }
            if (isset($_POST['session']) && $_POST['session'] === 'keep') {
                $keep = 1;
            } else {
                $keep = 0;
            }
            service_user_duration($old_session, rand_string(), $_SESSION['auth']['user']['id'], $keep);

            // 操作ログの記録
            service_log_record('管理者用ページにログインしました。');

            // トランザクションを終了
            db_commit();
        }
    }
} else {
    $_view['user'] = [
        'username' => '',
        'password' => '',
        'session'  => null,
    ];
}

// ログイン確認
if (!empty($_SESSION['auth']['user']['id'])) {
    if ($_REQUEST['_work'] === 'index') {
        if (isset($_GET['referer']) && regexp_match('^\/', $_GET['referer'])) {
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
