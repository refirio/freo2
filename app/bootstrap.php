<?php

/**
 * Return the value for config.
 *
 * @param string $key
 * @param mixed  $default|null
 *
 * @return mixed|null
 */
function app_config($key, $default)
{
    return defined($key) ? constant($key) : $default;
}

/**
 * Return whether a test (unit test or scenario test) is running.
 *
 * @return bool
 */
function app_test()
{
    // 単体テスト（ブラウザでも CLI でも _mode で分かる）
    if (isset($_REQUEST['_mode']) && preg_match('/^(test_index|test_exec)$/', $_REQUEST['_mode'])) {
        return true;
    }

    // シナリオテスト（?test= は最初のページだけなので、2ページ目からはセッションで分かる。セッションの開始後に呼ぶこと）
    return isset($_GET['test']) || isset($_SESSION['test']);
}

/**
 * Return whether the page is rendered for the HTML export.
 *
 * @return bool
 */
function app_export()
{
    // HTML出力の処理がヘッダーで渡したトークンを、作業用のディレクトリがあるかで確かめる（設定の読み込みの後に呼ぶこと）
    if (!isset($_SERVER['HTTP_X_FREO2_EXPORT']) || !preg_match('/^[0-9a-f]{32}$/', $_SERVER['HTTP_X_FREO2_EXPORT'])) {
        return false;
    }

    return is_dir($GLOBALS['config']['file_target']['temp'] . 'export_' . $_SERVER['HTTP_X_FREO2_EXPORT'] . '/');
}

/**
 * Return the dataset from association.
 *
 * @param string $get_key
 * @param array  $get_value
 * @param string $set_key
 * @param string $set_value|null
 *
 * @return array
 */
function app_dataset($get_key, $get_value, $set_key, $set_value)
{
    $get_keys = explode('.', $get_key);
    $set_keys = explode('.', $set_key);

    $get_key1 = $get_keys[0];
    $get_key2 = isset($get_keys[1]) ? $get_keys[1] : null;

    $set_key1 = $set_keys[0];
    $set_key2 = isset($set_keys[1]) ? $set_keys[1] : null;

    $data_sets = model('select_' . $get_key1, [
        'where' => $get_key1 . '.' . $get_key2 . ' IN(' . implode(',', array_map('db_escape', $get_value)) . ')',
    ], [
        'associate' => true,
    ]);

    $dataset = [];
    foreach ($data_sets as $data_set) {
        if ($set_value) {
            $data = $data_set[$set_value];
        } else {
            $data = $data_set;
        }
        if ($set_key2) {
            $dataset[$data_set[$set_key1]][$data_set[$set_key2]] = $data;
        } else {
            $dataset[$data_set[$set_key1]][] = $data;
        }
    }

    return $dataset;
}

/**
 * Return the class for badge.
 *
 * @param string $key
 * @param string $value
 *
 * @return string
 */
function app_badge($key, $value)
{
    $text_color = 'light';
    $bg_color   = 'secondary';

    if (is_numeric($value) && $value == 1) {
        $text_color = 'light';
        $bg_color   = 'success';
    }
    if (is_numeric($value) && $value == 0) {
        $text_color = 'dark';
        $bg_color   = 'warning';
    }
    if ($value === 'yes') {
        $text_color = 'light';
        $bg_color   = 'success';
    }
    if ($value === 'no') {
        $text_color = 'dark';
        $bg_color   = 'warning';
    }

    if ($key === 'public') {
        if ($value === 'all' || $value === 'user' || $value === 'attribute' || $value === 'password') {
            $text_color = 'light';
            $bg_color   = 'success';
        }
    }
    if ($key === 'status') {
        if ($value === 'opened') {
            $text_color = 'dark';
            $bg_color   = 'info';
        } elseif ($value === 'closed') {
            $text_color = 'light';
            $bg_color   = 'success';
        } elseif ($value === 'unnecessary') {
            $text_color = 'light';
            $bg_color   = 'secondary';
        } else {
            $text_color = 'dark';
            $bg_color   = 'warning';
        }
    }
    if ($key === 'installed') {
        if ($value === 0) {
            $text_color = 'light';
            $bg_color   = 'secondary';
        }
    }
    if ($key === 'upgrade') {
        if ($value === 1) {
            $text_color = 'dark';
            $bg_color   = 'warning';
        }
    }
    if ($key === 'filterable') {
        if ($value == 1) {
            $text_color = 'dark';
            $bg_color   = 'warning';
        } else {
            $text_color = 'light';
            $bg_color   = 'secondary';
        }
    }

    if ($key === 'kind' || $key === 'authority_id') {
        $text_color = 'dark';
        $bg_color   = 'info';
    }

    return 'rounded-pill text-' . $text_color . ' bg-' . $bg_color;
}

