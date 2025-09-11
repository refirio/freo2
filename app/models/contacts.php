<?php

import('libs/modules/validator.php');

/**
 * お問い合わせの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_contacts($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // お問い合わせを取得
    $queries['from'] = DATABASE_PREFIX . 'contacts';

    // 削除済みデータは取得しない
    if (!isset($queries['where'])) {
        $queries['where'] = 'TRUE';
    }
    $queries['where'] = 'deleted IS NULL AND (' . $queries['where'] . ')';

    // データを取得
    $results = db_select($queries);

    return $results;
}

/**
 * お問い合わせの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_contacts($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_contacts');

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
    $queries['insert_into'] = DATABASE_PREFIX . 'contacts';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * お問い合わせの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_contacts($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_contacts');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'contacts';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * お問い合わせの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_contacts($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
    ];

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'contacts AS contacts',
            'set'    => [
                'deleted' => localdate('Y-m-d H:i:s'),
            ],
            'where'  => isset($queries['where']) ? $queries['where'] : '',
            'limit'  => isset($queries['limit']) ? $queries['limit'] : '',
        ]);
        if (!$resource) {
            return $resource;
        }
    } else {
        // データを削除
        $resource = db_delete([
            'delete_from' => DATABASE_PREFIX . 'contacts AS contacts',
            'where'       => isset($queries['where']) ? $queries['where'] : '',
            'limit'       => isset($queries['limit']) ? $queries['limit'] : '',
        ]);
        if (!$resource) {
            return $resource;
        }
    }

    return $resource;
}

/**
 * お問い合わせの検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_contacts($queries, $options = [])
{
    $messages = [];

    // お名前
    if (isset($queries['name'])) {
        if (!validator_required($queries['name'])) {
            $messages['name'] = 'お名前が入力されていません。';
        } elseif (!validator_max_length($queries['name'], 50)) {
            $messages['name'] = 'お名前は50文字以内で入力してください。';
        }
    }

    // メールアドレス
    if (isset($queries['email'])) {
        if (!validator_required($queries['email'])) {
            $messages['email'] = 'メールアドレスが入力されていません。';
        } elseif (!validator_email($queries['email'])) {
            $messages['email'] = 'メールアドレスの入力内容が正しくありません。';
        } elseif (!validator_max_length($queries['email'], 80)) {
            $messages['email'] = 'メールアドレスは80文字以内で入力してください。';
        }
    }

    // お問い合わせ内容
    if (isset($queries['message'])) {
        if (!validator_required($queries['message'])) {
            $messages['message'] = 'お問い合わせ内容が入力されていません。';
        } elseif (!validator_max_length($queries['message'], 5000)) {
            $messages['message'] = 'お問い合わせ内容は5000文字以内で入力してください。';
        }
    }

    // メモ
    if (isset($queries['memo'])) {
        if (!validator_required($queries['memo'])) {
        } elseif (!validator_max_length($queries['memo'], 5000)) {
            $messages['memo'] = 'お問い合わせ内容は5000文字以内で入力してください。';
        }
    }

    return $messages;
}

/**
 * お問い合わせの表示用データ作成
 *
 * @param array $data
 *
 * @return array
 */
function view_contacts($data)
{
    return $data;
}

/**
 * お問い合わせの初期値
 *
 * @return array
 */
function default_contacts()
{
    return [
        'id'       => null,
        'created'  => localdate('Y-m-d H:i:s'),
        'modified' => localdate('Y-m-d H:i:s'),
        'deleted'  => null,
        'name'     => '',
        'email'    => '',
        'message'  => '',
        'memo'     => null,
    ];
}
