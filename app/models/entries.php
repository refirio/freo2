<?php

import('app/services/storage.php');
import('libs/plugins/validator.php');

/**
 * エントリーの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_entries($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        // 関連するデータを取得
        if (!isset($queries['select'])) {
            $queries['select'] = 'DISTINCT entries.*, '
                               . 'types.code AS type_code, '
                               . 'types.name AS type_name, '
                               . 'types.sort AS type_sort';
        }

        $queries['from'] = DATABASE_PREFIX . 'entries AS entries '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'types AS types ON entries.type_id = types.id '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'field_sets AS field_sets ON entries.id = field_sets.entry_id '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'category_sets AS category_sets ON entries.id = category_sets.entry_id';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'entries.deleted IS NULL AND (' . $queries['where'] . ')';
    } else {
        // エントリーを取得
        $queries['from'] = DATABASE_PREFIX . 'entries';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'deleted IS NULL AND (' . $queries['where'] . ')';
    }

    // データを取得
    $results = db_select($queries);

    // 関連するデータを取得
    if ($options['associate'] === true) {
        $id_columns = array_column($results, 'id');

        if (!empty($id_columns)) {
            // フィールドを取得
            $field_sets = model('select_field_sets', [
                'where' => 'field_sets.entry_id IN(' . implode(',', array_map('db_escape', $id_columns)) . ')',
            ], [
                'associate' => true,
            ]);

            $fields = [];
            foreach ($field_sets as $field_set) {
                $fields[$field_set['entry_id']][$field_set['field_id']] = $field_set['text'];
            }

            // カテゴリを取得
            $category_sets = model('select_category_sets', [
                'where' => 'category_sets.entry_id IN(' . implode(',', array_map('db_escape', $id_columns)) . ')',
            ], [
                'associate' => true,
            ]);

            $categories = [];
            foreach ($category_sets as $category_set) {
                $categories[$category_set['entry_id']][] = $category_set;
            }

            // 関連するデータを結合
            for ($i = 0; $i < count($results); $i++) {
                if (!isset($fields[$results[$i]['id']])) {
                    $fields[$results[$i]['id']] = [];
                }
                if (!isset($categories[$results[$i]['id']])) {
                    $categories[$results[$i]['id']] = [];
                }
                $results[$i]['field_sets']    = $fields[$results[$i]['id']];
                $results[$i]['category_sets'] = $categories[$results[$i]['id']];
            }
        }
    }

    return $results;
}

/**
 * エントリーの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_entries($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'field_sets'    => isset($options['field_sets'])    ? $options['field_sets']    : [],
        'category_sets' => isset($options['category_sets']) ? $options['category_sets'] : [],
        'files'         => isset($options['files'])         ? $options['files']         : [],
    ];

    // 初期値を取得
    $defaults = model('default_entries');

    if (isset($queries['values']['created'])) {
        if ($queries['values']['created'] === false) {
            unset($queries['values']['created']);
        }
    } else {
        $queries['values']['created'] = $defaults['created'];
    }
    if (isset($queries['values']['modified'])) {
        if ($queries['values']['modified'] === false) {
            unset($queries['values']['modified']);
        }
    } else {
        $queries['values']['modified'] = $defaults['modified'];
    }

    // データを登録
    $queries['insert_into'] = DATABASE_PREFIX . 'entries';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    // IDを取得
    $entry_id = db_last_insert_id();

    if (isset($options['field_sets'])) {
        // 関連するフィールドを登録
        model('insert_field_entries', $entry_id, $options['field_sets']);
    }

    if (isset($options['category_sets'])) {
        // 関連するカテゴリを登録
        model('insert_category_entries', $entry_id, $options['category_sets']);
    }

    if (!empty($options['files'])) {
        // 関連するファイルを削除
        model('remove_file_entries', $entry_id, $options['files']);

        // 関連するファイルを保存
        model('save_file_entries', $entry_id, $options['files']);
    }

    return $resource;
}

/**
 * エントリーの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_entries($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'id'            => isset($options['id'])            ? $options['id']            : null,
        'field_sets'    => isset($options['field_sets'])    ? $options['field_sets']    : [],
        'category_sets' => isset($options['category_sets']) ? $options['category_sets'] : [],
        'files'         => isset($options['files'])         ? $options['files']         : [],
    ];

    // 初期値を取得
    $defaults = model('default_entries');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'entries';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    // IDを取得
    $id = $options['id'];

    if (isset($options['field_sets'])) {
        // 関連するフィールドを編集
        model('update_field_entries', $id, $options['field_sets']);
    }

    if (isset($options['category_sets'])) {
        // 関連するカテゴリを編集
        model('update_category_entries', $id, $options['category_sets']);
    }

    if (!empty($options['files'])) {
        // 関連するファイルを削除
        model('remove_file_entries', $id, $options['files']);

        // 関連するファイルを保存
        model('save_file_entries', $id, $options['files']);
    }

    return $resource;
}

/**
 * エントリーの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_entries($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
        'field'      => isset($options['field'])      ? $options['field']      : true,
        'category'   => isset($options['category'])   ? $options['category']   : true,
        'file'       => isset($options['file'])       ? $options['file']       : true,
    ];

    // 削除するデータのIDを取得
    $entries = db_select([
        'select' => 'id',
        'from'   => DATABASE_PREFIX . 'entries AS entries',
        'where'  => isset($queries['where']) ? $queries['where'] : '',
        'limit'  => isset($queries['limit']) ? $queries['limit'] : '',
    ]);

    $ids = [];
    foreach ($entries as $entry) {
        $ids[] = intval($entry['id']);
    }

    // 削除する関連データのIDを取得
    $fields = model('select_fields', [
        'select' => 'id',
        'where'  => 'kind = ' . db_escape('image') . ' OR kind = ' . db_escape('file'),
    ]);
    if (empty($fields)) {
        $field_ids = [0];
    } else {
        $field_ids = array_column($fields, 'id');
    }

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'entries AS entries',
            'set'    => [
                'deleted' => localdate('Y-m-d H:i:s'),
            ],
            'where'  => isset($queries['where']) ? $queries['where'] : '',
            'limit'  => isset($queries['limit']) ? $queries['limit'] : '',
        ]);
        if (!$resource) {
            return $resource;
        }
    } else {
        // データを削除
        $resource = db_delete([
            'delete_from' => DATABASE_PREFIX . 'entries AS entries',
            'where'       => isset($queries['where']) ? $queries['where'] : '',
            'limit'       => isset($queries['limit']) ? $queries['limit'] : '',
        ]);
        if (!$resource) {
            return $resource;
        }
    }

    if ($options['field'] === true) {
        // 関連するフィールドを削除
        $resource = model('delete_field_sets', [
            'where' => 'entry_id IN(' . implode(',', array_map('db_escape', $ids)) . ')',
        ]);
        if (!$resource) {
            return $resource;
        }
    }

    if ($options['category'] === true) {
        // 関連するカテゴリを削除
        $resource = model('delete_category_sets', [
            'where' => 'entry_id IN(' . implode(',', array_map('db_escape', $ids)) . ')',
        ]);
        if (!$resource) {
            return $resource;
        }
    }

    if ($options['file'] === true) {
        // 関連するファイルを削除
        foreach ($ids as $id) {
            service_storage_remove($GLOBALS['config']['file_targets']['entry'] . $id . '/');

            foreach ($field_ids as $field_id) {
                service_storage_remove($GLOBALS['config']['file_targets']['field'] . $id . '_' . $field_id . '/');
            }
        }
    }

    return $resource;
}

/**
 * エントリーの正規化
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function normalize_entries($queries, $options = [])
{
    // 公開開始日時
    if (isset($queries['public_begin'])) {
        if (!empty($queries['public_begin'])) {
            $queries['public_begin'] .= ':00';
        }
        $queries['public_begin'] = mb_convert_kana($queries['public_begin'], 'a', MAIN_INTERNAL_ENCODING);
    }

    // 公開終了日時
    if (isset($queries['public_end'])) {
        if (!empty($queries['public_end'])) {
            $queries['public_end'] .= ':00';
        }
        $queries['public_end'] = mb_convert_kana($queries['public_end'], 'a', MAIN_INTERNAL_ENCODING);
    }

    // 日時
    if (isset($queries['datetime'])) {
        if (!empty($queries['datetime'])) {
            $queries['datetime'] .= ':00';
        }
        $queries['datetime'] = mb_convert_kana($queries['datetime'], 'a', MAIN_INTERNAL_ENCODING);
    }

    // フィールド
    if (isset($queries['field_sets'])) {
        foreach ($queries['field_sets'] as $key => $value) {
            $queries['field_sets'][$key] = is_array($value) ? implode("\n", $value) : $value;
        }
    }

    return $queries;
}

/**
 * エントリーの検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_entries($queries, $options = [])
{
    $options = [
        'duplicate' => isset($options['duplicate']) ? $options['duplicate'] : true,
    ];

    $messages = [];

    // 外部キー 型
    if (isset($queries['type_id'])) {
        if (!validator_required($queries['type_id'])) {
            $messages['type_id'] = '型が入力されていません。';
        }
    }

    // 公開
    if (isset($queries['public'])) {
        if (!validator_boolean($queries['public'])) {
            $messages['public'] = '公開の書式が不正です。';
        }
    }

    // 公開開始日時
    if (isset($queries['public_begin'])) {
        if (!validator_required($queries['public_begin'])) {
        } elseif (!validator_datetime($queries['public_begin'])) {
            $messages['public_begin'] = '公開開始日時の値が不正です。';
        }
    }

    // 公開終了日時
    if (isset($queries['public_end'])) {
        if (!validator_required($queries['public_end'])) {
        } elseif (!validator_datetime($queries['public_end'])) {
            $messages['public_end'] = '公開終了日時の値が不正です。';
        }
    }

    // 日時
    if (isset($queries['datetime'])) {
        if (!validator_required($queries['datetime'])) {
            $messages['datetime'] = '日時が入力されていません。';
        } elseif (!validator_datetime($queries['datetime'])) {
            $messages['datetime'] = '日時の値が不正です。';
        }
    }

    // コード
    if (isset($queries['code'])) {
        if (!validator_required($queries['code'])) {
            $messages['code'] = 'コードが入力されていません。';
        } elseif (!validator_regexp($queries['code'], '^[\w\-\/]+$')) {
            $messages['code'] = 'コードは半角英数字で入力してください。';
        } elseif (!validator_between($queries['code'], 1, 80)) {
            $messages['code'] = 'コードは1文字以上80文字以内で入力してください。';
        } elseif ($options['duplicate'] === true) {
            if (empty($queries['id'])) {
                $entries = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'entries',
                    'where'  => [
                        'type_id = :type_id AND code = :code',
                        [
                            'type_id' => $queries['type_id'],
                            'code'    => $queries['code'],
                        ],
                    ],
                ]);
            } else {
                $entries = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'entries',
                    'where'  => [
                        'type_id = :type_id AND id != :id AND code = :code',
                        [
                            'type_id' => $queries['type_id'],
                            'id'      => $queries['id'],
                            'code'    => $queries['code'],
                        ],
                    ],
                ]);
            }
            if (!empty($entries)) {
                $messages['code'] = '入力されたコードはすでに使用されています。';
            }
        }
    }

    // タイトル
    if (isset($queries['title'])) {
        if (!validator_required($queries['title'])) {
            $messages['title'] = 'タイトルが入力されていません。';
        } elseif (!validator_max_length($queries['title'], 100)) {
            $messages['title'] = 'タイトルは100文字以内で入力してください。';
        }
    }

    // 本文
    if (isset($queries['text'])) {
        if (!validator_required($queries['text'])) {
        } elseif (!validator_max_length($queries['text'], 5000)) {
            $messages['text'] = '本文は5000文字以内で入力してください。';
        }
    }

    // フィールド
    if (isset($queries['field_sets'])) {
        // フィールドを取得
        $fields = model('select_fields', [
            'where'    => [
                'type_id = :type_id',
                [
                    'type_id' => $queries['type_id'],
                ],
            ],
            'order_by' => 'sort, id',
        ]);

        // フィールドを検証
        foreach ($fields as $field) {
            if (isset($queries['field_sets'][$field['id']])) {
                if ($field['validation'] === 'required' && !validator_required($queries['field_sets'][$field['id']])) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'が入力されていません。';
                } elseif ($field['kind'] === 'number' && $queries['field_sets'][$field['id']] !== '' && !validator_decimal($queries['field_sets'][$field['id']])) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'は数値で入力してください。';
                } elseif ($field['kind'] === 'alphabet' && $queries['field_sets'][$field['id']] !== '' && !validator_alpha_dash($queries['field_sets'][$field['id']])) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'は英数字で入力してください。';
                } elseif (($field['kind'] === 'text' || $field['kind'] === 'number' || $field['kind'] === 'alphabet') && !validator_max_length($queries['field_sets'][$field['id']], 100)) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'は100文字以内で入力してください。';
                } elseif (($field['kind'] === 'textarea') && !validator_max_length($queries['field_sets'][$field['id']], 2000)) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'は2000文字以内で入力してください。';
                } elseif (($field['kind'] === 'select' || $field['kind'] === 'radio' || $field['kind'] === 'checkbox') && $queries['field_sets'][$field['id']] && !validator_list(explode("\n", $queries['field_sets'][$field['id']]), array_fill_keys(explode("\n", $field['text']), 1))) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'の値が不正です。';
                }
            }
        }
    }

    return $messages;
}

/**
 * エントリーの絞り込み
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function filter_entries($queries, $options = [])
{
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        $wheres = [];
        $pagers = [];

        // フィールドを取得
        if (isset($queries['field_sets'])) {
            if (is_array($queries['field_sets'])) {
                $fields = [];
                foreach ($queries['field_sets'] as $field_set) {
                    $fields[] = 'field_sets.field_id = ' . db_escape($field_set);
                    $pagers[] = 'field_sets[]=' . rawurlencode($field_set);
                }
                $wheres[] = '(' . implode(' OR ', $fields) . ')';
            }
        }

        // カテゴリを取得
        if (isset($queries['category_sets'])) {
            if (is_array($queries['category_sets'])) {
                $categories = [];
                foreach ($queries['category_sets'] as $category_set) {
                    $categories[] = 'category_sets.category_id = ' . db_escape($category_set);
                    $pagers[]     = 'category_sets[]=' . rawurlencode($category_set);
                }
                $wheres[] = '(' . implode(' OR ', $categories) . ')';
            }
        }

        // 年月を取得
        if (isset($queries['archive'])) {
            if ($queries['archive'] !== '') {
                $wheres[] = 'DATE_FORMAT(entries.datetime, ' . db_escape('%Y-%m') . ') = ' . db_escape($queries['archive']);
                $pagers[] = 'archive=' . rawurlencode($queries['archive']);
            }
        }

        // 日時を取得
        if (isset($queries['datetime'])) {
            if ($queries['datetime'] !== '') {
                $wheres[] = 'entries.datetime = ' . db_escape($queries['datetime']);
                $pagers[] = 'datetime=' . rawurlencode($queries['datetime']);
            }
        }

        // 型を取得
        if (isset($queries['type_id'])) {
            if ($queries['type_id'] !== '') {
                $wheres[] = 'entries.type_id = ' . db_escape($queries['type_id']);
                $pagers[] = 'type_id=' . rawurlencode($queries['type_id']);
            }
        }

        // タイトルを取得
        if (isset($queries['title'])) {
            if ($queries['title'] !== '') {
                $wheres[] = 'entries.title = ' . db_escape($queries['title']);
                $pagers[] = 'title=' . rawurlencode($queries['title']);
            }
        }

        $results = [
            'where' => implode(' AND ', $wheres),
            'pager' => implode('&amp;', $pagers),
        ];
    } else {
        $results = [
            'where' => null,
            'pager' => null,
        ];
    }

    return $results;
}

/**
 * 関連するフィールドを登録
 *
 * @param int   $entry_id
 * @param array $field_sets
 *
 * @return void
 */
