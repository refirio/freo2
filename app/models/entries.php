<?php

import('app/services/storage.php');
import('libs/plugins/validator.php');

/**
 * 記事の取得
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
            $queries['select'] = 'DISTINCT entries.*';
        }

        $queries['from'] = DATABASE_PREFIX . 'entries AS entries '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'category_sets AS category_sets ON entries.id = category_sets.entry_id';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'entries.deleted IS NULL AND (' . $queries['where'] . ')';
    } else {
        // 記事を取得
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
                if (!isset($categories[$results[$i]['id']])) {
                    $categories[$results[$i]['id']] = [];
                }
                $results[$i]['category_sets'] = $categories[$results[$i]['id']];
            }
        }
    }

    return $results;
}

/**
 * 記事の登録
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

    if (isset($options['category_sets'])) {
        // カテゴリを登録
        foreach ($options['category_sets'] as $category_id) {
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

    if (!empty($options['files'])) {
        // 関連するファイルを削除
        model('remove_entries', $entry_id, $options['files']);

        // 関連するファイルを保存
        model('save_entries', $entry_id, $options['files']);
    }

    return $resource;
}

/**
 * 記事の編集
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

    if (isset($options['category_sets'])) {
        // カテゴリを編集
        $resource = model('delete_category_sets', [
            'where' => [
                'entry_id = :id',
                [
                    'id' => $id,
                ],
            ],
        ]);
        if (!$resource) {
            return $resource;
        }

        foreach ($options['category_sets'] as $category_id) {
            $resource = model('insert_category_sets', [
                'values' => [
                    'category_id' => $category_id,
                    'entry_id'    => $id,
                ],
            ]);
            if (!$resource) {
                return $resource;
            }
        }
    }

    if (!empty($options['files'])) {
        // 関連するファイルを削除
        model('remove_entries', $id, $options['files']);

        // 関連するファイルを保存
        model('save_entries', $id, $options['files']);
    }

    return $resource;
}

/**
 * 記事の削除
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
        'category'   => isset($options['category'])   ? $options['category']   : false,
        'file'       => isset($options['file'])       ? $options['file']       : false,
    ];

    // 削除するデータのIDを取得
    $entries = db_select([
        'select' => 'id',
        'from'   => DATABASE_PREFIX . 'entries AS entries',
        'where'  => isset($queries['where']) ? $queries['where'] : '',
        'limit'  => isset($queries['limit']) ? $queries['limit'] : '',
    ]);

    $deletes = [];
    foreach ($entries as $entry) {
        $deletes[] = intval($entry['id']);
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

    if ($options['category'] === true) {
        // 関連するカテゴリを削除
        $resource = model('delete_category_sets', [
            'where' => 'entry_id IN(' . implode(',', array_map('db_escape', $deletes)) . ')',
        ]);
        if (!$resource) {
            return $resource;
        }
    }

    if ($options['file'] === true) {
        // 関連するファイルを削除
        foreach ($deletes as $delete) {
            service_storage_remove($GLOBALS['config']['file_targets']['entry'] . $delete . '/');
        }
    }

    return $resource;
}

/**
 * 記事の正規化
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

    return $queries;
}

/**
 * 記事の検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_entries($queries, $options = [])
{
    $messages = [];

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

    return $messages;
}

/**
 * 記事の絞り込み
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
                $wheres[] = 'DATE_FORMAT(entries.datetime, \'%Y-%m\') = ' . db_escape($queries['archive']);
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
 * ファイルの保存
 *
 * @param string $id
 * @param array  $files
 *
 * @return void
 */
function save_entries($id, $files)
{
    foreach (array_keys($files) as $file) {
        if (empty($files[$file]['delete']) && !empty($files[$file]['name'])) {
            if (preg_match('/\.(.*)$/', $files[$file]['name'], $matches)) {
                $directory = $GLOBALS['config']['file_targets']['entry'] . intval($id) . '/';
                $filename  = $file . '.' . $matches[1];

                service_storage_put($directory);

                if (service_storage_put($directory . $filename, $files[$file]['data']) === false) {
                    error('ファイル ' . $filename . ' を保存できません。');
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
 * @param string $id
 * @param array  $files
 *
 * @return void
 */
function remove_entries($id, $files)
{
    foreach (array_keys($files) as $file) {
        if (!empty($files[$file]['delete']) || !empty($files[$file]['name'])) {
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

/**
 * 記事の表示用データ作成
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
 * 記事の初期値
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
        'public'        => 1,
        'public_begin'  => null,
        'public_end'    => null,
        'datetime'      => localdate('Y-m-d H:00'),
        'title'         => '',
        'text'          => null,
        'picture'       => null,
        'thumbnail'     => null,
        'category_sets' => [],
    ];
}
