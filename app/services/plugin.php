<?php

import('app/services/log.php');

/**
 * プラグインの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_plugin_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'plugins', 'insert');

    // プラグインを登録
    $resource = model('insert_plugins', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
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
function service_plugin_update($queries, $options = [])
{
    $options = [
        'id'     => isset($options['id'])     ? $options['id']     : null,
        'update' => isset($options['update']) ? $options['update'] : null,
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $plugins = model('select_plugins', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($plugins)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'plugins', 'update');

    // プラグインを編集
    $resource = model('update_plugins', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
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
function service_plugin_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'plugins', 'delete');

    // プラグインを削除
    $resource = model('delete_plugins', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}
