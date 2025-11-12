<?php

import('app/services/storage.php');

// メディアを取得
$_view['medias'] = service_storage_list($GLOBALS['config']['file_target']['media']);

// タイトル
$_view['title'] = 'メディア管理';