function insert_field_entries($entry_id, $field_sets)
{
    // フィールドを登録
    foreach ($field_sets as $field_id => $text) {
        if ($text === '') {
            continue;
        }
        if (is_array($text)) {
            $text = implode("\n", $text);
        }
        $resource = model('insert_field_sets', [
            'values' => [
                'field_id' => $field_id,
                'entry_id' => $entry_id,
                'text'     => $text,
            ],
        ]);
        if (!$resource) {
            return $resource;
        }
    }
}

/**
 * 関連するカテゴリを登録
 *
 * @param int   $entry_id
 * @param array $category_sets
 *
 * @return void
 */
function insert_category_entries($entry_id, $category_sets)
{
    // カテゴリを登録
    foreach ($category_sets as $category_id) {
        $resource = model('insert_category_sets', [
            'values' => [
                'category_id' => $category_id,
                'entry_id'    => $entry_id,
            ],
        ]);
        if (!$resource) {
            return $resource;
        }
    }
}

/**
 * 関連するフィールドを編集
 *
 * @param int   $entry_id
 * @param array $field_sets
 *
 * @return void
 */
function update_field_entries($entry_id, $field_sets)
{
    // フィールドを編集
    $fields = model('select_fields', [
        'select' => 'id',
        'where'  => 'kind != ' . db_escape('image') . ' AND kind != ' . db_escape('file'),
    ]);
    if (empty($fields)) {
        $field_ids = [0];
    } else {
        $field_ids = array_column($fields, 'id');
    }

    $resource = model('delete_field_sets', [
        'where' => [
            'field_id IN(' . implode(',', $field_ids) . ') AND entry_id = :id',
            [
                'id' => $entry_id,
            ],
        ],
    ]);
    if (!$resource) {
        return $resource;
    }

    foreach ($field_sets as $field_id => $text) {
        if ($text === '') {
            continue;
        }
        if (is_array($text)) {
            $text = implode("\n", $text);
        }
        $resource = model('insert_field_sets', [
            'values' => [
                'field_id' => $field_id,
                'entry_id' => $entry_id,
                'text'     => $text,
            ],
        ]);
        if (!$resource) {
            return $resource;
        }
    }
}

