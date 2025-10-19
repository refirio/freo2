<?php

import('app/services/log.php');

/**
 * お問い合わせの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_contact_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'contacts', 'insert');

    // ユーザ
    if (!empty($_SESSION['auth']['user']['id'])) {
        $queries['values']['user_id'] = $_SESSION['auth']['user']['id'];
    }

    // お問い合わせを登録
    $resource = model('insert_contacts', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
    }

    return $resource;
}

/**
 * お問い合わせの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_contact_update($queries, $options = [])
{
    $options = [
        'id'     => isset($options['id'])     ? $options['id']     : null,
        'update' => isset($options['update']) ? $options['update'] : null,
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $contacts = model('select_contacts', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($contacts)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'contacts', 'update');

    // お問い合わせを編集
    $resource = model('update_contacts', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $resource;
}

/**
 * お問い合わせの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_contact_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'contacts', 'delete');

    // お問い合わせを削除
    $resource = model('delete_contacts', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}
