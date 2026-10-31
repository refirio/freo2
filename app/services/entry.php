<?php

import('app/services/log.php');
import('app/services/storage.php');

/**
 * 公開エントリーの取得
 *
 * @param string $type
 * @param array  $queries
 * @param array  $options
 *
 * @return array
 */
function service_entry_select_published($type, $queries, $options = [])
{
    $authority_power = isset($GLOBALS['authority']['power']) ? $GLOBALS['authority']['power'] : null;
    $attributes      = isset($GLOBALS['attributes'])         ? $GLOBALS['attributes']         : null;

    // エントリーの絞り込み
    if (empty($queries['where'])) {
        $where1 = 'TRUE';
        $where2 = [];
    } elseif (!is_array($queries['where'])) {
        $where1 = $queries['where'];
        $where2 = [];
    } else {
        $where1 = $queries['where'][0];
        $where2 = $queries['where'][1];
    }

    if ($authority_power >= 1) {
        // 閲覧者以上: 「非公開」以外のエントリーを表示
        $public = ' AND entries.public != ' . db_escape('none');
    } elseif ($authority_power === 0) {
        // ゲスト: 「全体に公開」のエントリー、もしくは「登録ユーザーに公開」のエントリー、もしくは「指定の属性に公開」で指定の属性を持つエントリーを表示
        $public = ' AND (entries.public = ' . db_escape('all') . ' OR entries.public = ' . db_escape('user') . ' OR (entries.public = ' . db_escape('attribute') . ' AND attribute_sets.attribute_id IN(' . ($attributes ? implode(',', $attributes) : 0) . ')) OR entries.public = ' . db_escape('password') . ')';
    } else {
        // ユーザー登録なし: 「全体に公開」のエントリーを表示
        $public = ' AND (entries.public = ' . db_escape('all') . ' OR entries.public = ' . db_escape('password') . ')';
    }

    $where1 .= ' AND types.code = :type_code' . $public . ' AND entries.approved = 1 AND (entries.public_begin IS NULL OR entries.public_begin <= :now) AND (entries.public_end IS NULL OR entries.public_end >= :now)';
    $where2['type_code'] = $type;
    $where2['now']       = localdate('Y-m-d H:i:s');

    $queries['where'] = [$where1, $where2];

    // エントリーを取得
    $entries = model('select_entries', $queries, [
        'associate' => true,
    ]);

    // エントリーの表示制限
    $temps = [];
    foreach ($entries as $entry) {
        if (isset($entry['id'])) {
            if ($entry['public'] === 'password' && empty($_SESSION['entry_passwords'][$entry['id']])) {
                $entry['title']     = $GLOBALS['setting']['restricted_password_title'] . $entry['title'];
                $entry['text']      = $GLOBALS['setting']['restricted_password_text'];
                $entry['pictures']  = null;
                $entry['thumbnail'] = null;

                // フィールドの値も伏せる（画像・ファイルのフィールドは、公開側でファイルを開けなくなる）
                if (isset($entry['field_sets'])) {
                    $entry['field_sets'] = [];
                }
            } else {
                if ($entry['text_type'] === 'none') {
                    $entry['text'] = null;
                } elseif ($entry['text_type'] === 'textarea') {
                    $entry['text'] = '<p>' . h($entry['text'], true) . '</p>';
                }

                if (!empty($entry['pictures'])) {
                    $entry['pictures'] = explode("\n", $entry['pictures']);
                }
            }
        }
        $temps[] = $entry;
    }
    $entries = $temps;

    return $entries;
}

/**
 * 公開側の詳細ページのURLを取得
 *
 * ページは /page/<コード>、それ以外の型は /<型コード>/detail/<コード>
 *
 * @param array $entry
 *
 * @return string
 */
function service_entry_url($entry)
{
    if ($entry['type_code'] === 'page') {
        return '/page/' . $entry['code'];
    }

    return '/' . $entry['type_code'] . '/detail/' . $entry['code'];
}

/**
 * 公開側のエントリー一覧のURLを取得
 *
 * /entry/、/entry/category/<コード>/、/entry/archive/<年月>/ のいずれかで、2ページ目以降は page/<ページ>/ を付ける
 *
 * @param string|null $category
 * @param string|null $archive
 * @param int         $page
 *
 * @return string
 */
