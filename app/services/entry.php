<?php

import('app/services/log.php');

/**
 * 公開済み記事の取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_entry_select_published($queries, $options = [])
{
    // 公開済み記事の絞り込み
    if (empty($queries['where'])) {
        $where1 = 'TRUE';
        $where2 = [];
    } elseif (!is_array($queries['where'])) {
        $where1 = $queries['where'];
        $where2 = [];
    } else {
        $where1 = $queries['where'][0];
        $where2 = $queries['where'][1];
    }
    $where1 .= ' AND entries.public = 1 AND (entries.public_begin IS NULL OR entries.public_begin <= :now) AND (entries.public_end IS NULL OR entries.public_end >= :now)';
    $where2['now'] = localdate('Y-m-d H:i:s');

    $queries['where'] = [$where1, $where2];

    // 記事を取得
    $entries = model('select_entries', $queries, [
        'associate' => true,
    ]);

    return $entries;
}
/**
 * 記事の登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_entry_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'entries', 'insert');

    // 記事を登録
    $resource = model('insert_entries', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
    }

    return $resource;
}

/**
 * 記事の編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_entry_update($queries, $options = [])
{
    $options = [
        'id'            => isset($options['id'])            ? $options['id']            : null,
        'field_sets'    => isset($options['field_sets'])    ? $options['field_sets']    : [],
        'category_sets' => isset($options['category_sets']) ? $options['category_sets'] : [],
        'files'         => isset($options['files'])         ? $options['files']         : [],
        'update'        => isset($options['update'])        ? $options['update']        : null,
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $entries = model('select_entries', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($entries)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'entries', 'update');

    // 記事を編集
    $resource = model('update_entries', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $resource;
}

/**
 * 記事の削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_entry_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'entries', 'delete');

    // 記事を削除
    $resource = model('delete_entries', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}
