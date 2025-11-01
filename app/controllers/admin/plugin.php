<?php

// プラグインを取得
$plugins = model('select_plugins', [
    'order_by' => 'code, id',
]);

$plugin_sets = [];
foreach ($plugins as $plugin) {
    $plugin_sets[$plugin['code']] = $plugin;
}

// プラグインファイルを読み込み
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

        $plugin = $GLOBALS['plugin'][$entry];

        if (empty($plugin['code']) || empty($plugin['name']) || empty($plugin['version']) || empty($plugin['updated'])) {
            continue;
        }

        if (isset($plugin_sets[$entry])) {
            $plugin['installed'] = $plugin_sets[$entry]['version'];
            $plugin['enabled']   = $plugin_sets[$entry]['enabled'];
        } else {
            $plugin['installed'] = null;
            $plugin['enabled']   = 0;
        }

        $_view['plugins'][] = $plugin;
    }
} else {
    if (LOGGING_MESSAGE) {
        logging('message', 'Opendir error: ' . $GLOBALS['config']['plugin_path']);
    }

    error('Opendir error' . (DEBUG_LEVEL ? ': ' . $GLOBALS['config']['plugin_path'] : ''));
}

// タイトル
$_view['title'] = 'プラグイン管理';
