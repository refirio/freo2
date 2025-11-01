<?php

// ワンタイムトークン
$_view['token'] = token('create');

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
