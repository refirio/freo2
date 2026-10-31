<?php

import('app/services/entry.php');
import('app/services/user.php');
import('libs/modules/directory.php');

/**
 * 作業用のディレクトリを取得
 *
 * @param string $token
 *
 * @return string
 */
function service_export_dir($token)
{
    return $GLOBALS['config']['file_target']['temp'] . 'export_' . $token . '/';
}

/**
 * 出力を開始
 *
 * 作業用のディレクトリを作り、出力するURLの一覧を保存する
 *
 * @return array ['token' => トークン, 'urls' => 出力するURLの一覧]
 */
function service_export_start()
{
    service_export_clean();

    $token = service_user_token();
    $dir   = service_export_dir($token);

    if (!directory_mkdir($dir . 'html/')) {
        error('作業用のディレクトリを作成できません。');
    }

    $urls = service_export_urls();
    if (file_put_contents($dir . 'urls.json', json_encode($urls)) === false) {
        error('出力するURLの一覧を保存できません。');
    }

    return [
        'token' => $token,
        'urls'  => $urls,
    ];
}

/**
 * 出力するURLの一覧を取得
 *
 * @param string $token
 *
 * @return array
 */
function service_export_load($token)
{
    $file = service_export_dir($token) . 'urls.json';
    if (!is_file($file)) {
        return [];
    }

    return json_decode(file_get_contents($file), true);
}

/**
 * 古い作業用のディレクトリを削除
 *
 * 一時ファイルと同じく、temp_retention より古くなったものを削除する
 *
 * @return void
 */
function service_export_clean()
{
    $limit = localdate() - $GLOBALS['config']['temp_retention'];

    foreach (glob($GLOBALS['config']['file_target']['temp'] . 'export_*', GLOB_ONLYDIR) as $dir) {
        if (preg_match('/\/export_[0-9a-f]{32}$/', $dir) && filemtime($dir) < $limit) {
            directory_rmdir($dir . '/');
        }
    }
}

/**
 * 公開エントリーを、未ログインの訪問者と同じ判定で取得
 *
 * 出力の画面は管理者がログインした状態で動くので、権限と属性を外してから取得する。「全体に公開」のものだけを返す
 *
 * @param string $type
 * @param array  $queries
 *
 * @return array
 */
function service_export_select_published($type, $queries)
{
    $authority  = isset($GLOBALS['authority'])  ? $GLOBALS['authority']  : null;
    $attributes = isset($GLOBALS['attributes']) ? $GLOBALS['attributes'] : null;
    unset($GLOBALS['authority'], $GLOBALS['attributes']);

    $where = 'entries.public = ' . db_escape('all');
    if (!empty($queries['where'])) {
        $where .= ' AND (' . $queries['where'] . ')';
    }
    $queries['where'] = $where;

    $entries = service_entry_select_published($type, $queries);

    if ($authority !== null) {
        $GLOBALS['authority'] = $authority;
    }
    if ($attributes !== null) {
        $GLOBALS['attributes'] = $attributes;
    }

    return $entries;
}

/**
 * 出力するURLの一覧を作成
 *
 * @return array [['url' => URL, 'file' => 出力するファイル], ...]
 */
