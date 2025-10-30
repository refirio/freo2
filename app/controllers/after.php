<?php

// ワンタイムトークン
$_view['token'] = token('create');

// プラグイン
if ($dh = opendir(MAIN_PATH . $GLOBALS['config']['plugin_path'])) {
    while (($entry = readdir($dh)) !== false) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        $target_dir = MAIN_PATH . $GLOBALS['config']['plugin_path'] . $entry . '/';

        if (!file_exists($target_dir . 'config.php')) {
            continue;
        }

        $controller_dir = $target_dir . 'app/controllers/';
        $view_dir       = $target_dir . 'app/views/';

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
} else {
    if (LOGGING_MESSAGE) {
        logging('message', 'Opendir error: ' . $GLOBALS['config']['plugin_path']);
    }

    error('Opendir error' . (DEBUG_LEVEL ? ': ' . $GLOBALS['config']['plugin_path'] : ''));
}
