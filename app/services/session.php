<?php

/**
 * セッションの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_session_insert($queries, $options = [])
{
    // セッションを登録
    $resource = model('insert_sessions', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
    }

    return $resource;
}

/**
 * セッションの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_session_update($queries, $options = [])
{
    // セッションを編集
    $resource = model('update_sessions', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $resource;
}

/**
 * セッションの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_session_delete($queries, $options = [])
{
    // セッションを削除
    $resource = model('delete_sessions', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}