function service_entry_list_url($category = null, $archive = null, $page = 1)
{
    $url = '/entry/';
    if ($category !== null) {
        $url .= 'category/' . rawurlencode($category) . '/';
    } elseif ($archive !== null) {
        $url .= 'archive/' . rawurlencode($archive) . '/';
    }
    if ($page > 1) {
        $url .= 'page/' . intval($page) . '/';
    }

    return $url;
}

/**
 * 公開側のエントリー一覧のパスを解析
 *
 * $_params のうち mode より後ろを受け取り、絞り込み条件を返す。形が正しくなければ null を返す
 *
 * @param array $params
 *
 * @return array|null ['category' => コード, 'archive' => 年月, 'page' => ページ]
 */
function service_entry_list_parse($params)
{
    // 末尾のスラッシュでできる空の要素は除く
    $params = array_values(array_filter($params, function ($param) {
        return $param !== '';
    }));

    $results = [
        'category' => null,
        'archive'  => null,
        'page'     => 1,
    ];

    if (isset($params[0], $params[1]) && $params[0] === 'category') {
        $results['category'] = $params[1];
        $params = array_slice($params, 2);
    } elseif (isset($params[0], $params[1]) && $params[0] === 'archive' && preg_match('/^\d{4}-\d{2}$/', $params[1])) {
        $results['archive'] = $params[1];
        $params = array_slice($params, 2);
    }
    if (isset($params[0], $params[1]) && $params[0] === 'page' && preg_match('/^[1-9]\d*$/', $params[1])) {
        $results['page'] = intval($params[1]);
        $params = array_slice($params, 2);
    }
    if (!empty($params)) {
        return null;
    }

    return $results;
}

/**
 * 公開側のエントリー一覧の絞り込み条件を、パスで表せる形に変換
 *
 * カテゴリー1つ・年月・ページだけで絞り込んでいるときに条件を返し、パスで表せない絞り込み（複数のカテゴリー、カテゴリーと年月の組み合わせ、キーワードなど）があれば null を返す
 *
 * @param array $queries
 *
 * @return array|null ['category' => コード, 'archive' => 年月, 'page' => ページ]
 */
function service_entry_list_filter($queries)
{
    foreach (['type_id', 'public', 'datetime', 'title', 'text', 'field_sets', 'keyword'] as $key) {
        if (isset($queries[$key]) && $queries[$key] !== '' && $queries[$key] !== []) {
            return null;
        }
    }

    $results = [
        'category' => null,
        'archive'  => null,
        'page'     => 1,
    ];

    // カテゴリー
    if (isset($queries['category_sets']) && $queries['category_sets'] !== []) {
        if (!is_array($queries['category_sets']) || count($queries['category_sets']) !== 1 || !is_scalar(reset($queries['category_sets']))) {
            return null;
        }

        $categories = model('select_categories', [
            'where' => [
                'categories.id = :id AND types.code = :type',
                [
                    'id'   => reset($queries['category_sets']),
                    'type' => 'entry',
                ],
            ],
        ], [
            'associate' => true,
        ]);
        if (empty($categories)) {
            return null;
        }
        $results['category'] = $categories[0]['code'];
    }

    // 年月
    if (isset($queries['archive']) && $queries['archive'] !== '') {
        if (!is_string($queries['archive']) || !preg_match('/^\d{4}-\d{2}$/', $queries['archive'])) {
            return null;
        }
        $results['archive'] = $queries['archive'];
    }
    if ($results['category'] !== null && $results['archive'] !== null) {
        return null;
    }

    // ページ
    if (isset($queries['page'])) {
        if (!is_scalar($queries['page']) || !preg_match('/^[1-9]\d*$/', (string)$queries['page'])) {
            return null;
        }
        $results['page'] = intval($queries['page']);
    }

    return $results;
}

/**
 * 公開側で表示できるファイルの保存先を取得
 *
 * エントリーの公開範囲で判定し、表示できなければ null を返す
 *
 * @param string $target
 * @param string $key
 * @param string $name
 *
 * @return string|null
 */
