<?php

import('app/services/storage.php');
import('libs/plugins/validator.php');

/**
 * ページの取得
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function select_pages($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'associate' => isset($options['associate']) ? $options['associate'] : false,
    ];

    if ($options['associate'] === true) {
        // 関連するデータを取得
        if (!isset($queries['select'])) {
            $queries['select'] = 'DISTINCT pages.*';
        }

        $queries['from'] = DATABASE_PREFIX . 'pages AS pages '
                         . 'LEFT JOIN ' . DATABASE_PREFIX . 'field_sets AS field_sets ON pages.id = field_sets.page_id';

        // 削除済みデータは取得しない
        if (!isset($queries['where'])) {
            $queries['where'] = 'TRUE';
        }
        $queries['where'] = 'pages.deleted IS NULL AND (' . $queries['where'] . ')';
    } else {
        // ページを取得
        $queries['from'] = DATABASE_PREFIX . 'pages';

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
                'where' => 'field_sets.page_id IN(' . implode(',', array_map('db_escape', $id_columns)) . ')',
            ], [
                'associate' => true,
            ]);

            $fields = [];
            foreach ($field_sets as $field_set) {
                $fields[$field_set['page_id']][$field_set['field_id']] = $field_set['text'];
            }

            // 関連するデータを結合
            for ($i = 0; $i < count($results); $i++) {
                if (!isset($fields[$results[$i]['id']])) {
                    $fields[$results[$i]['id']] = [];
                }
                $results[$i]['field_sets'] = $fields[$results[$i]['id']];
            }
        }
    }

    return $results;
}

/**
 * ページの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function insert_pages($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'field_sets' => isset($options['field_sets']) ? $options['field_sets'] : [],
        'files'      => isset($options['files'])      ? $options['files']      : [],
    ];

    // 初期値を取得
    $defaults = model('default_pages');

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
    $queries['insert_into'] = DATABASE_PREFIX . 'pages';

    $resource = db_insert($queries);
    if (!$resource) {
        return $resource;
    }

    // IDを取得
    $page_id = db_last_insert_id();

    if (isset($options['field_sets'])) {
        // フィールドを登録
        foreach ($options['field_sets'] as $field_id => $text) {
            if ($text === '') {
                continue;
            }
            if (is_array($text)) {
                $text = implode("\n", $text);
            }
            $resource = model('insert_field_sets', [
                'values' => [
                    'field_id' => $field_id,
                    'page_id'  => $page_id,
                    'text'     => $text,
                ],
            ]);
            if (!$resource) {
                return $resource;
            }
        }
    }

    if (!empty($options['files'])) {
        // 関連するファイルを削除
        model('remove_pages', $page_id, $options['files']);

        // 関連するファイルを保存
        model('save_pages', $page_id, $options['files']);
    }

    return $resource;
}

/**
 * ページの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function update_pages($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'id'         => isset($options['id'])         ? $options['id']         : null,
        'field_sets' => isset($options['field_sets']) ? $options['field_sets'] : [],
        'files'      => isset($options['files'])      ? $options['files']      : [],
    ];

    // 初期値を取得
    $defaults = model('default_pages');

    if (isset($queries['set']['modified'])) {
        if ($queries['set']['modified'] === false) {
            unset($queries['set']['modified']);
        }
    } else {
        $queries['set']['modified'] = $defaults['modified'];
    }

    // データを編集
    $queries['update'] = DATABASE_PREFIX . 'pages';

    $resource = db_update($queries);
    if (!$resource) {
        return $resource;
    }

    // IDを取得
    $id = $options['id'];

    if (isset($options['field_sets'])) {
        // フィールドを編集
        $fields = model('select_fields', [
            'select' => 'id',
            'where'  => 'type != \'image\' AND type != \'file\' && target = \'page\'',
        ]);
        if (empty($fields)) {
            $field_ids = [0];
        } else {
            $field_ids = array_column($fields, 'id');
        }

        $resource = model('delete_field_sets', [
            'where' => [
                'field_id IN(' . implode(',', $field_ids) . ') AND page_id = :id',
                [
                    'id' => $id,
                ],
            ],
        ]);
        if (!$resource) {
            return $resource;
        }

        foreach ($options['field_sets'] as $field_id => $text) {
            if ($text === '') {
                continue;
            }
            if (is_array($text)) {
                $text = implode("\n", $text);
            }
            $resource = model('insert_field_sets', [
                'values' => [
                    'field_id' => $field_id,
                    'page_id'  => $id,
                    'text'     => $text,
                ],
            ]);
            if (!$resource) {
                return $resource;
            }
        }
    }

    if (!empty($options['files'])) {
        // 関連するファイルを削除
        model('remove_pages', $id, $options['files']);

        // 関連するファイルを保存
        model('save_pages', $id, $options['files']);
    }

    return $resource;
}

/**
 * ページの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function delete_pages($queries, $options = [])
{
    $queries = db_placeholder($queries);
    $options = [
        'softdelete' => isset($options['softdelete']) ? $options['softdelete'] : true,
        'field'      => isset($options['field'])      ? $options['field']      : false,
        'file'       => isset($options['file'])       ? $options['file']       : false,
    ];

    // 削除するデータのIDを取得
    $pages = db_select([
        'select' => 'id',
        'from'   => DATABASE_PREFIX . 'pages AS pages',
        'where'  => isset($queries['where']) ? $queries['where'] : '',
        'limit'  => isset($queries['limit']) ? $queries['limit'] : '',
    ]);

    $deletes = [];
    foreach ($pages as $page) {
        $deletes[] = intval($page['id']);
    }

    if ($options['softdelete'] === true) {
        // データを編集
        $resource = db_update([
            'update' => DATABASE_PREFIX . 'pages AS pages',
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
            'delete_from' => DATABASE_PREFIX . 'pages AS pages',
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
            'where' => 'page_id IN(' . implode(',', array_map('db_escape', $deletes)) . ')',
        ]);
        if (!$resource) {
            return $resource;
        }
    }

    if ($options['file'] === true) {
        // 関連するファイルを削除
        foreach ($deletes as $delete) {
            service_storage_remove($GLOBALS['config']['file_targets']['page'] . $delete . '/');
        }
    }

    return $resource;
}

/**
 * ページの正規化
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function normalize_pages($queries, $options = [])
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
 * ページの検証
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function validate_pages($queries, $options = [])
{
    $options = [
        'duplicate' => isset($options['duplicate']) ? $options['duplicate'] : true,
    ];

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

    // コード
    if (isset($queries['code'])) {
        if (!validator_required($queries['code'])) {
            $messages['code'] = 'コードが入力されていません。';
        } elseif (!validator_regexp($queries['code'], '^[\w\-\/]+$')) {
            $messages['code'] = 'コードは半角英数字で入力してください。';
        } elseif (!validator_between($queries['code'], 2, 80)) {
            $messages['code'] = 'コードは2文字以上80文字以内で入力してください。';
        } elseif ($options['duplicate'] === true) {
            if (empty($queries['id'])) {
                $users = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'pages',
                    'where'  => [
                        'code = :code',
                        [
                            'code' => $queries['code'],
                        ],
                    ],
                ]);
            } else {
                $users = db_select([
                    'select' => 'id',
                    'from'   => DATABASE_PREFIX . 'pages',
                    'where'  => [
                        'id != :id AND code = :code',
                        [
                            'id'   => $queries['id'],
                            'code' => $queries['code'],
                        ],
                    ],
                ]);
            }
            if (!empty($users)) {
                $messages['code'] = '入力されたコードはすでに使用されています。';
            }
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
                'target = :target',
                [
                    'target' => 'page',
                ],
            ],
            'order_by' => 'sort, id',
        ]);

        // フィールドを検証
        foreach ($fields as $field) {
            if (isset($queries['field_sets'][$field['id']])) {
                if ($field['validation'] === 'required' && !validator_required($queries['field_sets'][$field['id']])) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'が入力されていません。';
                } elseif ($field['type'] === 'number' && $queries['field_sets'][$field['id']] !== '' && !validator_decimal($queries['field_sets'][$field['id']])) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'は数値で入力してください。';
                } elseif ($field['type'] === 'alphabet' && $queries['field_sets'][$field['id']] !== '' && !validator_alpha_dash($queries['field_sets'][$field['id']])) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'は英数字で入力してください。';
                } elseif (($field['type'] === 'text' || $field['type'] === 'number' || $field['type'] === 'alphabet') && !validator_max_length($queries['field_sets'][$field['id']], 100)) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'は100文字以内で入力してください。';
                } elseif (($field['type'] === 'textarea') && !validator_max_length($queries['field_sets'][$field['id']], 2000)) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'は2000文字以内で入力してください。';
                } elseif (($field['type'] === 'select' || $field['type'] === 'radio' || $field['type'] === 'checkbox') && $queries['field_sets'][$field['id']] && !validator_list(explode("\n", $queries['field_sets'][$field['id']]), array_fill_keys(explode("\n", $field['text']), 1))) {
                    $messages['field_sets_' . $field['id']] = $field['name'] . 'の値が不正です。';
                }
            }
        }
    }

    return $messages;
}

/**
 * ページの絞り込み
 *
 * @param array $queries
 * @param array $options
 *
 * @return array
 */
