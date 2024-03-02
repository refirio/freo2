<?php

import('app/services/log.php');

/**
 * 公開済みページの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_page_select_published($queries, $options = [])
{
    // 公開済みページの絞り込み
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
    $where1 .= ' AND pages.public = 1 AND (pages.public_begin IS NULL OR pages.public_begin <= :now) AND (pages.public_end IS NULL OR pages.public_end >= :now)';
    $where2['now'] = localdate('Y-m-d H:i:s');

    $queries['where'] = [$where1, $where2];

    // ページを取得
    $pages = model('select_pages', $queries, [
        'associate' => true,
    ]);

    return $pages;
}
/**
 * ページの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_page_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'pages', 'insert');

    // ページを登録
    $resource = model('insert_pages', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
    }

    return $resource;
}

/**
 * ページの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_page_update($queries, $options = [])
{
    $options = [
        'id'         => isset($options['id'])         ? $options['id']         : null,
        'field_sets' => isset($options['field_sets']) ? $options['field_sets'] : [],
        'files'      => isset($options['files'])      ? $options['files']      : [],
        'update'     => isset($options['update'])     ? $options['update']     : null,
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $pages = model('select_pages', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($pages)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'pages', 'update');

    // ページを編集
    $resource = model('update_pages', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $resource;
}

/**
 * ページの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_page_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'pages', 'delete');

    // ページを削除
    $resource = model('delete_pages', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}
