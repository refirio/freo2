<?php

import('libs/modules/validator.php');

/**
 * メニューの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_menus($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // メニューを取得
    $queries['from'] = DATABASE_PREFIX . 'menus';

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
 * メニューの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_menus($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_menus');

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
    $queries['insert_into'] = DATABASE_PREFIX . 'menus';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * メニューの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_menus($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 初期値を取得
    $defaults = model('default_menus');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'menus';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    return $resource;
}

/**
 * メニューの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_menus($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
    ];

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'menus AS menus',
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
            'delete_from' => DATABASE_PREFIX . 'menus AS menus',
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
 * メニューの正規化
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function normalize_menus($queries, $options = [])
{
    // 並び順
    if (isset($queries['sort'])) {
        $queries['sort'] = mb_convert_kana($queries['sort'], 'n', MAIN_INTERNAL_ENCODING);
    } else {
        if (!$queries['id']) {
            $menus = db_select([
                'select' => 'MAX(sort) AS sort',
                'from'   => DATABASE_PREFIX . 'menus',
            ]);
            $queries['sort'] = $menus[0]['sort'] + 1;
        }
    }

    return $queries;
}

/**
 * メニューの検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_menus($queries, $options = [])
{
    $options = [
        'duplicate' => isset($options['duplicate']) ? $options['duplicate'] : true,
    ];

    $messages = [];

    // タイトル
    if (isset($queries['title'])) {
        if (!validator_required($queries['title'])) {
            $messages['title'] = 'タイトルが入力されていません。';
        } elseif (!validator_max_length($queries['title'], 20)) {
            $messages['title'] = 'タイトルは20文字以内で入力してください。';
        }
    }

    // URL
    if (isset($queries['url'])) {
        if (!validator_required($queries['url'])) {
            $messages['url'] = 'URLが入力されていません。';
        } elseif (!validator_max_length($queries['url'], 200)) {
            $messages['url'] = 'URLは200文字以内で入力してください。';
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
 * メニューの初期値
 *
 * @return array
 */
function default_menus()
{
    return [
        'id'       => null,
        'created'  => localdate('Y-m-d H:i:s'),
        'modified' => localdate('Y-m-d H:i:s'),
        'deleted'  => null,
        'title'    => '',
        'url'      => '',
        'sort'     => 0,
    ];
}
