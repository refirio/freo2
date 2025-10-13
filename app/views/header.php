<!DOCTYPE html>
<html>
    <head>
        <meta charset="<?php t(MAIN_CHARSET) ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php isset($_view['title']) ? h($_view['title'] . ' | ') : '' ?><?php h($GLOBALS['setting']['title']) ?></title>
        <?php e($_view['widgets']['initial']) ?>
        <link rel="alternate" href="<?php h($GLOBALS['config']['http_url']) ?>/entry/feed" type="application/rss+xml" title="RSS">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link rel="stylesheet" href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('common.css')) ?>">
        <link rel="stylesheet" href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('pages.css')) ?>">
        <?php isset($_view['link']) ? e($_view['link']) : '' ?>
    </head>
    <body>
        <header class="navbar pt-0 pb-1 m-3">
            <h1><a href="<?php t(MAIN_FILE) ?>/"><?php h($GLOBALS['setting']['title']) ?></a></h1>
            <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </header>
        <div class="container-fluid">
            <div class="row">
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 mb-3 ps-3 pe-0 d-md-block sidebar collapse">
                    <div class="sidebar-sticky">
                        <h2 class="h4 mb-3">Menu</h2>
                        <?php if (!empty($_view['menus'])) : ?>
                        <ul>
                            <?php foreach ($_view['menus'] as $menu) : ?>
                            <li><a href="<?php h($menu['url']) ?>"><?php h($menu['title']) ?></a></li>
                            <?php endforeach ?>
                        </ul>
                        <?php endif ?>
                        <ul>
                            <li><a href="<?php t(MAIN_FILE) ?>/entry/">記事一覧</a></li>
                            <li><a href="<?php t(MAIN_FILE) ?>/contact/">お問い合わせ</a></li>
                            <li><a href="<?php t(MAIN_FILE) ?>/auth/">ログイン</a></li>
                        </ul>
                        <?php e($_view['widgets']['menu']) ?>
                    </div>
                </nav>
