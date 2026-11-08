<?php

import('app/services/update.php');

// 未実行のマイグレーション
$_view['update_targets'] = service_update_pending();

// タイトル
$_view['title'] = 'システムの更新';