/**
 * 関連するカテゴリを編集
 *
 * @param int   $id
 * @param array $category_sets
 *
 * @return void
 */
function update_category_entries($entry_id, $category_sets)
{
    // カテゴリを編集
    $resource = model('delete_category_sets', [
        'where' => [
            'entry_id = :id',
            [
                'id' => $entry_id,
            ],
        ],
    ]);
    if (!$resource) {
        return $resource;
    }

    foreach ($category_sets as $category_id) {
        $resource = model('insert_category_sets', [
            'values' => [
                'category_id' => $category_id,
                'entry_id'    => $entry_id,
            ],
        ]);
        if (!$resource) {
            return $resource;
        }
    }
}

/**
 * ファイルの保存
 *
 * @param string $id
 * @param array  $files
 *
 * @return void
 */
function save_file_entries($id, $files)
{
    foreach (array_keys($files) as $file) {
        if (preg_match('/^field__(.*)$/', $file, $matches)) {
            $target = 'field';
            $field  = $matches[1];
            $key    = $id . '_' . $matches[1];
        } elseif (preg_match('/^field_(.*)_(.*)$/', $file, $matches)) {
            $target = 'field';
            $field  = $matches[2];
            $key    = $matches[1] . '_' . $matches[2];
        } else {
            $target = 'entry';
            $field  = null;
            $key    = intval($id);
        }
        if (empty($files[$file]['delete']) && !empty($files[$file]['name'])) {
            if (preg_match('/(.*)\.(.*)$/', $files[$file]['name'], $matches)) {
                $directory = $GLOBALS['config']['file_targets'][$target] . $key . '/';
                //$suffix    = $file === 'thumbnail' ? '_thumbnail' : '';
                $suffix    = '';
                $filename  = rawurlencode($matches[1]) . $suffix . '.' . $matches[2];

                service_storage_put($directory);

                if (service_storage_put($directory . $filename, $files[$file]['data']) === false) {
                    error('ファイル ' . $filename . ' を保存できません。');
                } else {
                    if ($target === 'field') {
                        $resource = model('insert_field_sets', [
                            'values' => [
                                'field_id' => $field,
                                'entry_id' => $id,
                                'text'     => $filename,
                            ],
                        ]);
                        if (!$resource) {
                            return $resource;
                        }
                    } else {
                        $resource = db_update([
                            'update' => DATABASE_PREFIX . 'entries',
                            'set'    => [
                                $file => $filename,
                            ],
                            'where'  => [
                                'id = :id',
                                [
                                    'id' => $id,
                                ],
                            ],
                        ]);
                        if (!$resource) {
                            error('データを編集できません。');
                        }
                    }

                    //file_resize($directory . $filename, $directory . 'thumbnail_' . $filename, $GLOBALS['config']['resize_width'], $GLOBALS['config']['resize_height'], $GLOBALS['config']['resize_quality']);
                }
            } else {
                error('ファイル ' . $files[$file]['name'] . ' の拡張子を取得できません。');
            }
        }
    }
}

