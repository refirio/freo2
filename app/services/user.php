<?php

import('app/services/session.php');
import('app/services/log.php');
import('libs/modules/cookie.php');

/**
 * ユーザの登録
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

    // ユーザを登録
    $resource = model('insert_users', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
    }

    return $resource;
}

/**
 * ユーザの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_user_update($queries, $options = [])
{
    $options = [
        'id'     => isset($options['id'])     ? $options['id']     : null,
        'update' => isset($options['update']) ? $options['update'] : null,
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

    // ユーザを編集
    $resource = update_users($queries, $options);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $resource;
}

/**
 * ユーザの削除
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

    // ユーザを削除
    $resource = model('delete_users', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}

/**
 * ユーザのログイン
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
        $new_session_id = rand_string();

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
            // ユーザを更新
            $resource = service_user_update([
                'set'   => [
                    'loggedin' => localdate('Y-m-d H:i:s'),
                ],
                'where' => [
                    'id = :id',
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
 * ユーザのセッション保持
 *
 * @param string $old_session
 * @param string $new_session
 * @param string $user_id
 * @param int $keep
 */
function service_user_duration($old_session, $new_session, $user_id, $keep)
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
 * ユーザのログアウト
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