function service_entry_file_published($target, $key, $name)
{
    // 対象（エントリーの画像はエントリーのID、フィールドのファイルは「<エントリーのID>_<フィールドのID>」）
    if ($target === 'entry' && preg_match('/^\d+$/', $key)) {
        $entry_id = $key;
        $field_id = null;
    } elseif ($target === 'field' && preg_match('/^(\d+)_(\d+)$/', $key, $matches)) {
        $entry_id = $matches[1];
        $field_id = $matches[2];
    } else {
        return null;
    }

    // 公開エントリーの取得には型が要るので、先に型を調べる
    $entries = model('select_entries', [
        'where' => [
            'entries.id = :id',
            [
                'id' => $entry_id,
            ],
        ],
    ], [
        'associate' => true,
    ]);
    if (empty($entries)) {
        return null;
    }

    $entries = service_entry_select_published($entries[0]['type_code'], [
        'where' => [
            'entries.id = :id',
            [
                'id' => $entry_id,
            ],
        ],
    ]);
    if (empty($entries)) {
        return null;
    }
    $entry = $entries[0];

    // 表示しているエントリーのファイルか（パスワード認証の前は画像が外れているので、ここで表示できなくなる）
    if ($target === 'entry') {
        $names = empty($entry['pictures']) ? [] : $entry['pictures'];
        if (!empty($entry['thumbnail'])) {
            $names[] = $entry['thumbnail'];
        }
        if (!in_array($name, $names, true)) {
            return null;
        }
    } elseif (!isset($entry['field_sets'][$field_id]) || $entry['field_sets'][$field_id] !== $name) {
        return null;
    }

    return $GLOBALS['config']['file_target'][$target] . $key . '/' . $name;
}

/**
 * 公開側でファイルを出力
 *
 * /storage/<対象>/<キー>/<ファイル名> のコントローラーから呼ぶ。表示できなければ 404 で終了する
 *
 * @param string $target
 *
 * @return void
 */
function service_entry_file_output($target)
{
    global $_params;

    // 同じ人の画像を並べて取得できるよう、セッションのロックを外す（この後はセッションに書き込まない）
    session_write_close();

    // ファイル名（拡張子は routing() で分かれているので戻す）
    if ($GLOBALS['config']['storage_protect'] && count($_params) === 5) {
        $file = service_entry_file_published($target, $_params[2], $_params[3] . '.' . $_params[4]);

        if ($file !== null) {
            service_storage_output($file);
        }
    }

    // 表示できないときは、ファイルがあるかどうかも分からないよう、同じ 404 にする（画像の取得が多いので、画面は組み立てない）
    header('HTTP/1.0 404 Not Found');
    header('Content-Type: text/plain; charset=' . MAIN_CHARSET);

    echo '404 Not Found';
    exit;
}

/**
 * パスワード認証
 *
 * 認証に成功したら、セッションに記録する
 *
 * @param array  $entry
 * @param string $password
 *
 * @return bool
 */
function service_entry_password($entry, $password)
{
    if ($password !== $entry['password']) {
        return false;
    }

    $_SESSION['entry_passwords'][$entry['id']] = true;

    return true;
}

/**
 * エントリーの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_entry_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'entries', 'insert');

    // ユーザー
    if (!empty($_SESSION['auth']['user']['id'])) {
        $queries['values']['user_id'] = $_SESSION['auth']['user']['id'];
    }

    // エントリーを登録
    $resource = model('insert_entries', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
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
function service_entry_update($queries, $options = [])
{
    $options = [
        'id'             => isset($options['id'])             ? $options['id']             : null,
        'field_sets'     => isset($options['field_sets'])     ? $options['field_sets']     : [],
        'category_sets'  => isset($options['category_sets'])  ? $options['category_sets']  : [],
        'attribute_sets' => isset($options['attribute_sets']) ? $options['attribute_sets'] : [],
        'files'          => isset($options['files'])          ? $options['files']          : [],
        'picture_files'  => isset($options['picture_files'])  ? $options['picture_files']  : [],
        'update'         => isset($options['update'])         ? $options['update']         : null,
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $entries = model('select_entries', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($entries)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'entries', 'update');

    // エントリーを編集
    $resource = model('update_entries', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
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
function service_entry_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'entries', 'delete');

    // エントリーを削除
    $resource = model('delete_entries', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}

/**
 * エントリーの並び順を一括変更
 *
 * @param array $data
 *
 * @return void
 */
