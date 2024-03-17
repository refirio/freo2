<?php

import('libs/plugins/validator.php');

/**
 * 操作ログの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_logs($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        // 関連するデータを取得
        if (!isset($queries['select'])) {
            $queries['select'] = 'logs.*, '
                               . 'users.id AS user_id, '
                               . 'users.username AS user_username, '
                               . 'users.authority_id AS user_authority_id, '
                               . 'users.name AS user_name, '
                               . 'users.email AS user_email';
        }

        $queries['from'] = DATABASE_PREFIX . 'logs AS logs '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'users AS users ON logs.user_id = users.id';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'logs.deleted IS NULL AND users.deleted IS NULL AND (' . $queries['where'] . ')';
    } else {
        // ユーザを取得
        $queries['from'] = DATABASE_PREFIX . 'logs';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'deleted IS NULL AND (' . $queries['where'] . ')';
    }

    // データを取得
    $results = db_select($queries);

    return $results;
}

/**
 * 操作ログの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_logs($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_logs');

    if (isset($queries['values']['created'])) {
        if ($queries['values']['created'] === false) {
            unset($queries['values']['created']);
        }
    } else {
        $queries['values']['created'] = $defaults['created'];
    }
    if (isset($queries['values']['modified'])) {
        if ($queries['values']['modified'] === false) {
            unset($queries['values']['modified']);
        }
    } else {
        $queries['values']['modified'] = $defaults['modified'];
    }

    // データを登録
    $queries['insert_into'] = DATABASE_PREFIX . 'logs';

    $resource = db_insert($queries);

    return $resource;
}

/**
 * 操作ログの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_logs($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_logs');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'logs';

    $resource = db_update($queries);

    return $resource;
}

/**
 * 操作ログの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_logs($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
    ];

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'logs AS logs',
            'set'    => [
                'deleted' => localdate('Y-m-d H:i:s'),
            ],
            'where'  => isset($queries['where']) ? $queries['where'] : '',
            'limit'  => isset($queries['limit']) ? $queries['limit'] : '',
        ]);
    } else {
        // データを削除
        $resource = db_delete([
            'delete_from' => DATABASE_PREFIX . 'logs AS logs',
            'where'       => isset($queries['where']) ? $queries['where'] : '',
            'limit'       => isset($queries['limit']) ? $queries['limit'] : '',
        ]);
    }

    return $resource;
}

/**
 * 操作ログの検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_logs($queries, $options = [])
{
    $options = [
        'duplicate' => isset($options['duplicate']) ? $options['duplicate'] : true,
    ];

    $messages = [];

    // IPアドレス
    if (isset($queries['ip'])) {
        if (!validator_required($queries['ip'])) {
            $messages['ip'] = 'IPアドレスが入力されていません。';
        }
    }

    // ページ
    if (isset($queries['page'])) {
        if (!validator_required($queries['page'])) {
            $messages['page'] = 'ページが入力されていません。';
        }
    }

    return $messages;
}

/**
 * 操作ログの初期値
 *
 * @return array
 */
function default_logs()
{
    return [
        'id'            => null,
        'created'       => localdate('Y-m-d H:i:s'),
        'modified'      => localdate('Y-m-d H:i:s'),
        'deleted'       => null,
        'user_id'       => null,
        'administrator' => null,
        'ip'            => '',
        'agent'         => null,
        'model'         => null,
        'exec'          => null,
        'message'       => null,
        'detail'        => null,
        'page'          => '',
    ];
}
