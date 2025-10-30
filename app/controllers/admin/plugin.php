<?php

// プラグインを取得
$_view['plugins'] = [];
if ($dh = opendir(MAIN_PATH . $GLOBALS['config']['plugin_path'])) {
    while (($entry = readdir($dh)) !== false) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        $target_dir = MAIN_PATH . $GLOBALS['config']['plugin_path'] . $entry . '/';

        if (!file_exists($target_dir . 'config.php')) {
            continue;
        }

        import($target_dir . 'config.php');

        $_view['plugins'][] = $GLOBALS['plugin'][$entry];
    }
} else {
    if (LOGGING_MESSAGE) {
        logging('message', 'Opendir error: ' . $GLOBALS['config']['plugin_path']);
    }

    error('Opendir error' . (DEBUG_LEVEL ? ': ' . $GLOBALS['config']['plugin_path'] : ''));
}

// タイトル
$_view['title'] = 'プラグイン管理';
