<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php isset($_view['title']) ? h($_view['title'] . ' | ') : '' ?><?php h($_REQUEST['_work'] == 'index' ? '認証 | ' : 'マイページ | ') ?><?php h($GLOBALS['setting']['title']) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('common.css')) ?>" rel="stylesheet">
        <link href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css($_REQUEST['_work'] == 'index' ? 'login.css' : 'auth.css')) ?>" rel="stylesheet">
        <?php isset($_view['link']) ? e($_view['link']) : '' ?>
    </head>
    <body>
