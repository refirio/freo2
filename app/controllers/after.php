<?php

// ワンタイムトークン
$_view['token'] = token('create');

// HTML出力では、コメントを受け付けない（静的なページからは投稿できないため。承認済みのコメントは表示する）
if (app_export() && isset($_view['entry']['comment'])) {
    $_view['entry']['comment'] = 'closed';
}

// 入力中の内容の一時保存を削除（ログアウトの直後）
if (isset($_SESSION['draft_clear']) && (!isset($_REQUEST['_type']) || $_REQUEST['_type'] === 'html')) {
    $_view['script'] = ($_view['script'] ?? '') . '<script>
            try {
                var draftPrefix = ' . json_encode($_SESSION['draft_clear'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';
                for (var i = window.localStorage.length - 1; i >= 0; i--) {
                    if (window.localStorage.key(i).indexOf(draftPrefix) === 0) {
                        window.localStorage.removeItem(window.localStorage.key(i));
                    }
                }
            } catch (e) {
            }
        </script>' . "\n";

    unset($_SESSION['draft_clear']);
}

// プラグインを取得
$plugins = model('select_plugins', [
    'where'    => 'enabled = 1',
    'order_by' => 'code, id',
]);

// プラグインファイルを読み込み
foreach ($plugins as $plugin) {
    $target_dir = MAIN_PATH . $GLOBALS['config']['plugin_path'] . $plugin['code'] . '/';

    if (!file_exists($target_dir . 'config.php')) {
        continue;
    }

    $controller_dir = $target_dir . 'app/controllers/';

    if (isset($_params[0]) && is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/after_' . $_params[0] . '.php')) {
        import('app/controllers/after_' . $_params[0] . '.php');
    }

    if (is_file($controller_dir . 'after.php')) {
        import($controller_dir . 'after.php');
    }
    if (isset($_params[0]) && is_file($controller_dir . 'after_' . $_params[0] . '.php')) {
        import($controller_dir . 'after_' . $_params[0] . '.php');
    }

    if (is_file($target_dir . 'after.php')) {
        import($target_dir . 'after.php');
    }
}