/**
 * ファイルの削除
 *
 * @param int   $id
 * @param array $files
 *
 * @return void
 */
function remove_file_entries($id, $files)
{
    foreach (array_keys($files) as $file) {
        if (preg_match('/^field__(.*)$/', $file, $matches)) {
            $target = 'field';
            $field  = $matches[1];
            $key    = $id . '_' . $matches[1];
        } elseif (preg_match('/^field_(.*)_(.*)$/', $file, $matches)) {
            $target = 'field';
            $field  = $matches[2];
            $key    = $matches[1] . '_' . $matches[2];
        } else {
            $target = 'entry';
            $field  = null;
            $key    = intval($id);
        }
        if (!empty($files[$file]['delete']) || !empty($files[$file]['name'])) {
            if ($target === 'field') {
                $field_sets = model('select_field_sets', [
                    'select' => 'text',
                    'where'  => [
                        'field_id = :field_id AND entry_id = :entry_id',
                        [
                            'field_id' => $field,
                            'entry_id' => $id,
                        ],
                    ],
                ]);
                if (!empty($field_sets)) {
                    $field_set = $field_sets[0];

                    if (service_storage_exist($GLOBALS['config']['file_targets']['field'] . $key . '/' . $field_set['text'])) {
                        //if (is_file($GLOBALS['config']['file_targets']['field'] . intval($id) . '/thumbnail_' . $field_set['text'])) {
                        //    unlink($GLOBALS['config']['file_targets']['field'] . intval($id) . '/thumbnail_' . $field_set['text']);
                        //}
                        service_storage_remove($GLOBALS['config']['file_targets']['field'] . $key . '/' . $field_set['text']);

                        $resource = model('delete_field_sets', [
                            'where' => [
                                'field_id = :field_id AND entry_id = :entry_id',
                                [
                                    'field_id' => $field,
                                    'entry_id' => $id,
                                ],
                            ],
                        ]);
                        if (!$resource) {
                            return $resource;
                        }
                    }
                }
            } else {
                $entries = db_select([
                    'select' => $file,
                    'from'   => DATABASE_PREFIX . 'entries',
                    'where'  => [
                        'id = :id',
                        [
                            'id' => $id,
                        ],
                    ],
                ]);
                if (empty($entries)) {
                    error('編集データが見つかりません。');
                } else {
                    $entry = $entries[0];
                }

                if (service_storage_exist($GLOBALS['config']['file_targets']['entry'] . intval($id) . '/' . $entry[$file])) {
                    //if (is_file($GLOBALS['config']['file_targets']['entry'] . intval($id) . '/thumbnail_' . $entry[$file])) {
                    //    unlink($GLOBALS['config']['file_targets']['entry'] . intval($id) . '/thumbnail_' . $entry[$file]);
                    //}
                    service_storage_remove($GLOBALS['config']['file_targets']['entry'] . intval($id) . '/' . $entry[$file]);

                    $resource = db_update([
                        'update' => DATABASE_PREFIX . 'entries',
                        'set'    => [
                            $file => null,
                        ],
                        'where'  => [
                            'id = :id',
                            [
                                'id' => $id,
                            ],
                        ],
                    ]);
                    if (!$resource) {
                        error('データを編集できません。');
                    }
                }
            }
        }
    }
}

