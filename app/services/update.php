<?php

/**
 * 未実行のマイグレーションの取得
 *
 * @return array
 */
function service_update_pending()
{
    // マイグレーションを一度も実行していなければ、初回の設置前として扱う（更新の対象にしない）
    if (!db_migrate_installed()) {
        return [];
    }

    $targets = db_migrate_targets(true);

    // 削除済みファイルの一覧に載っているもの（以前の版で名前を変えたものなど）は、サーバーに残っていても実行しない
    if (!empty($targets)) {
        $removed = service_update_removed_list();

        $targets = array_values(array_filter($targets, function ($target) use ($removed) {
            return !isset($removed['migrate/' . $target]);
        }));
    }

    return $targets;
}

/**
 * プログラムを置いたディレクトリ（削除済みファイルを探す場所）
 * （1つめはプログラムの置き場所、2つめは公開ディレクトリ。標準の配置では同じ場所になる）
 *
 * @return array
 */
function service_update_roots()
{
    // import() と同じく、MAIN_PATH が無ければ index.php のあるディレクトリを基準にする
    $base = MAIN_PATH ? MAIN_PATH : dirname($_SERVER['SCRIPT_FILENAME']) . '/';

    return array_values(array_unique([$base . MAIN_APPLICATION_PATH, $base]));
}

/**
 * 削除済みファイルの一覧の取得
 * （リリースの ZIP に入っている app/removed_files.txt。形式は sha256sum の出力と同じで、1行に「ハッシュ、空白2つ、パス」）
 *
 * @param string|null $file
 *
 * @return array パスをキーに、過去の版のハッシュの配列
 */
function service_update_removed_list($file = null)
{
    if ($file === null) {
        $file = service_update_roots()[0] . 'app/removed_files.txt';
    }
    if (!is_file($file) || !is_readable($file)) {
        return [];
    }

    $list = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (!preg_match('/^([0-9a-f]{64})  (.+)$/', rtrim($line, "\r"), $matches)) {
            continue;
        }
        if (!service_update_removed_path($matches[2])) {
            continue;
        }

        $list[$matches[2]][] = $matches[1];
    }

    return $list;
}

/**
 * 削除の対象にしてよいパスか
 * （一覧の書き換えなどで、プログラムの外や利用者のファイルを消さないようにする）
 *
 * @param string $path
 *
 * @return bool
 */
function service_update_removed_path($path)
{
    // 使える文字と形（先頭の / や、..・空のディレクトリ名を含むものは不可）
    if (!preg_match('/^[A-Za-z0-9_\-.]+(\/[A-Za-z0-9_\-.]+)*$/', $path) || preg_match('/(^|\/)\.\.?(\/|$)/', $path)) {
        return false;
    }

    // アップロードしたファイル、Composer で入れたライブラリ、同梱していないプラグイン・テーマは対象外
    if (preg_match('/^(files|libs\/vendor)\//', $path) || preg_match('/^(plugins|themes)\/(?!sample\/)/', $path)) {
        return false;
    }

    return true;
}

/**
 * ファイルの内容のハッシュ
 * （改行コードの違いで判定が変わらないよう、CRLF を LF にそろえてから計算する）
 *
 * @param string $file
 *
 * @return string|null
 */
function service_update_hash($file)
{
    $content = file_get_contents($file);
    if ($content === false) {
        return null;
    }

    return hash('sha256', str_replace("\r\n", "\n", $content));
}

/**
 * サーバーに残っている削除済みファイルの取得
 * （内容が本体の過去の版と一致するものは delete に、一致しないもの（編集した・利用者が作った）は keep に入れる）
 *
 * @param array|null $list
 * @param array|null $roots
 *
 * @return array
 */
function service_update_removed($list = null, $roots = null)
{
    if ($list === null) {
        $list = service_update_removed_list();
    }
    if ($roots === null) {
        $roots = service_update_roots();
    }

    $results = [
        'delete' => [],
        'keep'   => [],
    ];

    $checked = [];
    foreach ($list as $path => $hashes) {
        foreach ($roots as $root) {
            if (!is_file($root . $path)) {
                continue;
            }

            // 標準の配置では2つの場所が同じなので、同じファイルは1回だけ扱う
            $file = realpath($root . $path);
            if ($file === false || isset($checked[$file])) {
                continue;
            }
            $checked[$file] = true;

            $hash = is_readable($file) ? service_update_hash($file) : null;

            $results[in_array($hash, $hashes, true) ? 'delete' : 'keep'][] = [
                'path' => $path,
                'file' => $file,
            ];
        }
    }

    return $results;
}

/**
 * 削除済みファイルの削除
 * （送信された内容は使わず、その場で調べ直して、内容が一致するものだけを消す）
 *
 * @param array|null $list
 * @param array|null $roots
 *
 * @return array
 */
function service_update_remove($list = null, $roots = null)
{
    $results = [
        'deleted' => [],
        'failed'  => [],
    ];

    foreach (service_update_removed($list, $roots)['delete'] as $removed) {
        if (@unlink($removed['file'])) {
            $results['deleted'][] = $removed;
        } else {
            $results['failed'][] = $removed;
        }
    }

    return $results;
}

/**
 * DB の更新
 * （最初に失敗したところで止まる。戻り値の最後の要素の error が null でなければ失敗）
 *
 * @param array $targets
 *
 * @return array
 */
function service_update_migrate($targets)
{
    // 時間の掛かる更新でも途中で止まらないようにする（ブラウザを閉じても続ける）
    if (function_exists('set_time_limit')) {
        set_time_limit(0);
    }
    ignore_user_abort(true);

    return db_migrate_execute($targets);
}

/**
 * DB の更新に失敗したか
 *
 * @param array $results
 *
 * @return bool
 */
function service_update_failed($results)
{
    if (empty($results)) {
        return false;
    }

    return $results[count($results) - 1]['error'] !== null;
}

/**
 * 更新できる管理者がログインしているか
 * （DB の更新が済むまで、テーブルが新しいコードと合わないことがあるので、読む列はユーザーの有効状態と権限の強さだけにする）
 *
 * @return bool
 */
function service_update_admin()
{
    if (empty($_SESSION['auth']['user']['id'])) {
        return false;
    }

    $users = model('select_users', [
        'select' => 'authority_id',
        'where'  => [
            'id = :id AND enabled = 1',
            [
                'id' => $_SESSION['auth']['user']['id'],
            ],
        ],
    ]);
    if (empty($users)) {
        return false;
    }

    $authorities = model('select_authorities', [
        'select' => 'power',
        'where'  => [
            'id = :id',
            [
                'id' => $users[0]['authority_id'],
            ],
        ],
    ]);
    if (empty($authorities)) {
        return false;
    }

    return $authorities[0]['power'] == 3;
}