function service_export_urls()
{
    $urls = [];

    // トップページ
    $urls[] = '/';

    // エントリーの一覧（1ページも無いときも、一覧からのリンク先なので1ページ目は出力する）
    $limit = max(1, intval($GLOBALS['setting']['number_limit_entry']));

    $counts = service_export_select_published('entry', [
        'select' => 'COUNT(DISTINCT entries.id) AS count',
    ]);
    for ($i = 1; $i <= max(1, ceil($counts[0]['count'] / $limit)); $i++) {
        $urls[] = service_entry_list_url(null, null, $i);
    }

    $categories = model('select_categories', [
        'where'    => 'types.code = ' . db_escape('entry'),
        'order_by' => 'categories.sort, categories.id',
    ], [
        'associate' => true,
    ]);
    foreach ($categories as $category) {
        $counts = service_export_select_published('entry', [
            'select' => 'COUNT(DISTINCT entries.id) AS count',
            'where'  => 'category_sets.category_id = ' . intval($category['id']),
        ]);
        for ($i = 1; $i <= max(1, ceil($counts[0]['count'] / $limit)); $i++) {
            $urls[] = service_entry_list_url($category['code'], null, $i);
        }
    }

    $archives = service_export_select_published('entry', [
        'select'   => 'DATE_FORMAT(entries.datetime, ' . db_escape('%Y-%m') . ') AS month, COUNT(DISTINCT entries.id) AS count',
        'group_by' => 'month',
        'order_by' => 'month DESC',
    ]);
    foreach ($archives as $archive) {
        for ($i = 1; $i <= ceil($archive['count'] / $limit); $i++) {
            $urls[] = service_entry_list_url(null, $archive['month'], $i);
        }
    }

    // エントリーとページの詳細（画像があれば、画像のページも）
    foreach (['entry', 'page'] as $type) {
        $entries = service_export_select_published($type, [
            'order_by' => 'entries.id',
        ]);
        foreach ($entries as $entry) {
            $urls[] = service_entry_url($entry);

            if ($type === 'page' && $GLOBALS['setting']['page_url_omission']) {
                $urls[] = '/' . $entry['code'];
            }
            if (!empty($entry['thumbnail']) || !empty($entry['pictures'])) {
                $urls[] = '/file/' . $type . '/' . $entry['code'];
            }
        }
    }

    // プラグインのページ（config.php の export に、URLの配列か、URLの配列を返す関数の名前を書く）
    foreach (service_export_plugins() as $plugin) {
        if (is_array($plugin['export'])) {
            $urls = array_merge($urls, $plugin['export']);
        } elseif (is_string($plugin['export']) && function_exists($plugin['export'])) {
            $urls = array_merge($urls, call_user_func($plugin['export']));
        }
    }

    $results = [];
    foreach (array_unique($urls) as $url) {
        $results[] = [
            'url'  => $url,
            'file' => service_export_file($url),
        ];
    }

    // RSS（index.html では HTML として配信されるので、拡張子を付ける）
    $results[] = [
        'url'  => '/entry/feed',
        'file' => 'entry/feed.xml',
    ];

    // 存在しないページ（どこにも無いURLを取得して、404 のページを出力する）
    $results[] = [
        'url'  => '/export-not-found/',
        'file' => '404.html',
    ];

    return $results;
}

/**
 * 有効なプラグインのうち、静的出力への対応を書いているものを取得
 *
 * @return array [コード => ['name' => 名前, 'export' => config.php の export（書いていなければ null）], ...]
 */
function service_export_plugins()
{
    $results = [];

    if (empty($GLOBALS['plugin'])) {
        return $results;
    }

    foreach ($GLOBALS['plugin'] as $code => $plugin) {
        if (!isset($plugin['setting'])) {
            // 有効なプラグインだけ before.php が setting を入れる
            continue;
        }

        $results[$code] = [
            'name'   => $plugin['name'],
            'export' => array_key_exists('export', $plugin) ? $plugin['export'] : null,
        ];
    }

    return $results;
}

/**
 * URLから出力するファイルを取得
 *
 * /entry/ は entry/index.html、/entry/detail/hello は entry/detail/hello/index.html にする
 *
 * @param string $url
 *
 * @return string
 */
function service_export_file($url)
{
    $path = ltrim((string)parse_url($url, PHP_URL_PATH), '/');

    if ($path !== '' && substr($path, -1) !== '/') {
        $path .= '/';
    }

    return $path . 'index.html';
}

/**
 * ページを取得
 *
 * クッキーを付けずに取得するので、未ログインの訪問者と同じ内容になる
 *
 * @param string $token
 * @param string $url
 *
 * @return array|null ['status' => ステータス, 'body' => 内容]、取得できなければ null
 */
