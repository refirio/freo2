<?php

import('libs/modules/validator.php');

/**
 * カテゴリの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_categories($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        // 関連するデータを取得
        if (!isset($queries['select'])) {
            $queries['select'] = 'DISTINCT categories.*, '
                               . 'types.code AS type_code, '
                               . 'types.name AS type_name, '
                               . 'types.sort AS type_sort';
        }

        $queries['from'] = DATABASE_PREFIX . 'categories AS categories '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'types AS types ON categories.type_id = types.id';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'categories.deleted IS NULL AND (' . $queries['where'] . ')';
    } else {
        // 関連するデータを取得
        $queries = db_placeholder($queries);

        // カテゴリを取得
        $queries['from'] = DATABASE_PREFIX . 'categories';

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
 * カテゴリの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_categories($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_categories');

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
    $queries['insert_into'] = DATABASE_PREFIX . 'categories';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * カテゴリの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_categories($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_categories');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'categories';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * カテゴリの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_categories($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
        'associate'  => isset($options['associate'])  ? $options['associate']  : false,
    ];

    // 削除するデータのIDを取得
    $categories = db_select([
        'select' => 'id',
        'from'   => DATABASE_PREFIX . 'categories AS categories',
        'where'  => isset($queries['where']) ? $queries['where'] : '',
        'limit'  => isset($queries['limit']) ? $queries['limit'] : '',
    ]);

    $ids = [];
    foreach ($categories as $category) {
        $ids[] = intval($category['id']);
    }

    if ($options['associate'] === true) {
        // 関連するデータを削除
        $resource = model('delete_category_sets', [
            'where' => 'category_id IN(' . implode($ids) . ')',
        ]);
        if (!$resource) {
            return $resource;
        }
    }

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'categories AS categories',
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
            'delete_from' => DATABASE_PREFIX . 'categories AS categories',
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
 * カテゴリの正規化
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function normalize_categories($queries, $options = [])
{
    // 並び順
    if (isset($queries['sort'])) {
        $queries['sort'] = mb_convert_kana($queries['sort'], 'n', MAIN_INTERNAL_ENCODING);
    } else {
        if (!$queries['id']) {
            $categories = db_select([
                'select' => 'MAX(sort) AS sort',
                'from'   => DATABASE_PREFIX . 'categories',
            ]);
            $queries['sort'] = $categories[0]['sort'] + 1;
        }
    }

    return $queries;
}

/**
 * カテゴリの検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_categories($queries, $options = [])
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
                $categories = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'categories',
                    'where'  => [
                        'code = :code',
                        [
                            'code' => $queries['code'],
                        ],
                    ],
                ]);
            } else {
                $categories = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'categories',
                    'where'  => [
                        'id != :id AND code = :code',
                        [
                            'id'   => $queries['id'],
                            'code' => $queries['code'],
                        ],
                    ],
                ]);
            }
            if (!empty($categories)) {
                $messages['code'] = '入力されたコードはすでに使用されています。';
            }
        }
    }

    // 名前
    if (isset($queries['name'])) {
        if (!validator_required($queries['name'])) {
            $messages['name'] = '名前が入力されていません。';
        } elseif (!validator_max_length($queries['name'], 20)) {
            $messages['name'] = '名前は20文字以内で入力してください。';
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
 * カテゴリの初期値
 *
 * @return array
 */
function default_categories()
{
    return [
        'id'       => null,
        'created'  => localdate('Y-m-d H:i:s'),
        'modified' => localdate('Y-m-d H:i:s'),
        'deleted'  => null,
        'code'     => '',
        'name'     => '',
        'sort'     => 0,
    ];
}
