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
            $messages['memo'] = 'メモ内容は5000文字以内で入力してください。';
        }
    }

    return $messages;
}

/**
 * コメントの絞り込み
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function filter_comments($queries, $options = [])
{
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        $wheres = [];
        $pagers = [];

        // 承認を取得
        if (isset($queries['approved'])) {
            if ($queries['approved'] !== '') {
                $wheres[] = 'comments.approved = ' . db_escape($queries['approved']);
                $pagers[] = 'approved=' . rawurlencode($queries['approved']);
            }
        }

        // 名前を取得
        if (isset($queries['name'])) {
            if ($queries['name'] !== '') {
                $wheres[] = 'comments.name LIKE ' . db_escape('%' . $queries['name'] . '%');
                $pagers[] = 'name=' . rawurlencode($queries['name']);
            }
        }

        // URLを取得
        if (isset($queries['url'])) {
            if ($queries['url'] !== '') {
                $wheres[] = 'comments.url LIKE ' . db_escape('%' . $queries['url'] . '%');
                $pagers[] = 'url=' . rawurlencode($queries['url']);
            }
        }

        // コメント内容を取得
        if (isset($queries['message'])) {
            if ($queries['message'] !== '') {
                $wheres[] = 'comments.message LIKE ' . db_escape('%' . $queries['message'] . '%');
                $pagers[] = 'message=' . rawurlencode($queries['message']);
            }
        }

        // メモを取得
        if (isset($queries['memo'])) {
            if ($queries['memo'] !== '') {
                $wheres[] = 'comments.memo LIKE ' . db_escape('%' . $queries['memo'] . '%');
                $pagers[] = 'memo=' . rawurlencode($queries['memo']);
            }
        }

        // キーワードを取得
        if (isset($queries['keyword'])) {
            if ($queries['keyword'] !== '') {
                $wheres[] = '(comments.name LIKE ' . db_escape('%' . $queries['keyword'] . '%') . ' OR comments.url LIKE ' . db_escape('%' . $queries['keyword'] . '%') . ' OR comments.message LIKE ' . db_escape('%' . $queries['keyword'] . '%') . ')';
                $pagers[] = 'keyword=' . rawurlencode($queries['keyword']);
            }
        }

        $results = [
            'where' => implode(' AND ', $wheres),
            'pager' => implode('&amp;', $pagers),
        ];
    } else {
        $results = [
            'where' => null,
            'pager' => null,
        ];
    }

    return $results;
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