function service_entry_sort($data)
{
    // 並び順を更新
    foreach ($data as $id => $sort) {
        if (!preg_match('/^[\w\-\/]+$/', $id)) {
            continue;
        }
        if (!preg_match('/^\d+$/', $sort)) {
            continue;
        }

        $resource = service_entry_update([
            'set'   => [
                'sort' => $sort,
            ],
            'where' => [
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

    return;
}

/**
 * 親のコードを取得
 *
 * コードをスラッシュで区切った階層を末尾から遡り、$codes に含まれる最も近い祖先を親とする
 * （about/staff が無ければ、about/staff/yamada の親は about）
 *
 * @param string $code
 * @param array  $codes
 *
 * @return string|null
 */
function service_entry_parent($code, $codes)
{
    $position = strrpos($code, '/');
    while ($position !== false) {
        $code = substr($code, 0, $position);
        if (in_array($code, $codes, true)) {
            return $code;
        }
        $position = strrpos($code, '/');
    }

    return null;
}

/**
 * ページを一括取得
 *
 * @param string $select
 *
 * @return array
 */
function service_entry_select_pages($select)
{
    $types = model('select_types', [
        'select' => 'id',
        'where'  => 'code = ' . db_escape('page'),
    ]);
    if (empty($types)) {
        return [];
    }

    return model('select_entries', [
        'select' => $select,
        'where'  => [
            'type_id = :type_id',
            [
                'type_id' => $types[0]['id'],
            ],
        ],
    ]);
}

/**
 * ページのコードを一括取得
 *
 * @return array
 */
function service_entry_page_codes()
{
    return array_column(service_entry_select_pages('code'), 'code');
}

/**
 * 親ページを階層順に取得
 *
 * $parent と、その祖先のページを一覧のトップに近い順に返す（パンくずに使う）
 *
 * @param string|null $parent
 *
 * @return array
 */
function service_entry_page_parents($parent)
{
    $parents = [];
    if ($parent === null) {
        return $parents;
    }

    $entries = service_entry_select_pages('code, title');
    $codes   = array_column($entries, 'code');
    $titles  = array_column($entries, 'title', 'code');

    for ($code = $parent; $code !== null; $code = service_entry_parent($code, $codes)) {
        if (!isset($titles[$code])) {
            break;
        }
        array_unshift($parents, [
            'code'  => $code,
            'title' => $titles[$code],
        ]);
    }

    return $parents;
}

/**
 * ページ一覧のURLを取得
 *
 * @param string      $query
 * @param string|null $parent
 *
 * @return string
 */
function service_entry_page_list($query, $parent)
{
    $url = '/admin/page?' . $query;
    if ($parent !== null) {
        $url .= '&parent=' . rawurlencode($parent);
    }

    return $url;
}

/**
 * アップロードされたファイルを一時ファイルに保存（セッションには、返した名前と一時ファイルの場所だけを入れる）
 *
 * @param string $name     ファイル名
 * @param string $tmp_name アップロードされたファイルの場所
 *
 * @return array|false ['name' => ファイル名, 'temp' => 一時ファイルの場所]、保存できなければ false
 */
function service_entry_file_upload($name, $tmp_name)
{
    // 古い一時ファイルを削除
    service_storage_temp_clean();

    // ファイル名は利用者の入力なので、一時ファイルの名前には使わない
    $temp = $GLOBALS['config']['file_target']['temp'] . session_id() . '_entry_' . bin2hex(random_bytes(16));

    if (service_storage_put($temp, file_get_contents($tmp_name)) === false) {
        return false;
    }

    return [
        'name' => $name,
        'temp' => $temp,
    ];
}

/**
 * アップロードされたファイルの内容を取得（一時ファイルから読む。以前の形でセッションに内容があれば、それを返す）
 *
 * @param array $file
 *
 * @return string|null
 */
function service_entry_file_data($file)
{
    if (isset($file['temp'])) {
        return service_storage_exist($file['temp']) ? service_storage_get($file['temp']) : null;
    }

    return isset($file['data']) ? $file['data'] : null;
}

/**
 * アップロードされたファイルの情報を破棄（一時ファイルも削除する）
 *
 * @return void
 */
function service_entry_file_clear()
{
    if (isset($_SESSION['file'])) {
        array_walk_recursive($_SESSION['file'], function ($value, $key) {
            if ($key === 'temp' && service_storage_exist($value)) {
                service_storage_remove($value);
            }
        });
    }

    unset($_SESSION['file']);
}
