<?php

/**
 * 属性 ひも付けの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_attribute_sets($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        // 関連するデータを取得
        if (!isset($queries['select'])) {
            $queries['select'] = 'attribute_sets.*, '
                               . 'attributes.name AS attribute_name, '
                               . 'attributes.sort AS attribute_sort';
        }

        $queries['from'] = DATABASE_PREFIX . 'attribute_sets AS attribute_sets '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'attributes AS attributes ON attribute_sets.attribute_id = attributes.id';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'attributes.deleted IS NULL AND (' . $queries['where'] . ')';
    } else {
        // ユーザを取得
        $queries['from'] = DATABASE_PREFIX . 'attribute_sets';
    }

    // データを取得
    $results = db_select($queries);

    return $results;
}
