<?php

import('app/services/dashboard.php');

// ダッシュボードのボックスを取得（本体と、有効なプラグインの dashboard）
$_view['dashboard'] = service_dashboard_boxes($GLOBALS['authority']['power']);
