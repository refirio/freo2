<?php

import('libs/plugins/validator.php');

/**
 * フィールドの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_fields($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        // 関連するデータを取得
        if (!isset($queries['select'])) {
            $queries['select'] = 'DISTINCT fields.*, '
                               . 'types.code AS type_code, '
                               . 'types.name AS type_name, '
                               . 'types.sort AS type_sort';
        }

        $queries['from'] = DATABASE_PREFIX . 'fields AS fields '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'types AS types ON fields.type_id = types.id';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'fields.deleted IS NULL AND (' . $queries['where'] . ')';
    } else {
        // 記事を取得
        $queries['from'] = DATABASE_PREFIX . 'fields';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'deleted IS NULL AND (' . $queries['where'] . ')';
    }
    //debug($queries);
    //exit;

    // データを取得
    $results = db_select($queries);

    return $results;
}

/**
 * フィールドの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_fields($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_fields');

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
    $queries['insert_into'] = DATABASE_PREFIX . 'fields';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * フィールドの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_fields($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_fields');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'fields';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * フィールドの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_fields($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
        'associate'  => isset($options['associate'])  ? $options['associate']  : false,
    ];

    // 削除するデータのIDを取得
    $fields = db_select([
        'select' => 'id',
        'from'   => DATABASE_PREFIX . 'fields AS fields',
        'where'  => isset($queries['where']) ? $queries['where'] : '',
        'limit'  => isset($queries['limit']) ? $queries['limit'] : '',
    ]);

    $ids = [];
    foreach ($fields as $field) {
        $ids[] = intval($field['id']);
    }

    if ($options['associate'] === true) {
/*
        // 関連するデータを削除
        $resource = model('delete_field_sets', [
            'where' => 'field_id IN(' . implode($ids) . ')',
        ]);
        if (!$resource) {
            return $resource;
        }
*/
    }

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'fields AS fields',
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
            'delete_from' => DATABASE_PREFIX . 'fields AS fields',
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
 * フィールドの正規化
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function normalize_fields($queries, $options = [])
{
    // 並び順
    if (isset($queries['sort'])) {
        $queries['sort'] = mb_convert_kana($queries['sort'], 'n', MAIN_INTERNAL_ENCODING);
    } else {
        if (!$queries['id']) {
            $fields = db_select([
                'select' => 'MAX(sort) AS sort',
                'from'   => DATABASE_PREFIX . 'fields',
            ]);
            $queries['sort'] = $fields[0]['sort'] + 1;
        }
    }

    return $queries;
}

/**
 * フィールドの検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_fields($queries, $options = [])
{
    $messages = [];

    // 外部キー 型
    if (isset($queries['type_id'])) {
        if (!validator_required($queries['type_id'])) {
            $messages['type_id'] = '型が入力されていません。';
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

    // 種類
    if (isset($queries['kind'])) {
        if (!validator_required($queries['kind'])) {
            $messages['kind'] = '種類が入力されていません。';
        } elseif (!validator_list($queries['kind'], $GLOBALS['config']['options']['field']['kinds'])) {
            $messages['kind'] = '種類の値が不正です。';
        }
    }

    // バリデーション
    if (isset($queries['validation'])) {
        if (!validator_required($queries['validation'])) {
        } elseif (!validator_list($queries['validation'], $GLOBALS['config']['options']['field']['validations'])) {
            $messages['validation'] = 'バリデーションの値が不正です。';
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
 * フィールドの初期値
 *
 * @return array
 */
function default_fields()
{
    return [
        'id'         => null,
        'created'    => localdate('Y-m-d H:i:s'),
        'modified'   => localdate('Y-m-d H:i:s'),
        'deleted'    => null,
        'type_id'    => 0,
        'name'       => '',
        'kind'       => '',
        'validation' => null,
        'text'       => null,
        'sort'       => 0,
    ];
}
