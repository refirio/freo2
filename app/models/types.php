<?php

/**
 * 型の取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_types($queries, $options = [])
{
    $queries = db_placeholder($queries);

    // 型を取得
    $queries['from'] = DATABASE_PREFIX . 'types';

    // 削除済みデータは取得しない
    if (!isset($queries['where'])) {
        $queries['where'] = 'TRUE';
    }
    $queries['where'] = 'deleted IS NULL AND (' . $queries['where'] . ')';

    // データを取得
    $results = db_select($queries);

    return $results;
}
