<?php

import('libs/modules/validator.php');

/**
 * コメントの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_comments($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        // 関連するデータを取得
        if (!isset($queries['select'])) {
            $queries['select'] = 'DISTINCT comments.*, '
                               . 'entries.code AS entry_code, '
                               . 'entries.title AS entry_title, '
                               . 'types.code AS type_code, '
                               . 'contacts.subject AS contact_subject, '
                               . 'users.username AS user_username';
        }

        $queries['from'] = DATABASE_PREFIX . 'comments AS comments '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'entries AS entries ON comments.entry_id = entries.id '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'types AS types ON entries.type_id = types.id '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'contacts AS contacts ON comments.contact_id = contacts.id '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'users AS users ON comments.user_id = users.id';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'comments.deleted IS NULL AND (' . $queries['where'] . ')';
    } else {
        // コメントを取得
        $queries['from'] = DATABASE_PREFIX . 'comments';

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
 * コメントの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_comments($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_comments');

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
    $queries['insert_into'] = DATABASE_PREFIX . 'comments';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * コメントの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_comments($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_comments');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'comments';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * コメントの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_comments($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
    ];

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'comments AS comments',
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
            'delete_from' => DATABASE_PREFIX . 'comments AS comments',
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
 * コメントの検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_comments($queries, $options = [])
{
    $messages = [];

    // 承認
    if (isset($queries['approved'])) {
        if (!validator_boolean($queries['approved'])) {
            $messages['approved'] = '承認の書式が不正です。';
        }
    }

    // お名前
    if (isset($queries['name'])) {
        if (!validator_required($queries['name'])) {
            $messages['name'] = 'お名前が入力されていません。';
        } elseif (!validator_max_length($queries['name'], 50)) {
            $messages['name'] = 'お名前は50文字以内で入力してください。';
        }
    }

    // URL
    if (isset($queries['url'])) {
        if (!validator_required($queries['url'])) {
        } elseif (!validator_max_length($queries['url'], 200)) {
            $messages['url'] = 'URLは200文字以内で入力してください。';
        }
    }

    // コメント内容
    if (isset($queries['message'])) {
        if (!validator_required($queries['message'])) {
            $messages['message'] = 'コメント内容が入力されていません。';
        } elseif (!validator_max_length($queries['message'], 5000)) {
            $messages['message'] = 'コメント内容は5000文字以内で入力してください。';
        }
    }

    // メモ
    if (isset($queries['memo'])) {
        if (!validator_required($queries['memo'])) {
        } elseif (!validator_max_length($queries['memo'], 5000)) {
            $messages['memo'] = 'コメント内容は5000文字以内で入力してください。';
        }
    }

    return $messages;
}

/**
 * コメントの表示用データ作成
 *
 * @param array $data
 *
 * @return array
 */
function view_comments($data)
{
    return $data;
}

/**
 * コメントの初期値
 *
 * @return array
 */
function default_comments()
{
    return [
        'id'         => null,
        'created'    => localdate('Y-m-d H:i:s'),
        'modified'   => localdate('Y-m-d H:i:s'),
        'deleted'    => null,
        'user_id'    => null,
        'entry_id'   => null,
        'contact_id' => null,
        'approved'   => 1,
        'name'       => '',
        'url'        => '',
        'message'    => '',
        'memo'       => null,
    ];
}