/**
 * Return the key for draft.
 *
 * @param int|null $type_id
 * @param int|null $entry_id
 *
 * @return string
 */
function app_draft_key($type_id = null, $entry_id = null)
{
    // 同じドメインに設置した別の freo2 と混ざらないよう、設置パスを含める（テスト中は、テスト用データベースのIDの下書きと混ざらないよう目印を付ける）
    $key = 'freo2_draft:' . $GLOBALS['config']['http_path'] . ':' . (app_test() ? 'test:' : '') . $_SESSION['auth']['user']['id'] . ':';

    // 型を指定しなければ、ユーザーの下書きすべてに共通する接頭辞を返す
    if ($type_id !== null) {
        $key .= $type_id . ':' . ($entry_id ? $entry_id : 'new');
    }

    return $key;
}

/**
 * Return the URL for uploaded file.
 *
 * @param string     $target
 * @param int|string $key
 * @param string     $name
 *
 * @return string
 */
function app_file_url($target, $key, $name)
{
    // 保存したファイル名は rawurlencode() 済みなので、URLではもう一度エンコードする（そのまま並べると、日本語のファイル名がデコードされて開けない）
    $name = rawurlencode($name);

    // プログラムを通して表示する（$key はエントリーのID、フィールドなら「<エントリーのID>_<フィールドのID>」）
    if ($GLOBALS['config']['storage_protect']) {
        return MAIN_FILE . '/storage/' . $target . '/' . $key . '/' . $name;
    }

    return $GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target'][$target] . $key . '/' . $name;
}

/**
 * Return the formatted file size.
 *
 * @param int $size
 *
 * @return string
 */
function app_filesize($size)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $index = 0;
    while ($size >= 1024 && $index < count($units) - 1) {
        $size /= 1024;
        $index++;
    }

    return number_format($size) . ' ' . $units[$index];
}

/**
 * Return the definitions of data.
 *
 * @return array
 */
function app_data()
{
    // 本体の定義（plugin が null）
    $data = [];
    foreach ($GLOBALS['config']['data'] as $code => $definition) {
        $data[$code] = $definition + ['plugin' => null];
    }

    // 有効なプラグインの定義（data を持たないプラグインは何も足さない。本体と同じコードは本体を優先する）
    if (isset($GLOBALS['plugin']) && is_array($GLOBALS['plugin'])) {
        foreach ($GLOBALS['plugin'] as $plugin_code => $plugin) {
            if (empty($plugin['data']) || !is_array($plugin['data'])) {
                continue;
            }
            foreach ($plugin['data'] as $code => $definition) {
                if (isset($data[$code]) || !is_array($definition)) {
                    continue;
                }
                $data[$code] = $definition + ['plugin' => $plugin_code];
            }
        }
    }

    return $data;
}

/**
 * Return the definition of data by type.
 *
 * @param string $type
 *
 * @return array|null
 */
function app_data_type($type)
{
    foreach (app_data() as $code => $definition) {
        if (isset($definition['type']) && $definition['type'] === $type) {
            return $definition + ['code' => $code];
        }
    }

    return null;
}

/**
 * Return the setting of data.
 *
 * @param array  $definition
 * @param string $key
 * @param mixed  $default
 *
 * @return mixed
 */
function app_data_setting($definition, $key, $default = null)
{
    // setting に無い項目は既定値（設定のキーを書いていない、または設定が保存されていない）
    if (!isset($definition['setting'][$key])) {
        return $default;
    }
    $name = $definition['setting'][$key];

    // 本体は本体の設定、プラグインはそのプラグインの設定から読む（保存されていなければ、プラグインの設定の初期値）
    if (empty($definition['plugin'])) {
        $setting = isset($GLOBALS['setting']) ? $GLOBALS['setting'] : [];
    } else {
        $plugin  = isset($GLOBALS['plugin'][$definition['plugin']]) ? $GLOBALS['plugin'][$definition['plugin']] : [];
        $setting = (isset($plugin['setting']) ? $plugin['setting'] : []) + (isset($plugin['setting_default']) ? $plugin['setting_default'] : []);
    }

    return array_key_exists($name, $setting) ? $setting[$name] : $default;
}
