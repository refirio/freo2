<?php

// タイトル
$_view['title'] = 'バージョン情報';

// Webサーバー
$server_version = $_SERVER['SERVER_SOFTWARE'];

if (preg_match('/^(\S+)/', $server_version, $matches)) {
    $server_version = str_replace('/', ' ', $matches[1]);
}

// PHP
$php_version = 'PHP ' . PHP_VERSION;

// データベース
$resource = db_query('SELECT VERSION() AS version;');
$results  = db_result($resource);

$database_version = $results[0]['version'];

if (preg_match('/^([^\-]+)\-MariaDB/', $database_version, $matches)) {
    $database_version = 'MariaDB ' . $matches[1];
} else {
    $database_version = 'MySQL ' . $database_version;
}

// バージョン情報
$_view['version_info'] = [
    'server_version'   => $server_version,
    'php_version'      => $php_version,
    'database_version' => $database_version,
];
