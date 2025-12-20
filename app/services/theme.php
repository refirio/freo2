<?php

import('app/services/log.php');

/**
 * テーマの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_theme_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'themes', 'insert');

    // テーマを登録
    $resource = model('insert_themes', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
    }

    return $resource;
}

/**
 * テーマの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_theme_update($queries, $options = [])
{
    $options = [
        'id'     => isset($options['id'])     ? $options['id']     : null,
        'update' => isset($options['update']) ? $options['update'] : null,
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $themes = model('select_themes', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($themes)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'themes', 'update');

    // テーマを編集
    $resource = model('update_themes', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $resource;
}

/**
 * テーマの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_theme_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'themes', 'delete');

    // テーマを削除
    $resource = model('delete_themes', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}
