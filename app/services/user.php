<?php

import('app/services/session.php');
import('app/services/log.php');
import('app/services/mail.php');
import('libs/modules/cookie.php');
import('libs/modules/hash.php');

/**
 * ユーザーの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_user_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'users', 'insert');

    // ユーザーを登録
    $resource = model('insert_users', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
    }

    return $resource;
}

/**
 * ユーザーの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_user_update($queries, $options = [])
{
    $options = [
        'id'             => isset($options['id'])             ? $options['id']             : null,
        'update'         => isset($options['update'])         ? $options['update']         : null,
        'attribute_sets' => isset($options['attribute_sets']) ? $options['attribute_sets'] : [],
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $users = model('select_users', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($users)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'users', 'update');

    // ユーザーを編集
    $resource = update_users($queries, $options);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $resource;
}

/**
 * ユーザーの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_user_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'users', 'delete');

    // ユーザーを削除
    $resource = model('delete_users', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}

/**
 * ユーザーのログイン
 *
 * @param string $session_id
 *
 * @return array
 */
function service_user_login($session_id)
{
    // セッションを取得
    $users = model('select_sessions', [
        'select' => 'user_id, keep',
        'where'  => [
            'id = :id AND expire > :expire',
            [
                'id'     => $session_id,
                'expire' => localdate('Y-m-d H:i:s'),
            ],
        ],
    ]);

    $session = false;
    $user_id = null;
    if (!empty($users)) {
        // セッションの有効期限を取得
        if ($users[0]['keep']) {
            $expire = $GLOBALS['config']['cookie_expire'];
        } else {
            $expire = $GLOBALS['config']['login_expire'];
        }

        // セッションを更新
        $new_session_id = service_user_token();

        $resource = service_session_update([
            'set'   => [
                'id'     => $new_session_id,
                'agent'  => $_SERVER['HTTP_USER_AGENT'],
                'expire' => localdate('Y-m-d H:i:s', time() + $expire),
            ],
            'where' => [
                'id = :id',
                [
                    'id' => $session_id,
                ],
            ],
        ]);
        if ($resource) {
            cookie_set('auth[session]', $new_session_id, time() + $GLOBALS['config']['cookie_expire'], $GLOBALS['config']['cookie_path'], $GLOBALS['config']['cookie_domain'], $GLOBALS['config']['cookie_secure']);
        } else {
            error('データを編集できません。');
        }

        if ($users[0]['keep']) {
            // ユーザーを更新
            $resource = service_user_update([
                'set'   => [
                    'loggedin' => localdate('Y-m-d H:i:s'),
                ],
                'where' => [
                    'id = :id AND enabled = 1',
                    [
                        'id' => $users[0]['user_id'],
                    ],
                ],
            ]);
            if (!$resource) {
                error('データを編集できません。');
            }

            $session = true;
            $user_id = $users[0]['user_id'];
        }
    }

    return [$session, $user_id];
}

/**
 * ユーザーのセッション保持
 *
 * @param string $old_session
 * @param string $new_session
 * @param string $user_id
 * @param int $keep
 * @param int $twostep
 */
function service_user_duration($old_session, $new_session, $user_id, $keep, $twostep)
{
    // セッションを取得
    $flag = false;
    if ($old_session) {
        $users = model('select_sessions', [
            'select' => 'user_id',
            'where'  => [
                'id = :id',
                [
                    'id' => $old_session,
                ],
            ],
        ]);
        if (!empty($users)) {
            $flag = true;
        }
    }

    // セッションの有効期限を取得
    if ($keep) {
        $expire = $GLOBALS['config']['cookie_expire'];
    } else {
        $expire = $GLOBALS['config']['login_expire'];
    }

    // セッションを更新
    if ($flag === true) {
        $resource = service_session_update([
            'set'   => [
                'id'      => $new_session,
                'user_id' => $user_id,
                'agent'   => $_SERVER['HTTP_USER_AGENT'],
                'keep'    => $keep,
                'twostep' => $twostep,
                'expire'  => localdate('Y-m-d H:i:s', time() + $expire),
            ],
            'where' => [
                'id = :id',
                [
                    'id' => $old_session,
                ],
            ],
        ]);
        if (!$resource) {
            error('データを編集できません。');
        }
    } else {
        $resource = service_session_insert([
            'values' => [
                'id'      => $new_session,
                'user_id' => $user_id,
                'agent'   => $_SERVER['HTTP_USER_AGENT'],
                'keep'    => $keep,
                'twostep' => $twostep,
                'expire'  => localdate('Y-m-d H:i:s', time() + $expire),
            ],
        ]);
        if (!$resource) {
            error('データを登録できません。');
        }
    }

    cookie_set('auth[session]', $new_session, localdate() + $GLOBALS['config']['cookie_expire'], $GLOBALS['config']['cookie_path'], $GLOBALS['config']['cookie_domain'], $GLOBALS['config']['cookie_secure']);

    // 古いセッションを削除
    $resource = service_session_delete([
        'where' => [
            'expire < :expire',
            [
                'expire' => localdate('Y-m-d H:i:s'),
            ],
        ],
    ]);
    if (!$resource) {
        error('データを削除できません。');
    }
}

