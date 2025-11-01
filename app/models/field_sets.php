<?php

/**
 * フィールド ひも付けの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_field_sets($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        // 関連するデータを取得
        if (!isset($queries['select'])) {
            $queries['select'] = 'field_sets.*, '
                               . 'fields.type_id AS field_type_id, '
                               . 'fields.name AS field_name, '
                               . 'fields.kind AS field_kind, '
                               . 'fields.validation AS field_validation, '
                               . 'fields.text AS field_text, '
                               . 'fields.sort AS field_sort';
        }

        $queries['from'] = DATABASE_PREFIX . 'field_sets AS field_sets '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'fields AS fields ON field_sets.field_id = fields.id';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'fields.deleted IS NULL AND (' . $queries['where'] . ')';
    } else {
        // ユーザーを取得
        $queries['from'] = DATABASE_PREFIX . 'field_sets';
    }

    // データを取得
    $results = db_select($queries);

    return $results;
}