/**
 * エントリーの表示用データ作成
 *
 * @param array $data
 *
 * @return array
 */
function view_entries($data)
{
    // 公開開始日時
    if (isset($data['public_begin'])) {
        if (preg_match('/^(\d\d\d\d\-\d\d\-\d\d \d\d:\d\d):\d\d$/', $data['public_begin'], $matches)) {
            $data['public_begin'] = $matches[1];
        }
    }

    // 公開終了日時
    if (isset($data['public_end'])) {
        if (preg_match('/^(\d\d\d\d\-\d\d\-\d\d \d\d:\d\d):\d\d$/', $data['public_end'], $matches)) {
            $data['public_end'] = $matches[1];
        }
    }

    // 日時
    if (isset($data['datetime'])) {
        if (preg_match('/^(\d\d\d\d\-\d\d\-\d\d \d\d:\d\d):\d\d$/', $data['datetime'], $matches)) {
            $data['datetime'] = $matches[1];
        }
    }

    return $data;
}

/**
 * エントリーの初期値
 *
 * @return array
 */
function default_entries()
{
    return [
        'id'            => null,
        'created'       => localdate('Y-m-d H:i:s'),
        'modified'      => localdate('Y-m-d H:i:s'),
        'deleted'       => null,
        'type_id'       => 0,
        'public'        => 1,
        'public_begin'  => null,
        'public_end'    => null,
        'datetime'      => localdate('Y-m-d H:00'),
        'title'         => '',
        'code'          => '',
        'text'          => null,
        'picture'       => null,
        'thumbnail'     => null,
        'field_sets'    => [],
        'category_sets' => [],
    ];
}