function filter_pages($queries, $options = [])
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

        // 年月を取得
        if (isset($queries['archive'])) {
            if ($queries['archive'] !== '') {
                $wheres[] = 'DATE_FORMAT(pages.datetime, \'%Y-%m\') = ' . db_escape($queries['archive']);
                $pagers[] = 'archive=' . rawurlencode($queries['archive']);
            }
        }

        // 日時を取得
        if (isset($queries['datetime'])) {
            if ($queries['datetime'] !== '') {
                $wheres[] = 'pages.datetime = ' . db_escape($queries['datetime']);
                $pagers[] = 'datetime=' . rawurlencode($queries['datetime']);
            }
        }

        // タイトルを取得
        if (isset($queries['title'])) {
            if ($queries['title'] !== '') {
                $wheres[] = 'pages.title = ' . db_escape($queries['title']);
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
function save_pages($id, $files)
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
            $target = 'page';
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
                                'page_id'  => $id,
                                'text'     => $filename,
                            ],
                        ]);
                        if (!$resource) {
                            return $resource;
                        }
                    } else {
                        $resource = db_update([
                            'update' => DATABASE_PREFIX . 'pages',
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
 * @param string $id
 * @param array  $files
 *
 * @return void
 */
function remove_pages($id, $files)
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
            $target = 'page';
            $field  = null;
            $key    = intval($id);
        }
        if (!empty($files[$file]['delete']) || !empty($files[$file]['name'])) {
            if ($target === 'field') {
                $field_sets = model('select_field_sets', [
                    'select' => 'text',
                    'where'  => [
                        'field_id = :field_id AND page_id = :page_id',
                        [
                            'field_id' => $field,
                            'page_id'  => $id,
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
                                'field_id = :field_id AND page_id = :page_id',
                                [
                                    'field_id' => $field,
                                    'page_id'  => $id,
                                ],
                            ],
                        ]);
                        if (!$resource) {
                            return $resource;
                        }
                    }
                }
            } else {
                $pages = db_select([
                    'select' => $file,
                    'from'   => DATABASE_PREFIX . 'pages',
                    'where'  => [
                        'id = :id',
                        [
                            'id' => $id,
                        ],
                    ],
                ]);
                if (empty($pages)) {
                    error('編集データが見つかりません。');
                } else {
                    $page = $pages[0];
                }

                if (service_storage_exist($GLOBALS['config']['file_targets']['page'] . intval($id) . '/' . $page[$file])) {
                    //if (is_file($GLOBALS['config']['file_targets']['page'] . intval($id) . '/thumbnail_' . $page[$file])) {
                    //    unlink($GLOBALS['config']['file_targets']['page'] . intval($id) . '/thumbnail_' . $page[$file]);
                    //}
                    service_storage_remove($GLOBALS['config']['file_targets']['page'] . intval($id) . '/' . $page[$file]);

                    $resource = db_update([
                        'update' => DATABASE_PREFIX . 'pages',
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
 * ページの表示用データ作成
 *
 * @param array $data
 *
 * @return array
 */
function view_pages($data)
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
 * ページの初期値
 *
 * @return array
 */
function default_pages()
{
    return [
        'id'           => null,
        'created'      => localdate('Y-m-d H:i:s'),
        'modified'     => localdate('Y-m-d H:i:s'),
        'deleted'      => null,
        'public'       => 1,
        'public_begin' => null,
        'public_end'   => null,
        'datetime'     => localdate('Y-m-d H:00'),
        'title'        => '',
        'code'         => '',
        'text'         => null,
        'picture'      => null,
        'field_sets'   => [],
        'thumbnail'    => null,
    ];
}