function service_export_fetch($token, $url)
{
    $target = rtrim($GLOBALS['config']['export_fetch_url'], '/') . $url;

    // テスト中は、取得するページもテスト用データベースで描画する
    if (app_test()) {
        $target .= (strpos($target, '?') === false ? '?' : '&') . 'test=export';
    }

    $curl = curl_init($target);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTPHEADER     => ['X-Freo2-Export: ' . $token],
    ]);
    $body = curl_exec($curl);
    if ($body === false) {
        return null;
    }

    return [
        'status' => curl_getinfo($curl, CURLINFO_RESPONSE_CODE),
        'body'   => $body,
    ];
}

/**
 * ページを描画（コマンドラインから出力するとき）
 *
 * ページごとに別のプロセス（php index.php <URL>）で描画する。トークンは環境変数で渡す（CLI では環境変数が $_SERVER に入る）
 *
 * @param string $token
 * @param string $url
 *
 * @return string|null 描画したページ、描画できなければ（転送・警告・エラーのとき）null
 */
function service_export_render($token, $url)
{
    // テスト中は、描画するページもテスト用データベースで描画する
    if (app_test()) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . 'test=export';
    }

    // 描画したページは標準出力から受け取る（標準エラー出力は、このコマンドの標準エラー出力にそのまま出す）
    $process = proc_open([PHP_BINARY, 'index.php', $url], [
        1 => ['pipe', 'w'],
        2 => STDERR,
    ], $pipes, null, array_merge(getenv(), [
        'HTTP_X_FREO2_EXPORT' => $token,
    ]));
    if (!is_resource($process)) {
        return null;
    }

    $body = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    if (proc_close($process) !== 0 || $body === '' || preg_match('/^(WARNING|ERROR): /', $body)) {
        return null;
    }

    return $body;
}

/**
 * HTMLのURLを置き換える内容を取得
 *
 * @return array ['url' => [置き換える前 => 後], 'path' => [置き換える前, ...], 'to' => 置き換えた後のパス]
 */
function service_export_replaces()
{
    $export_url  = rtrim(trim($GLOBALS['setting']['export_url']), '/');
    $export_path = rtrim((string)parse_url($export_url, PHP_URL_PATH), '/') . '/';

    // 絶対URL（管理用のURLと、ファイルの保存先のURL）
    $urls = [
        rtrim($GLOBALS['config']['http_url'], '/') . '/' => $export_url . '/',
    ];
    if ($GLOBALS['config']['storage_type'] === 'file') {
        $urls[rtrim($GLOBALS['config']['storage_url'], '/') . '/'] = $export_url . '/';
    }

    // サイトの中の絶対パス（index.php を含む形を先に）
    $paths = [];
    if (MAIN_FILE !== '') {
        $paths[] = MAIN_FILE . '/';
    }
    $paths[] = $GLOBALS['config']['http_path'];

    return [
        'url'  => $urls,
        'path' => $paths,
        'to'   => $export_path,
    ];
}

/**
 * HTMLのURLを置き換える
 *
 * @param string $html
 * @param array  $replaces service_export_replaces() の戻り値
 *
 * @return string
 */
function service_export_convert($html, $replaces)
{
    // 絶対URL（strtr() は長いものから置き換え、置き換えた結果をもう一度置き換えない）
    $html = strtr($html, $replaces['url']);

    // 属性に書かれたサイトの中の絶対パス（// で始まるURLは除く。1回の置き換えで済ませ、置き換えた結果をもう一度置き換えない）
    $paths = array_map(function ($path) {
        return preg_quote($path, '/');
    }, $replaces['path']);
    $html  = preg_replace('/(\s(?:href|src|action)=["\'])(?:' . implode('|', $paths) . ')(?!\/)/', '${1}' . str_replace(['\\', '$'], ['\\\\', '\\$'], $replaces['to']), $html);

    // RSS は entry/feed.xml に出力する
    $html = preg_replace('/\/entry\/feed(?=["\'<])/', '/entry/feed.xml', $html);

    return $html;
}

/**
 * 取得したページを保存
 *
 * @param string $token
 * @param string $file
 * @param string $content
 *
 * @return bool
 */
