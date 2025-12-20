<?php

// テーマを取得
$themes = model('select_themes', [
    'order_by' => 'code, id',
]);

$theme_sets = [];
foreach ($themes as $theme) {
    $theme_sets[$theme['code']] = $theme;
}

// テーマファイルを読み込み
$_view['themes'] = [];
if ($dh = opendir(MAIN_PATH . $GLOBALS['config']['theme_path'])) {
    while (($entry = readdir($dh)) !== false) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        $target_dir = MAIN_PATH . $GLOBALS['config']['theme_path'] . $entry . '/';

        if (!file_exists($target_dir . 'config.php')) {
            continue;
        }

        import($target_dir . 'config.php');

        $theme = $GLOBALS['theme'][$entry];

        if (empty($theme['code']) || empty($theme['name']) || empty($theme['version']) || empty($theme['updated'])) {
            continue;
        }

        if (isset($theme_sets[$entry])) {
            $theme['installed'] = $theme_sets[$entry]['version'];
            $theme['enabled']   = $theme_sets[$entry]['enabled'];
        } else {
            $theme['installed'] = null;
            $theme['enabled']   = 0;
        }

        $_view['themes'][] = $theme;
    }
} else {
    if (LOGGING_MESSAGE) {
        logging('message', 'Opendir error: ' . $GLOBALS['config']['theme_path']);
    }

    error('Opendir error' . (DEBUG_LEVEL ? ': ' . $GLOBALS['config']['theme_path'] : ''));
}

// タイトル
$_view['title'] = 'テーマ管理';
