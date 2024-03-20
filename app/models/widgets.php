<?php

import('libs/plugins/validator.php');

/**
 * ウィジェットの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_widgets($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // ウィジェットを取得
    $queries['from'] = DATABASE_PREFIX . 'widgets';

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
 * ウィジェットの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_widgets($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_widgets');

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
    $queries['insert_into'] = DATABASE_PREFIX . 'widgets';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * ウィジェットの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_widgets($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_widgets');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'widgets';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * ウィジェットの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_widgets($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
    ];

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'widgets AS widgets',
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
            'delete_from' => DATABASE_PREFIX . 'widgets AS widgets',
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
 * ウィジェットの正規化
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function normalize_widgets($queries, $options = [])
{
    // 並び順
    if (isset($queries['sort'])) {
        $queries['sort'] = mb_convert_kana($queries['sort'], 'n', MAIN_INTERNAL_ENCODING);
    } else {
        if (!$queries['id']) {
            $widgets = db_select([
                'select' => 'MAX(sort) AS sort',
                'from'   => DATABASE_PREFIX . 'widgets',
            ]);
            $queries['sort'] = $widgets[0]['sort'] + 1;
        }
    }

    return $queries;
}

/**
 * ウィジェットの検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_widgets($queries, $options = [])
{
    $options = [
        'duplicate' => isset($options['duplicate']) ? $options['duplicate'] : true,
    ];

    $messages = [];

    // コード
    if (isset($queries['code'])) {
        if (!validator_required($queries['code'])) {
            $messages['code'] = 'コードが入力されていません。';
        } elseif (!validator_alpha_dash($queries['code'])) {
            $messages['code'] = 'コードは半角英数字で入力してください。';
        } elseif (!validator_between($queries['code'], 2, 80)) {
            $messages['code'] = 'コードは2文字以上80文字以内で入力してください。';
        } elseif ($options['duplicate'] === true) {
            if (empty($queries['id'])) {
                $users = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'widgets',
                    'where'  => [
                        'code = :code',
                        [
                            'code' => $queries['code'],
                        ],
                    ],
                ]);
            } else {
                $users = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'widgets',
                    'where'  => [
                        'id != :id AND code = :code',
                        [
                            'id'   => $queries['id'],
                            'code' => $queries['code'],
                        ],
                    ],
                ]);
            }
            if (!empty($users)) {
                $messages['code'] = '入力されたコードはすでに使用されています。';
            }
        }
    }

    // タイトル
    if (isset($queries['title'])) {
        if (!validator_required($queries['title'])) {
            $messages['title'] = 'タイトルが入力されていません。';
        } elseif (!validator_max_length($queries['title'], 20)) {
            $messages['title'] = 'タイトルは20文字以内で入力してください。';
        }
    }

    // テキスト
    if (isset($queries['text'])) {
        if (!validator_required($queries['text'])) {
        } elseif (!validator_max_length($queries['text'], 5000)) {
            $messages['text'] = 'テキストは5000文字以内で入力してください。';
        }
    }

    // 並び順
    if (isset($queries['sort'])) {
        if (!validator_required($queries['sort'])) {
            $messages['sort'] = '並び順が入力されていません。';
        } elseif (!validator_numeric($queries['sort'])) {
            $messages['sort'] = '並び順は半角数字で入力してください。';
        } elseif (!validator_max_length($queries['sort'], 5)) {
            $messages['sort'] = '並び順は5桁以内で入力してください。';
        }
    }

    return $messages;
}

/**
 * ウィジェットの初期値
 *
 * @return array
 */
function default_widgets()
{
    return [
        'id'       => null,
        'created'  => localdate('Y-m-d H:i:s'),
        'modified' => localdate('Y-m-d H:i:s'),
        'deleted'  => null,
        'title'    => '',
        'code'     => '',
        'text'     => null,
        'sort'     => 0,
    ];
}
