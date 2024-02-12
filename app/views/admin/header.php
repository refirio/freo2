<!DOCTYPE html>
<html>
    <head>
        <meta charset="<?php t(MAIN_CHARSET) ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php isset($_view['title']) ? h($_view['title'] . ' | ') : '' ?>管理者用 | <?php h($GLOBALS['setting']['title']) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('jquery.datetimepicker.min.css')) ?>" rel="stylesheet">
        <link href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('common.css')) ?>" rel="stylesheet">
        <link href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('admin.css')) ?>" rel="stylesheet">
        <link href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('upload.css')) ?>" rel="stylesheet">
        <?php isset($_view['link']) ? e($_view['link']) : '' ?>
    </head>
    <body>
        <header class="navbar sticky-top flex-md-nowrap p-0 shadow-sm">
            <h1 class="navbar-brand col-md-3 col-lg-2 me-0 text-center"><a href="<?php t(MAIN_FILE) ?>/admin/home"><?php h($GLOBALS['setting']['title']) ?></a></h1>
            <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="w-100 d-none d-md-block">
                <span class="px-2 float-end">
                    <button type="button" class="btn border position-relative" onclick="window.open('<?php t(MAIN_FILE) ?>/');">
                        <svg class="bi flex-shrink-0" width="20" height="20" style="margin: -6px 2px 0 0;"><use xlink:href="#symbol-box-arrow-up-right"/></svg>
                        <span class="d-none d-lg-inline">ユーザ側ページ</span>
                    </button>
                </span>
            </div>
            <div class="navbar-nav">
                <div class="nav-item text-nowrap">
                    <div class="user mx-2 mx-md-4">
                        <a class="dropdown-toggle px-0" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg class="bi flex-shrink-0" width="20" height="20" style="margin: -2px 2px 0 0;"><use xlink:href="#symbol-person-circle"/></svg>
                            <?php h($_view['_user']['name'] ? $_view['_user']['name'] : $_view['_user']['username']) ?>さん
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end mx-2" style="position: absolute;">
                            <li><a class="dropdown-item" href="<?php t(MAIN_FILE) ?>/admin/modify">ユーザ情報編集</a></li>
                            <li><a class="dropdown-item" href="<?php t(MAIN_FILE) ?>/admin/logout">ログアウト</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-fluid">
            <div class="row">
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="sidebar-sticky pt-3">
                        <h2 class="d-none">メニュー</h2>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link<?php if ($_REQUEST['_work'] == 'home') : ?> active<?php endif ?>" aria-current="page" href="<?php t(MAIN_FILE) ?>/admin/home">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-clipboard-data"/></svg>
                                    ホーム
                                </a>
                            </li>
                        </ul>

                        <h3 class="h6 d-flex justify-content-between align-items-center px-3 mt-4 mb-2">
                            <span>コンテンツ</span>
                        </h3>
                        <ul class="nav flex-column mb-2">
                            <li class="nav-item">
                                <a class="nav-link<?php if (preg_match('/^entry(_|$)/', $_REQUEST['_work'])) : ?> active<?php endif ?>" href="<?php t(MAIN_FILE) ?>/admin/entry">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-file-text"/></svg>
                                    記事管理
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if (preg_match('/^category(_|$)/', $_REQUEST['_work'])) : ?> active<?php endif ?>" href="<?php t(MAIN_FILE) ?>/admin/category">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-file-text"/></svg>
                                    カテゴリ管理
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if (preg_match('/^page(_|$)/', $_REQUEST['_work'])) : ?> active<?php endif ?>" href="<?php t(MAIN_FILE) ?>/admin/page">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-file-text"/></svg>
                                    ページ管理
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if (preg_match('/^menu(_|$)/', $_REQUEST['_work'])) : ?> active<?php endif ?>" href="<?php t(MAIN_FILE) ?>/admin/menu">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-file-text"/></svg>
                                    メニュー管理
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if (preg_match('/^widget(_|$)/', $_REQUEST['_work'])) : ?> active<?php endif ?>" href="<?php t(MAIN_FILE) ?>/admin/widget">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-file-text"/></svg>
                                    ウィジェット管理
                                </a>
                            </li>
                        </ul>

                        <h3 class="h6 d-flex justify-content-between align-items-center px-3 mt-4 mb-2">
                            <span>お問い合わせ</span>
                        </h3>
                        <ul class="nav flex-column mb-2">
                            <li class="nav-item">
                                <a class="nav-link<?php if (preg_match('/^contact(_|$)/', $_REQUEST['_work'])) : ?> active<?php endif ?>" href="<?php t(MAIN_FILE) ?>/admin/contact">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-file-text"/></svg>
                                    お問い合わせ管理
                                </a>
                            </li>
                        </ul>

                        <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                        <h3 class="h6 d-flex justify-content-between align-items-center px-3 mt-4 mb-2">
                            <span>システム</span>
                        </h6>
                        <ul class="nav flex-column mb-2">
                            <li class="nav-item">
                                <a class="nav-link<?php if ($_REQUEST['_work'] == 'setting' && $_GET['target'] == 'basis' && empty($_GET['id'])) : ?> active<?php endif ?>" href="<?php t(MAIN_FILE) ?>/admin/setting?target=basis">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-pencil-square"/></svg>
                                    基本設定
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($_REQUEST['_work'] == 'setting' && $_GET['target'] == 'page' && empty($_GET['id'])) : ?> active<?php endif ?>" href="<?php t(MAIN_FILE) ?>/admin/setting?target=page">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-pencil-square"/></svg>
                                    ページ設定
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($_REQUEST['_work'] == 'setting' && $_GET['target'] == 'mail' && empty($_GET['id'])) : ?> active<?php endif ?>" href="<?php t(MAIN_FILE) ?>/admin/setting?target=mail">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-pencil-square"/></svg>
                                    メール設定
                                </a>
                            </li>
                            <?php if ($GLOBALS['authority']['power'] >= 3) : ?>
                            <li class="nav-item">
                                <a class="nav-link<?php if (preg_match('/^user(_|$)/', $_REQUEST['_work'])) : ?> active<?php endif ?>" href="<?php t(MAIN_FILE) ?>/admin/user">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-file-text"/></svg>
                                    ユーザ管理
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?php if ($_REQUEST['_work'] == 'log') : ?> active<?php endif ?>" href="<?php t(MAIN_FILE) ?>/admin/log">
                                    <svg class="bi flex-shrink-0" width="16" height="16" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-file-text"/></svg>
                                    ログ一覧
                                </a>
                            </li>
                            <?php endif ?>
                        </ul>
                        <?php endif ?>
                    </div>
                </nav>
