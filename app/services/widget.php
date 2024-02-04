<?php

import('app/services/log.php');

/**
 * ウィジェットの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_widget_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'widgets', 'insert');

    // ウィジェットを登録
    $resource = model('insert_widgets', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
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
function service_widget_update($queries, $options = [])
{
    $options = [
        'id'     => isset($options['id'])     ? $options['id']     : null,
        'update' => isset($options['update']) ? $options['update'] : null,
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $widgets = model('select_widgets', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($widgets)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'widgets', 'update');

    // ウィジェットを編集
    $resource = model('update_widgets', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
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
function service_widget_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'widgets', 'delete');

    // ウィジェットを削除
    $resource = model('delete_widgets', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}
