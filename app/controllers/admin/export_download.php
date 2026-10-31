<?php

import('app/services/export.php');

// 出力した ZIP ファイルを取得
if (empty($_SESSION['export']['token'])) {
    error('出力したファイルが見つかりません。');
}
$file = service_export_dir($_SESSION['export']['token']) . 'export.zip';
if (!is_file($file)) {
    error('出力したファイルが見つかりません。');
}

// ダウンロード（出力バッファがあると内容をすべてメモリに溜めるので、外してから流す）
header('Content-Type: application/zip');
header('Content-Length: ' . filesize($file));
header('Content-Disposition: attachment; filename="export_' . localdate('YmdHis') . '.zip"');

while (ob_get_level() > 0) {
    ob_end_clean();
}
readfile($file);
exit;
