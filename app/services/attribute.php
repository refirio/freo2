<?php

import('app/services/log.php');

/**
 * 属性の登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_attribute_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'attributes', 'insert');

    // 属性を登録
    $resource = model('insert_attributes', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
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
function service_attribute_update($queries, $options = [])
{
    $options = [
        'id'     => isset($options['id'])     ? $options['id']     : null,
        'update' => isset($options['update']) ? $options['update'] : null,
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $attributes = model('select_attributes', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($attributes)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'attributes', 'update');

    // 属性を編集
    $resource = model('update_attributes', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
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
function service_attribute_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'attributes', 'delete');

    // 属性を削除
    $resource = model('delete_attributes', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}

/**
 * 属性の並び順を一括変更
 *
 * @param array $data
 *
 * @return void
 */
function service_attribute_sort($data)
{
    // 並び順を更新
    foreach ($data as $id => $sort) {
        if (!preg_match('/^[\w\-\/]+$/', $id)) {
            continue;
        }
        if (!preg_match('/^\d+$/', $sort)) {
            continue;
        }

        $resource = service_attribute_update([
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
