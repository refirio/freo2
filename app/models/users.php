<?php

import('libs/modules/validator.php');

/**
 * ユーザの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_users($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        // 関連するデータを取得
        if (!isset($queries['select'])) {
            $queries['select'] = 'DISTINCT users.*';
        }

        $queries['from'] = DATABASE_PREFIX . 'users AS users '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'attribute_sets AS attribute_sets ON users.id = attribute_sets.user_id';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'users.deleted IS NULL AND (' . $queries['where'] . ')';
    } else {
        // ユーザを取得
        $queries['from'] = DATABASE_PREFIX . 'users';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'deleted IS NULL AND (' . $queries['where'] . ')';
    }

    // データを取得
    $results = db_select($queries);

    // 関連するデータを取得
    if ($options['associate'] === true) {
        $id_columns = array_column($results, 'id');

        if (!empty($id_columns)) {
            // 属性を取得
            $attribute_sets = model('select_attribute_sets', [
                'where' => 'attribute_sets.user_id IN(' . implode(',', array_map('db_escape', $id_columns)) . ')',
            ], [
                'associate' => true,
            ]);

            $attributes = [];
            foreach ($attribute_sets as $attribute_set) {
                $attributes[$attribute_set['user_id']][] = $attribute_set;
            }

            // 関連するデータを結合
            for ($i = 0; $i < count($results); $i++) {
                if (!isset($attributes[$results[$i]['id']])) {
                    $attributes[$results[$i]['id']] = [];
                }
                $results[$i]['attribute_sets'] = $attributes[$results[$i]['id']];
            }
        }
    }

    return $results;
}

/**
 * ユーザの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_users($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'attribute_sets' => isset($options['attribute_sets']) ? $options['attribute_sets'] : [],
    ];

    // 初期値を取得
    $defaults = model('default_users');

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
    $queries['insert_into'] = DATABASE_PREFIX . 'users';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    // IDを取得
    $user_id = db_last_insert_id();

    if (isset($options['attribute_sets'])) {
        // 関連する属性を登録
        model('insert_attribute_users', $user_id, $options['attribute_sets']);
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
function update_users($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_users');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'users';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    // IDを取得
    $id = $options['id'];

    if (isset($options['attribute_sets'])) {
        // 関連する属性を編集
        model('update_attribute_users', $id, $options['attribute_sets']);
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
function delete_users($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
        'attribute'  => isset($options['attribute'])  ? $options['attribute']  : true,
    ];

    // 削除するデータのIDを取得
    $users = db_select([
        'select' => 'id',
        'from'   => DATABASE_PREFIX . 'users AS users',
        'where'  => isset($queries['where']) ? $queries['where'] : '',
        'limit'  => isset($queries['limit']) ? $queries['limit'] : '',
    ]);

    $ids = [];
    foreach ($users as $user) {
        $ids[] = intval($user['id']);
    }

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'users AS users',
            'set'    => [
                'deleted'  => localdate('Y-m-d H:i:s'),
                'username' => ['CONCAT(' . db_escape('DELETED ' . localdate('YmdHis') . ' ') . ', username)'],
                'email'    => ['CONCAT(' . db_escape('DELETED ' . localdate('YmdHis') . ' ') . ', email)'],
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
            'delete_from' => DATABASE_PREFIX . 'users AS users',
            'where'       => isset($queries['where']) ? $queries['where'] : '',
            'limit'       => isset($queries['limit']) ? $queries['limit'] : '',
        ]);
        if (!$resource) {
            return $resource;
        }
    }

    if ($options['attribute'] === true) {
        // 関連する属性を削除
        $resource = model('delete_attribute_sets', [
            'where' => 'user_id IN(' . implode(',', array_map('db_escape', $ids)) . ')',
        ]);
        if (!$resource) {
            return $resource;
        }
    }

    return $resource;
}

/**
 * ユーザの検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_users($queries, $options = [])
{
    $options = [
        'duplicate' => isset($options['duplicate']) ? $options['duplicate'] : true,
    ];

    $messages = [];

    // ユーザ名
    if (isset($queries['username'])) {
        if (!validator_required($queries['username'])) {
            $messages['username'] = 'ユーザ名が入力されていません。';
        } elseif (!validator_alpha_dash($queries['username'])) {
            $messages['username'] = 'ユーザ名は半角英数字で入力してください。';
        } elseif (!validator_between($queries['username'], 4, 20)) {
            $messages['username'] = 'ユーザ名は4文字以上20文字以内で入力してください。';
        } elseif ($options['duplicate'] === true) {
            if (empty($queries['id'])) {
                $users = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'users',
                    'where'  => [
                        'username = :username',
                        [
                            'username' => $queries['username'],
                        ],
                    ],
                ]);
            } else {
                $users = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'users',
                    'where'  => [
                        'id != :id AND username = :username',
                        [
                            'id'       => $queries['id'],
                            'username' => $queries['username'],
                        ],
                    ],
                ]);
            }
            if (!empty($users)) {
                $messages['username'] = '入力されたユーザ名はすでに使用されています。';
            }
        }
    }

    // パスワード
    if (isset($queries['password'])) {
        $flag = false;
        if (empty($queries['id'])) {
            if (!validator_required($queries['password'])) {
                $messages['password'] = 'パスワードが入力されていません。';
            } else {
                $flag = true;
            }
        } else {
            if ($queries['password'] !== '') {
                $flag = true;
            }
        }
        if ($flag === true) {
            if (!validator_regexp($queries['password'], '^[\w\!\"\#\$\%\&\'\(\)\*\+\,\-\.\/\:\;\<\=\>\?\@\[\\\\\]\^\_\`\{\|\}\~]+$')) {
                $messages['password'] = 'パスワードは半角英数字記号で入力してください。';
            } elseif (validator_regexp($queries['password'], '^([a-zA-Z]+|[0-9]+)$')) {
                $messages['password'] = 'パスワードは英数字を混在させてください。';
            } elseif (!validator_between($queries['password'], 8, 40)) {
                $messages['password'] = 'パスワードは8文字以上40文字以内で入力してください。';
            } elseif (isset($queries['username']) && validator_equals($queries['password'], $queries['username'])) {
                $messages['password'] = 'パスワードはユーザ名とは異なるものを入力してください。';
            } elseif (!validator_equals($queries['password'], $queries['password_confirm'])) {
                $messages['password'] = 'パスワードと確認パスワードが一致しません。';
            }
        }
    }

    // 外部キー 権限
    if (isset($queries['authority_id'])) {
        if (!validator_required($queries['authority_id'])) {
            $messages['authority_id'] = '権限が入力されていません。';
        }
    }

    // 名前
    if (isset($queries['name'])) {
        if (!validator_required($queries['name'])) {
        } elseif (!validator_max_length($queries['name'], 20)) {
            $messages['name'] = '名前は20文字以内で入力してください。';
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
        } elseif ($options['duplicate'] === true) {
            if (empty($queries['id'])) {
                $users = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'users',
                    'where'  => [
                        'email = :email',
                        [
                            'email' => $queries['email'],
                        ],
                    ],
                ]);
            } else {
                $users = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'users',
                    'where'  => [
                        'id != :id AND email = :email',
                        [
                            'id'    => $queries['id'],
                            'email' => $queries['email'],
                        ],
                    ],
                ]);
            }
            if (!empty($users)) {
                $messages['email'] = '入力されたメールアドレスはすでに使用されています。';
            }
        }
    }

    return $messages;
}

/**
 * 関連する属性を登録
 *
 * @param int   $user_id
 * @param array $attribute_sets
 *
 * @return void
 */
