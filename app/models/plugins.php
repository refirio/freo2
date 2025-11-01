<?php

import('libs/modules/validator.php');

/**
 * プラグインの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_plugins($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // プラグインを取得
    $queries['from'] = DATABASE_PREFIX . 'plugins';

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
 * プラグインの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_plugins($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_plugins');

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
    $queries['insert_into'] = DATABASE_PREFIX . 'plugins';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * プラグインの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_plugins($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_plugins');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'plugins';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * プラグインの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_plugins($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
    ];

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'plugins AS plugins',
            'set'    => [
                'deleted' => localdate('Y-m-d H:i:s'),
                'code'    => ['CONCAT(\'DELETED ' . localdate('YmdHis') . ' \', code)'],
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
            'delete_from' => DATABASE_PREFIX . 'plugins AS plugins',
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
 * プラグインの検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_plugins($queries, $options = [])
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
                $plugins = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'plugins',
                    'where'  => [
                        'deleted IS NULL AND code = :code',
                        [
                            'code' => $queries['code'],
                        ],
                    ],
                ]);
            } else {
                $plugins = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'plugins',
                    'where'  => [
                        'id != :id AND deleted IS NULL AND code = :code',
                        [
                            'id'   => $queries['id'],
                            'code' => $queries['code'],
                        ],
                    ],
                ]);
            }
            if (!empty($plugins)) {
                $messages['code'] = '入力されたコードはすでに使用されています。';
            }
        }
    }

    // バージョン
    if (isset($queries['version'])) {
        if (!validator_required($queries['version'])) {
            $messages['version'] = 'バージョンが入力されていません。';
        } elseif (!validator_regexp($queries['version'], '[\d\.]')) {
            $messages['version'] = 'バージョンは数値で入力してください。';
        } elseif (!validator_max_length($queries['version'], 20)) {
            $messages['version'] = 'バージョンは20文字以内で入力してください。';
        }
    }

    // 有効
    if (isset($queries['enabled'])) {
        if (!validator_boolean($queries['enabled'])) {
            $messages['enabled'] = '有効の書式が不正です。';
        }
    }

    return $messages;
}

/**
 * プラグインの初期値
 *
 * @return array
 */
function default_plugins()
{
    return [
        'id'       => null,
        'created'  => localdate('Y-m-d H:i:s'),
        'modified' => localdate('Y-m-d H:i:s'),
        'deleted'  => null,
        'code'     => '',
        'version'  => '',
        'enabled'  => 0,
    ];
}