function service_export_save($token, $file, $content)
{
    // 作業用のディレクトリの外に書き込まないよう、形を確かめる（service_export_file() が作る形と、RSS・404 のページ）
    if (!preg_match('/^([\w\-]+\/)*[\w\-]+\.(html|xml)$/', $file)) {
        return false;
    }

    $path = service_export_dir($token) . 'html/' . $file;

    if (!is_dir(dirname($path)) && !directory_mkdir(dirname($path))) {
        return false;
    }

    return file_put_contents($path, $content) !== false;
}

/**
 * 静的なファイルを複製
 *
 * CSS・JavaScript・画像と、ストレージがファイルのときはアップロードしたファイルを複製する
 *
 * @param string $token
 *
 * @return bool
 */
function service_export_static($token)
{
    $dir = service_export_dir($token) . 'html/';

    $targets = $GLOBALS['config']['export_static'];
    if ($GLOBALS['config']['storage_type'] === 'file') {
        foreach (['entry', 'field', 'media', 'thumbnail'] as $key) {
            $targets[] = $GLOBALS['config']['file_target'][$key];
        }
    }

    foreach ($targets as $target) {
        if (!is_dir($target)) {
            continue;
        }
        if (!service_export_copy($target, $dir . $target)) {
            return false;
        }
    }

    return true;
}

/**
 * ディレクトリを複製
 *
 * 公開しないファイル（.htaccess などの . で始まるファイルと、PHP のファイル）は複製しない
 *
 * @param string $from
 * @param string $to
 *
 * @return bool
 */
function service_export_copy($from, $to)
{
    $from = rtrim($from, '/') . '/';
    $to   = rtrim($to, '/') . '/';

    $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($items as $item) {
        $name = substr(str_replace('\\', '/', $item->getPathname()), strlen($from));

        if (preg_match('/(^|\/)\./', $name) || preg_match('/\.php$/i', $name)) {
            continue;
        }

        if ($item->isDir()) {
            if (!is_dir($to . $name) && !directory_mkdir($to . $name)) {
                return false;
            }
        } else {
            if (!is_dir(dirname($to . $name)) && !directory_mkdir(dirname($to . $name))) {
                return false;
            }
            if (!copy($item->getPathname(), $to . $name)) {
                return false;
            }
        }
    }

    return true;
}

/**
 * 出力したファイルを ZIP にまとめる
 *
 * @param string      $token
 * @param string|null $path  ZIP ファイルの保存先（指定しなければ作業用のディレクトリ）
 *
 * @return bool
 */
function service_export_zip($token, $path = null)
{
    $dir = service_export_dir($token);

    if ($path === null) {
        $path = $dir . 'export.zip';
    }

    $zip = new ZipArchive();
    if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }

    $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir . 'html/', FilesystemIterator::SKIP_DOTS));
    foreach ($items as $item) {
        $name = substr(str_replace('\\', '/', $item->getPathname()), strlen($dir . 'html/'));

        if (!$zip->addFile($item->getPathname(), $name)) {
            $zip->close();

            return false;
        }
    }

    return $zip->close();
}

/**
 * 出力する前の確認
 *
 * @return array ['errors' => 出力できない理由, 'notices' => 出力しても使えない機能]
 */
