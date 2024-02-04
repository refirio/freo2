<?php

import('app/services/log.php');

/**
 * メニューの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_menu_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'menus', 'insert');

    // メニューを登録
    $resource = model('insert_menus', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
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
function service_menu_update($queries, $options = [])
{
    $options = [
        'id'     => isset($options['id'])     ? $options['id']     : null,
        'update' => isset($options['update']) ? $options['update'] : null,
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $menus = model('select_menus', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($menus)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'menus', 'update');

    // メニューを編集
    $resource = model('update_menus', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
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
function service_menu_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'menus', 'delete');

    // メニューを削除
    $resource = model('delete_menus', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}

/**
 * メニューの並び順を一括変更
 *
 * @param array $data
 *
 * @return void
 */
function service_menu_sort($data)
{
    // 並び順を更新
    foreach ($data as $id => $sort) {
        if (!preg_match('/^[\w\-\/]+$/', $id)) {
            continue;
        }
        if (!preg_match('/^\d+$/', $sort)) {
            continue;
        }

        $resource = service_menu_update([
            'set'   => [
                'sort' => $sort,
            ],
            'where' => [
                'id = :id',
                [
                    'id' => $id,
                ],
            ],
        ]);
        if (!$resource) {
            error('データを編集できません。');
        }
    }

    return;
}