function insert_attribute_users($user_id, $attribute_sets)
{
    // 属性を登録
    foreach ($attribute_sets as $attribute_id) {
        $resource = model('insert_attribute_sets', [
            'values' => [
                'attribute_id' => $attribute_id,
                'user_id'      => $user_id,
            ],
        ]);
        if (!$resource) {
            error('データを登録できません。');
        }
    }
}

/**
 * 関連する属性を編集
 *
 * @param int   $id
 * @param array $attribute_sets
 *
 * @return void
 */
function update_attribute_users($user_id, $attribute_sets)
{
    // 属性を編集
    $resource = model('delete_attribute_sets', [
        'where' => [
            'user_id = :id',
            [
                'id' => $user_id,
            ],
        ],
    ]);
    if (!$resource) {
        error('データを削除できません。');
    }

    foreach ($attribute_sets as $attribute_id) {
        $resource = model('insert_attribute_sets', [
            'values' => [
                'attribute_id' => $attribute_id,
                'user_id'      => $user_id,
            ],
        ]);
        if (!$resource) {
            error('データを登録できません。');
        }
    }
}

/**
 * ユーザの初期値
 *
 * @return array
 */
function default_users()
{
    return [
        'id'             => null,
        'created'        => localdate('Y-m-d H:i:s'),
        'modified'       => localdate('Y-m-d H:i:s'),
        'deleted'        => null,
        'username'       => '',
        'password'       => '',
        'password_salt'  => '',
        'authority_id'   => 0,
        'name'           => null,
        'email'          => '',
        'loggedin'       => null,
        'failed'         => null,
        'failed_last'    => null,
        'attribute_sets' => [],
    ];
}
