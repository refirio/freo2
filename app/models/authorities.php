<?php

import('libs/modules/validator.php');

/**
 * 権限の取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_authorities($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 権限を取得
    $queries['from'] = DATABASE_PREFIX . 'authorities';

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
 * 権限の登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_authorities($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_authorities');

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
    $queries['insert_into'] = DATABASE_PREFIX . 'authorities';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * 権限の編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_authorities($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_authorities');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'authorities';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * 権限の削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_authorities($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
    ];

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'authorities AS authorities',
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
            'delete_from' => DATABASE_PREFIX . 'authorities AS authorities',
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
 * 権限の正規化
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function normalize_authorities($queries, $options = [])
{
    // 並び順
    if (isset($queries['power'])) {
        $queries['power'] = mb_convert_kana($queries['power'], 'n', MAIN_INTERNAL_ENCODING);
    } else {
        if (!$queries['id']) {
            $authorities = db_select([
                'select'   => 'id',
                'from'     => DATABASE_PREFIX . 'authorities',
                'order_by' => 'power, id',
            ]);
            $queries['power'] = $authorities[0]['id'];
        }
    }

    return $queries;
}

/**
 * 権限の検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_authorities($queries, $options = [])
{
    $messages = [];

    // 名前
    if (isset($queries['name'])) {
        if (!validator_required($queries['name'])) {
            $messages['name'] = '名前が入力されていません。';
        } elseif (!validator_max_length($queries['name'], 20)) {
            $messages['name'] = '名前は20文字以内で入力してください。';
        }
    }

    // 権力
    if (isset($queries['power'])) {
        if (!validator_required($queries['power'])) {
            $messages['power'] = '権力が入力されていません。';
        } elseif (!validator_numeric($queries['power'])) {
            $messages['power'] = '権力は半角数字で入力してください。';
        } elseif (!validator_max_length($queries['power'], 1)) {
            $messages['power'] = '権力は1桁以内で入力してください。';
        }
    }

    return $messages;
}

/**
 * 権限の初期値
 *
 * @return array
 */
function default_authorities()
{
    return [
        'id'       => null,
        'created'  => localdate('Y-m-d H:i:s'),
        'modified' => localdate('Y-m-d H:i:s'),
        'deleted'  => null,
        'name'     => '',
        'power'    => 0,
    ];
}
