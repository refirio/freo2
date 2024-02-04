<?php

/**
 * 操作ログの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_log_insert($queries, $options = [])
{
    // 操作ログを登録
    $resource = model('insert_logs', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
    }

    return $resource;
}

/**
 * 操作ログの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_log_update($queries, $options = [])
{
    // 操作ログを編集
    $resource = model('update_logs', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $resource;
}

/**
 * 操作ログの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_log_delete($queries, $options = [])
{
    // 操作ログを削除
    $resource = model('delete_logs', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}

/**
 * 操作ログの記録
 *
 * @param string $model
 * @param string $exec
 *
 * @return bool
 */
function service_log_record($message = null, $model = null, $exec = null)
{
    global $_params;

    static $recorded = [
        'model' => [],
        'exec'  => [],
    ];

    if (isset($recorded['model'][$model]) && isset($recorded['exec'][$exec])) {
        return;
    } else {
        $recorded['model'][$model] = true;
        $recorded['exec'][$exec]   = true;
    }

    // ユーザ
    if (empty($_SESSION['auth']['user']['id'])) {
        $user_id = null;
    } else {
        $user_id = $_SESSION['auth']['user']['id'];
    }

    // IPアドレス
    $ip = clientip($GLOBALS['config']['proxy']);

    // ユーザエージェント
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $agent = null;
    } else {
        $agent = $_SERVER['HTTP_USER_AGENT'];
    }

    // ページ
    $page = '/' . implode('/', $_params);
    if (!empty($_SERVER['QUERY_STRING'])) {
        $page .= '?' . $_SERVER['QUERY_STRING'];
    }

    // 操作ログを登録
    $resource = service_log_insert([
        'values' => [
            'user_id' => $user_id,
            'ip'      => $ip,
            'agent'   => $agent,
            'page'    => $page,
            'message' => $message,
            'model'   => $model,
            'exec'    => $exec,
        ],
    ]);
    if (!$resource) {
        error('データを登録できません。');
    }

    return;
}