/**
 * ユーザーのログアウト
 *
 * @param string $session_id
 * @param string $user_id
 */
function service_user_logout($session_id, $user_id)
{
    $resource = service_session_update([
        'set'   => [
            'keep' => 0
        ],
        'where' => [
            'id = :session AND user_id = :user_id',
            [
                'session' => $session_id,
                'user_id' => $user_id,
            ],
        ],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }
}

/**
 * ユーザーのアカウントが凍結されているか（ログインに10回続けて失敗すると、最後の失敗から5分間）
 *
 * @param string $username
 *
 * @return bool
 */
function service_user_frozen($username)
{
    $users = model('select_users', [
        'select' => 'failed, failed_last',
        'where'  => [
            'username = :username',
            [
                'username' => $username,
            ],
        ],
    ]);
    if (empty($users)) {
        return false;
    }

    return localdate(null, $users[0]['failed_last']) + 60 * 5 > localdate() && $users[0]['failed'] >= 10;
}

/**
 * ペッパーを適用したパスワードの前処理（bcrypt/argon2 用）
 * HMACを通すことで、ペッパーを効かせつつ bcrypt の72バイト上限・NULバイト問題も回避する。
 *
 * @param string $password
 *
 * @return string
 */
function service_user_password_prepare($password)
{
    return base64_encode(hash_hmac('sha256', (string)$password, (string)$GLOBALS['config']['hash_pepper'], true));
}

/**
 * パスワードのハッシュを作成する（設定 hash_algorithm に従う）
 *
 * @param string $password
 *
 * @return string 保存用の文字列（bcrypt/argon2 は password_hash 形式、md5 は「ハッシュ/ペッパー」形式）
 */
function service_user_password_hash($password)
{
    if ($GLOBALS['config']['hash_algorithm'] === 'md5') {
        // レガシー（古いPHP環境向けの互換方式）。ハッシュとペッパーを「/」で結合して1つの列に保存する
        import('libs/modules/hash.php');

        $salt = hash_salt();

        return hash_crypt($password, $salt . ':' . $GLOBALS['config']['hash_pepper']) . '/' . $salt;
    }

    if ($GLOBALS['config']['hash_algorithm'] === 'argon2') {
        if (!defined('PASSWORD_ARGON2ID')) {
            error('この環境では argon2 を利用できません。設定（hash_algorithm）を確認してください。');
        }

        return password_hash(service_user_password_prepare($password), PASSWORD_ARGON2ID);
    }

    // 既定: bcrypt（どの環境でも利用できる）
    return password_hash(service_user_password_prepare($password), PASSWORD_BCRYPT);
}

/**
 * パスワードを照合する（保存形式を自動判別する）
 *
 * @param string      $password
 * @param string|null $hash 保存されているハッシュ
 *
 * @return bool
 */
function service_user_password_verify($password, $hash)
{
    if ($hash === null || $hash === '') {
        return false;
    }

    // bcrypt / argon2（password_hash 形式。先頭が $）
    if (strncmp($hash, '$', 1) === 0) {
        return password_verify(service_user_password_prepare($password), $hash);
    }

    // レガシー md5（「ハッシュ/ペッパー」形式）
    import('libs/modules/hash.php');

    $parts = explode('/', $hash, 2);
    if (count($parts) !== 2) {
        return false;
    }

    return hash_equals($parts[0], hash_crypt($password, $parts[1] . ':' . $GLOBALS['config']['hash_pepper']));
}

/**
 * 保存されているハッシュを、設定の方式に合わせて作り直すべきかを判定する（ログイン時の付け替え用）
 *
 * @param string $hash
 *
 * @return bool
 */
function service_user_password_needs_rehash($hash)
{
    // md5 を選んでいる環境では作り直さない（モダン方式を弱体化させないため、既存のハッシュはそのまま）
    if ($GLOBALS['config']['hash_algorithm'] === 'md5') {
        return false;
    }

    // レガシー md5 形式（先頭が $ でない）なら、設定のモダン方式へ移行する
    if (strncmp($hash, '$', 1) !== 0) {
        return true;
    }

    // すでに password_hash 形式。方式・コストの変更があれば作り直す
    if ($GLOBALS['config']['hash_algorithm'] === 'argon2') {
        if (!defined('PASSWORD_ARGON2ID')) {
            return false;
        }

        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }

    return password_needs_rehash($hash, PASSWORD_BCRYPT);
}

/**
 * ユーザーのパスワード認証（失敗したら、失敗した回数を記録する）
 *
 * @param string $username
 * @param string $password
 *
 * @return array|null 成功したらユーザー（id, enabled, twostep, twostep_email）、失敗したら null
 */
function service_user_authenticate($username, $password)
{
    // ユーザーを取得
    $users = model('select_users', [
        'select' => 'id, enabled, twostep, twostep_email, password, failed, failed_last',
        'where'  => [
            'username = :username',
            [
                'username' => $username,
            ],
        ],
    ]);

    // パスワード認証（照合は保存形式に応じてPHP側で行う）
    if (!empty($users) && service_user_password_verify($password, $users[0]['password'])) {
        // ログイン時のハッシュ付け替え（設定の方式に合っていなければ作り直す）
        if (service_user_password_needs_rehash($users[0]['password'])) {
            $resource = service_user_update([
                'set'   => [
                    'password' => service_user_password_hash($password),
                ],
                'where' => [
                    'id = :id',
                    [
                        'id' => $users[0]['id'],
                    ],
                ],
            ]);
            if (!$resource) {
                error('データを編集できません。');
            }
        }

        return [
            'id'            => $users[0]['id'],
            'enabled'       => $users[0]['enabled'],
            'twostep'       => $users[0]['twostep'],
            'twostep_email' => $users[0]['twostep_email'],
        ];
    }

    // 認証失敗回数を記録（前の失敗から凍結の期間が過ぎていれば、1から数え直す）
    if (empty($users) || empty($users[0]['failed_last']) || localdate(null, $users[0]['failed_last']) + 60 * 5 <= localdate()) {
        $failed = 0;
    } else {
        $failed = $users[0]['failed'];
    }

    $resource = service_user_update([
        'set'   => [
            'failed'      => $failed + 1,
            'failed_last' => localdate('Y-m-d H:i:s'),
        ],
        'where' => [
            'enabled = 1 AND username = :username',
            [
                'username' => $username,
            ],
        ],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }

    return null;
}

/**
 * ユーザーの2段階認証で、このブラウザのコードの入力を省略するか（ブラウザを信頼しているか）
 *
 * @param string $user_id
 * @param string|null $session_id
 *
 * @return bool
 */
function service_user_twostep_trusted($user_id, $session_id)
{
    if ($session_id === null || $session_id === '') {
        return false;
    }

    $sessions = model('select_sessions', [
        'select' => 'twostep',
        'where'  => [
            'id = :session AND user_id = :user_id',
            [
                'session' => $session_id,
                'user_id' => $user_id,
            ],
        ],
    ]);

    return !empty($sessions) && intval($sessions[0]['twostep']) === 1;
}

/**
 * メールで送るコードを作る（パスワード再設定の暗証コードと2段階認証用コードで共通。数字8桁）
 *
 * @return string
 */
function service_user_code()
{
    // 推測されないよう、暗号用の乱数を使う
    return (string)random_int(10000000, 99999999);
}

/**
 * トークンを作る（ログイン状態を保持するセッションID、パスワード再設定・メールアドレスの存在確認のURL用。16進数32文字）
 *
 * @return string
 */
function service_user_token()
{
    // 推測されないよう、暗号用の乱数を使う
    return bin2hex(random_bytes(16));
}

/**
 * ユーザーの2段階認証用コードを発行する（有効期限は10分。失敗した回数は数え直す）
 *
 * @param string $user_id
 *
 * @return string
 */
function service_user_twostep_issue($user_id)
{
    $twostep_code = service_user_code();

    $resource = service_user_update([
        'set'   => [
            'twostep_code'   => $twostep_code,
            'twostep_expire' => localdate('Y-m-d H:i:s', time() + 60 * 10),
            'twostep_failed' => null,
        ],
        'where' => [
            'id = :id',
            [
                'id' => $user_id,
            ],
        ],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $twostep_code;
}

/**
 * 2段階認証用コードをメールで送る（ログインと、本人が2段階認証用メールアドレスを確認するときに使う）
 *
 * @param string $email
 * @param string $twostep_code
 */
function service_user_twostep_mail($email, $twostep_code)
{
    // メール送信内容を作成（本文のテンプレートは $_view['code'] を読む）
    $GLOBALS['_view']['code'] = $twostep_code;

    $subject = $GLOBALS['setting']['mail_twostep_subject'];
    $message = view('mail/auth/twostep.php', true);
    $headers = [
        'From' => $GLOBALS['setting']['mail_from'],
    ];

    unset($GLOBALS['_view']['code']);

    // メールを送信
    if (service_mail_send($email, $subject, $message, $headers) === false) {
        error('メールを送信できません。');
    }
}

/**
 * ユーザーの2段階認証用コードを照合する（間違えたら失敗した回数を記録し、上限に達したらコードを無効にする）
 *
 * @param string $user_id
 * @param string $twostep_code
 *
 * @return string|null 成功したら null、失敗したらメッセージ
 */
function service_user_twostep_verify($user_id, $twostep_code)
{
    $twostep_code = trim((string)$twostep_code);

    if ($twostep_code === '') {
        return '2段階認証用コードが入力されていません。';
    }

    $users = model('select_users', [
        'select' => 'twostep_code, twostep_expire',
        'where'  => [
            'id = :id',
            [
                'id' => $user_id,
            ],
        ],
    ]);
    if (empty($users)) {
        return '2段階認証用コードが違います。';
    }

    if ($users[0]['twostep_code'] === null) {
        return '2段階認証用コードが無効になっています。コードを再送信してください。';
    }

    if (!hash_equals($users[0]['twostep_code'], $twostep_code)) {
        if (service_user_code_failed($user_id, 'twostep')) {
            return '2段階認証用コードを続けて間違えたため、このコードを無効にしました。コードを再送信してください。';
        } else {
            return '2段階認証用コードが違います。';
        }
    }

    if (empty($users[0]['twostep_expire']) || localdate(null, $users[0]['twostep_expire']) < localdate()) {
        return '2段階認証用コードの有効期限が終了しています。';
    }

    return null;
}

/**
 * ユーザーのログインを完了する（セッションへの記録、失敗回数と使い終わったコードの消去、ログイン状態の保持、操作ログ）
 *
 * @param string $user_id
 * @param int $keep    ログイン状態を記憶するか
 * @param int $twostep このブラウザで2段階認証のコードの入力を省略するか
 */
function service_user_login_complete($user_id, $keep, $twostep)
{
    // 2段階認証の認証待ちの状態を破棄
    unset($_SESSION['auth']['twostep']);

    $_SESSION['auth']['user'] = [
        'id'   => $user_id,
        'time' => localdate(),
    ];

    // 認証失敗回数をリセットし、使い終わった2段階認証用コードを削除
    $resource = service_user_update([
        'set'   => [
            'loggedin'       => localdate('Y-m-d H:i:s'),
            'failed'         => null,
            'failed_last'    => null,
            'twostep_code'   => null,
            'twostep_expire' => null,
            'twostep_failed' => null,
        ],
        'where' => [
            'id = :id AND enabled = 1',
            [
                'id' => $user_id,
            ],
        ],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }

    // ログイン状態を保持
    $old_session = isset($_COOKIE['auth']['session']) ? $_COOKIE['auth']['session'] : null;

    service_user_duration($old_session, service_user_token(), $user_id, $keep, $twostep);

    // 操作ログの記録
    service_log_record('管理者用ページにログインしました。');
}

/**
 * ユーザーのコードの照合に失敗した回数を記録し、上限に達したらそのコードを無効にする
 * （パスワード再設定の暗証コードは token、2段階認証用コードは twostep）
 *
 * @param string $user_id
 * @param string $type
 *
 * @return bool 上限に達してコードを無効にしたら true
 */
function service_user_code_failed($user_id, $type)
{
    // 列名に使うので、決まったもの以外は受け付けない
    if ($type !== 'token' && $type !== 'twostep') {
        error('不正な操作です。');
    }

    $users = model('select_users', [
        'select' => $type . '_failed AS failed',
        'where'  => [
            'id = :id',
            [
                'id' => $user_id,
            ],
        ],
    ]);
    if (empty($users)) {
        return false;
    }

    $failed = intval($users[0]['failed']) + 1;

    if ($failed >= $GLOBALS['config']['code_failed_limit']) {
        // 上限に達したら、コードを無効にする（回数は、次にコードを発行したときに数え直す）
        $sets = [
            $type . '_code'   => null,
            $type . '_failed' => null,
        ];
    } else {
        $sets = [
            $type . '_failed' => $failed,
        ];
    }

    $resource = service_user_update([
        'set'   => $sets,
        'where' => [
            'id = :id',
            [
                'id' => $user_id,
            ],
        ],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $failed >= $GLOBALS['config']['code_failed_limit'];
}

/**
 * ユーザーの2段階認証用メールアドレスに、コードが届くかの確認が必要か
 * （2段階認証を有効にするときと、有効のまま2段階認証用メールアドレスを変えるとき）
 *
 * @param string $user_id
 * @param string $twostep
 * @param string $twostep_email
 *
 * @return bool
 */
function service_user_twostep_verify_required($user_id, $twostep, $twostep_email)
{
    if ((string)$twostep !== '1') {
        return false;
    }

    $users = model('select_users', [
        'select' => 'twostep, twostep_email',
        'where'  => [
            'id = :id',
            [
                'id' => $user_id,
            ],
        ],
    ]);
    if (empty($users)) {
        return true;
    }

    return (string)$users[0]['twostep'] !== '1' || (string)$users[0]['twostep_email'] !== (string)$twostep_email;
}

/**
 * ユーザーの2段階認証で、コードの入力を省略しているブラウザの解除が必要か
 * （2段階認証を無効にするときと、2段階認証用メールアドレスを変えるとき）
 *
 * @param string $twostep               変更後の2段階認証
 * @param string|null $twostep_email    変更後の2段階認証用メールアドレス
 * @param string|null $current_email    変更前の2段階認証用メールアドレス
 *
 * @return bool
 */
function service_user_twostep_reset_required($twostep, $twostep_email, $current_email)
{
    return (string)$twostep !== '1' || (string)$current_email !== (string)$twostep_email;
}

/**
 * ユーザーの2段階認証で、コードの入力を省略しているブラウザをすべて解除
 *
 * @param string $user_id
 */
function service_user_twostep_reset($user_id)
{
    $resource = service_session_update([
        'set'   => [
            'twostep' => 0,
        ],
        'where' => [
            'user_id = :user_id',
            [
                'user_id' => $user_id,
            ],
        ],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }
}

/**
 * パスワード再設定用のURLのトークンと暗証コードを発行する（有効期限は10分。失敗した回数は数え直す）
 * （token はメールアドレスの存在確認と共用しているので、進行中の存在確認のURLは使えなくなる）
 *
 * @param string $email
 *
 * @return array|null 発行したらユーザー（id, email, token, token_code, token_expire）、メールアドレスが見つからなければ null
 */
function service_user_password_issue($email)
{
    $users = model('select_users', [
        'select' => 'id',
        'where'  => [
            'email = :email',
            [
                'email' => $email,
            ],
        ],
    ]);
    if (empty($users)) {
        return null;
    }

    $resource = service_user_update([
        'set'   => [
            'token'        => service_user_token(),
            'token_code'   => service_user_code(),
            'token_expire' => localdate('Y-m-d H:i:s', time() + 60 * 10),
            'token_failed' => null,
        ],
        'where' => [
            'id = :id',
            [
                'id' => $users[0]['id'],
            ],
        ],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }

    $users = model('select_users', [
        'select' => 'id, email, token, token_code, token_expire',
        'where'  => [
            'id = :id',
            [
                'id' => $users[0]['id'],
            ],
        ],
    ]);

    return $users[0];
}

/**
 * パスワード再設定用の暗証コードをメールで送る
 *
 * @param string $email
 * @param string $token_code
 * @param string $token_expire
 */
function service_user_password_mail($email, $token_code, $token_expire)
{
    // メール送信内容を作成（本文のテンプレートは $_view['token_code'] と $_view['token_expire'] を読む）
    $GLOBALS['_view']['token_code']   = $token_code;
    $GLOBALS['_view']['token_expire'] = $token_expire;

    $subject = $GLOBALS['setting']['mail_password_subject'];
    $message = view('mail/auth/password.php', true);
    $headers = [
        'From' => $GLOBALS['setting']['mail_from'],
    ];

    unset($GLOBALS['_view']['token_code'], $GLOBALS['_view']['token_expire']);

    // メールを送信
    if (service_mail_send($email, $subject, $message, $headers) === false) {
        error('メールを送信できません。');
    }
}

/**
 * パスワード再設定用の暗証コードの照合に失敗したときに、失敗した回数を記録する
 * （照合そのものは validate_users() が行う。誤っていたときだけ呼ぶ）
 *
 * @param string $email
 * @param string $token_code
 *
 * @return string|null 検証のメッセージを差し替えるならメッセージ、そのままなら null
 */
function service_user_password_failed($email, $token_code)
{
    $users = model('select_users', [
        'select' => 'id, token_code',
        'where'  => [
            'email = :email',
            [
                'email' => $email,
            ],
        ],
    ]);
    if (empty($users)) {
        return null;
    }

    if ($users[0]['token_code'] === null) {
        return '暗証コードが無効になっています。パスワードの再発行をやり直してください。';
    }

    // 暗証コードは合っている（有効期限が終了しているなど）
    if (hash_equals((string)$users[0]['token_code'], (string)$token_code)) {
        return null;
    }

    if (service_user_code_failed($users[0]['id'], 'token')) {
        return '暗証コードを続けて間違えたため、この暗証コードを無効にしました。パスワードの再発行をやり直してください。';
    }

    return null;
}

/**
 * メールアドレスの存在確認用のトークンを発行する（有効期限は24時間）
 * （token はパスワード再設定と共用しているので、進行中のパスワード再設定のURLは使えなくなる）
 *
 * @param string $user_id
 *
 * @return array ユーザー（email, token）
 */
function service_user_email_issue($user_id)
{
    $resource = service_user_update([
        'set'   => [
            'token'        => service_user_token(),
            'token_expire' => localdate('Y-m-d H:i:s', time() + 60 * 60 * 24),
        ],
        'where' => [
            'id = :id',
            [
                'id' => $user_id,
            ],
        ],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }

    $users = model('select_users', [
        'select' => 'email, token',
        'where'  => [
            'id = :id',
            [
                'id' => $user_id,
            ],
        ],
    ]);

    return $users[0];
}

/**
 * メールアドレスの存在確認用のURLをメールで送る
 *
 * @param string $email
 * @param string $token
 */
function service_user_email_mail($email, $token)
{
    // メール送信内容を作成（本文のテンプレートは $_view['url'] を読む）
    $GLOBALS['_view']['url'] = $GLOBALS['config']['http_url'] . '/auth/email_verify?key=' . rawurlencode($email) . '&token=' . $token;

    $subject = $GLOBALS['setting']['mail_verify_subject'];
    $message = view('mail/auth/email_verify.php', true);
    $headers = [
        'From' => $GLOBALS['setting']['mail_from'],
    ];

    unset($GLOBALS['_view']['url']);

    // メールを送信
    if (service_mail_send($email, $subject, $message, $headers) === false) {
        error('メールを送信できません。');
    }
}

/**
 * メールアドレスの存在確認用のURLを照合し、合っていれば確認済みにする
 *
 * @param string $email
 * @param string $token
 *
 * @return bool 確認済みにしたら true、URLの有効期限が終了しているか正しくなければ false
 */
function service_user_email_verify($email, $token)
{
    // 一致しなくても更新は0件で成功するので、先に確認する
    $users = model('select_users', [
        'select' => 'id',
        'where'  => [
            'email = :email AND token = :token AND token_expire > :token_expire',
            [
                'email'        => $email,
                'token'        => $token,
                'token_expire' => localdate('Y-m-d H:i:s'),
            ],
        ],
    ]);
    if (empty($users)) {
        return false;
    }

    $resource = service_user_update([
        'set'   => [
            'email_verified' => 1,
            'token'          => null,
            'token_expire'   => null,
        ],
        'where' => [
            'id = :id',
            [
                'id' => $users[0]['id'],
            ],
        ],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }

    return true;
}
