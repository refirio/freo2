<?php

/**
 * 未実行のマイグレーションの取得
 *
 * @return array
 */
function service_update_pending()
{
    // マイグレーションを一度も実行していなければ、初回の設置前として扱う（更新の対象にしない）
    if (!db_migrate_installed()) {
        return [];
    }

    return db_migrate_targets(true);
}

/**
 * 更新できる管理者がログインしているか
 * （DB の更新が済むまで、テーブルが新しいコードと合わないことがあるので、読む列はユーザーの有効状態と権限の強さだけにする）
 *
 * @return bool
 */
function service_update_admin()
{
    if (empty($_SESSION['auth']['user']['id'])) {
        return false;
    }

    $users = model('select_users', [
        'select' => 'authority_id',
        'where'  => [
            'id = :id AND enabled = 1',
            [
                'id' => $_SESSION['auth']['user']['id'],
            ],
        ],
    ]);
    if (empty($users)) {
        return false;
    }

    $authorities = model('select_authorities', [
        'select' => 'power',
        'where'  => [
            'id = :id',
            [
                'id' => $users[0]['authority_id'],
            ],
        ],
    ]);
    if (empty($authorities)) {
        return false;
    }

    return $authorities[0]['power'] == 3;
}
