<?php

/**
 * カテゴリ ひも付けの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_category_sets($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        // 関連するデータを取得
        if (!isset($queries['select'])) {
            $queries['select'] = 'category_sets.*, '
                               . 'categories.name AS category_name, '
                               . 'categories.code AS category_code, '
                               . 'categories.sort AS category_sort';
        }

        $queries['from'] = DATABASE_PREFIX . 'category_sets AS category_sets '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'categories AS categories ON category_sets.category_id = categories.id';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'categories.deleted IS NULL AND (' . $queries['where'] . ')';
    } else {
        // ユーザを取得
        $queries['from'] = DATABASE_PREFIX . 'category_sets';
    }

    // データを取得
    $results = db_select($queries);

    return $results;
}