function service_export_check()
{
    $errors  = [];
    $notices = [];

    // 出力できない理由
    // 公開用URL（前後の空白は取り除いて使う）
    $export_url = trim((string)$GLOBALS['setting']['export_url']);
    if ($export_url === '' || !preg_match('/^https?:\/\/[^\/\s]+(\/\S*)?$/', $export_url)) {
        $errors[] = '設定「HTML出力設定」の公開用URLを、http:// か https:// で始まる形で入力してください。';
    }
    if (!function_exists('curl_init')) {
        $errors[] = 'PHP の curl が有効ではありません。';
    }
    if (!class_exists('ZipArchive')) {
        $errors[] = 'PHP の zip が有効ではありません。';
    }

    // 出力しても使えない機能
    $now = localdate('Y-m-d H:i:s');

    $entries = model('select_entries', [
        'select' => 'COUNT(DISTINCT entries.id) AS count',
        'where'  => 'entries.public != ' . db_escape('all'),
    ], [
        'associate' => true,
    ]);
    if ($entries[0]['count'] > 0) {
        $notices[] = '公開範囲が「全体に公開」以外のエントリー・ページ（' . $entries[0]['count'] . '件）は出力しません。会員向けの公開やパスワード認証は使えません。';
    }

    $entries = service_export_select_published('entry', [
        'select' => 'COUNT(DISTINCT entries.id) AS count',
        'where'  => 'entries.comment != ' . db_escape('closed'),
    ]);
    $pages = service_export_select_published('page', [
        'select' => 'COUNT(DISTINCT entries.id) AS count',
        'where'  => 'entries.comment != ' . db_escape('closed'),
    ]);
    if ($entries[0]['count'] + $pages[0]['count'] > 0) {
        $notices[] = 'コメントを受け付けているエントリー・ページ（' . ($entries[0]['count'] + $pages[0]['count']) . '件）は、コメントのフォームを出力しません。承認済みのコメントは表示します。';
    }

    $entries = model('select_entries', [
        'select' => 'COUNT(DISTINCT entries.id) AS count',
        'where'  => [
            'entries.public = :public AND (entries.public_begin > :now OR entries.public_end > :now)',
            [
                'public' => 'all',
                'now'    => $now,
            ],
        ],
    ], [
        'associate' => true,
    ]);
    if ($entries[0]['count'] > 0) {
        $notices[] = '公開期間を指定したエントリー・ページ（' . $entries[0]['count'] . '件）があります。公開の開始・終了の日時になっても、出力し直すまで反映されません。';
    }

    $notices[] = 'お問い合わせ・ログインなどの画面は出力しません。メニューからも外します（ウィジェットや本文に書いたリンクは外れません）。';

    if ($GLOBALS['config']['storage_protect']) {
        $notices[] = 'APP_STORAGE_PROTECT が有効なので、エントリーの画像・フィールドのファイルを表示できません。';
    }

    // 古い形のURL（パスで表せる形に直す）
    $menus = model('select_menus', [
        'select' => 'COUNT(*) AS count',
        'where'  => 'url LIKE ' . db_escape('%/entry/?%'),
    ]);
    $widgets = model('select_widgets', [
        'select' => 'COUNT(*) AS count',
        'where'  => 'text LIKE ' . db_escape('%/entry/?%'),
    ]);
    if ($menus[0]['count'] + $widgets[0]['count'] > 0) {
        $notices[] = 'メニュー・ウィジェットに、エントリー一覧の古い形のURL（/entry/?…）があります。出力したHTMLでは絞り込みが効かないので、/entry/category/<コード>/ などの形に直してください。';
    }

    // メニューのリンク先のうち、出力しないサイトの中のページ（末尾の / とクエリ文字列は除いて比べる。お問い合わせ・ログインはメニューから外すので除く）
    $normalize = function ($url) {
        $path = rtrim((string)parse_url($url, PHP_URL_PATH), '/');

        return $path === '' ? '/' : $path;
    };
    $export_paths = array_map($normalize, array_column(service_export_urls(), 'url'));

    $menus = model('select_menus', [
        'where'    => 'enabled = 1',
        'order_by' => 'sort, id',
    ]);
    foreach ($menus as $menu) {
        if (!preg_match('/^\/(?!\/)/', $menu['url']) || preg_match('/^\/(auth|contact)(\/|\?|$)/', $menu['url'])) {
            continue;
        }
        if (!in_array($normalize($menu['url']), $export_paths, true)) {
            $notices[] = 'メニュー「' . $menu['title'] . '」（' . $menu['url'] . '）のリンク先は出力しません。HTMLではリンクが切れるので、メニューを無効にするか、リンク先を直してください。';
        }
    }

    // プラグイン
    foreach (service_export_plugins() as $plugin) {
        if ($plugin['export'] === false) {
            $notices[] = 'プラグイン「' . $plugin['name'] . '」は、HTML出力に対応していません。';
        } elseif ($plugin['export'] === null) {
            $notices[] = 'プラグイン「' . $plugin['name'] . '」は、HTML出力に対応しているか分かりません。プラグインのページは出力しません。';
        }
    }

    return [
        'errors'  => $errors,
        'notices' => $notices,
    ];
}
