<?php

import('libs/modules/validator.php');

/**
 * 属性の取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_attributes($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 属性を取得
    $queries['from'] = DATABASE_PREFIX . 'attributes';

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
 * 属性の登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_attributes($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_attributes');

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
    $queries['insert_into'] = DATABASE_PREFIX . 'attributes';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * 属性の編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_attributes($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_attributes');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'attributes';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * 属性の削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_attributes($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
    ];

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'attributes AS attributes',
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
            'delete_from' => DATABASE_PREFIX . 'attributes AS attributes',
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
 * 属性の正規化
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function normalize_attributes($queries, $options = [])
{
    // 並び順
    if (isset($queries['sort'])) {
        $queries['sort'] = mb_convert_kana($queries['sort'], 'n', MAIN_INTERNAL_ENCODING);
    } else {
        if (!$queries['id']) {
            $attributes = db_select([
                'select' => 'MAX(sort) AS sort',
                'from'   => DATABASE_PREFIX . 'attributes',
            ]);
            $queries['sort'] = $attributes[0]['sort'] + 1;
        }
    }

    return $queries;
}

/**
 * 属性の検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_attributes($queries, $options = [])
{
    $options = [
        'duplicate' => isset($options['duplicate']) ? $options['duplicate'] : true,
    ];

    $messages = [];

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
 * 属性の初期値
 *
 * @return array
 */
function default_attributes()
{
    return [
        'id'       => null,
        'created'  => localdate('Y-m-d H:i:s'),
        'modified' => localdate('Y-m-d H:i:s'),
        'deleted'  => null,
        'name'     => '',
        'sort'     => 0,
    ];
}
