<?php

import('app/services/log.php');

/**
 * コメントの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_comment_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'comments', 'insert');

    // ユーザー
    if (!empty($_SESSION['auth']['user']['id'])) {
        $queries['values']['user_id'] = $_SESSION['auth']['user']['id'];
    }

    // コメントを登録
    $resource = model('insert_comments', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
    }

    return $resource;
}

/**
 * コメントの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_comment_update($queries, $options = [])
{
    $options = [
        'id'     => isset($options['id'])     ? $options['id']     : null,
        'update' => isset($options['update']) ? $options['update'] : null,
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $comments = model('select_comments', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($comments)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'comments', 'update');

    // コメントを編集
    $resource = model('update_comments', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $resource;
}

/**
 * コメントの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_comment_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'comments', 'delete');

    // コメントを削除
    $resource = model('delete_comments', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}
