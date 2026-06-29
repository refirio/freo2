<!DOCTYPE html>
<html>
    <head>
        <meta charset="<?php t(MAIN_CHARSET) ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php isset($_view['title']) ? h($_view['title'] . ' | ') : '' ?>管理者用 | <?php h($GLOBALS['setting']['title']) ?></title>
        <?php e($_view['widget_sets']['admin_initial']) ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('jquery.datetimepicker.min.css')) ?>" rel="stylesheet">
        <link href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('common.css')) ?>" rel="stylesheet">
        <link href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('admin.css')) ?>" rel="stylesheet">
        <link href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('upload.css')) ?>" rel="stylesheet">
        <?php isset($_view['link']) ? e($_view['link']) : '' ?>
    </head>
    <body>
        <?php if ($_REQUEST['_type'] !== 'iframe') : ?>
        <header class="navbar sticky-top flex-md-nowrap p-0 shadow-sm">
            <h1 class="navbar-brand col-md-3 col-lg-2 me-0 pt-4 pt-md-3 pb-2 ps-3 ps-md-0 text-center"><a href="<?php t(MAIN_FILE) ?>/admin/"><?php h($GLOBALS['setting']['admin_title'] ? $GLOBALS['setting']['admin_title'] : $GLOBALS['setting']['title']) ?></a></h1>
            <button class="navbar-toggler position-absolute top-0 end-0 mt-3 me-1 d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="w-100 d-none d-md-block">
                <span class="px-2 float-end">
                    <button type="button" class="btn border position-relative" onclick="window.open('<?php t(MAIN_FILE) ?>/');">
                        <svg class="bi flex-shrink-0" width="20" height="20" style="margin: -6px 2px 0 0;"><use xlink:href="#symbol-box-arrow-up-right"/></svg>
                        <span class="d-none d-lg-inline">ユーザー側ページ</span>
                    </button>
                </span>
            </div>
            <div class="navbar-nav px-2 py-2 p-md-0 mt-2 mt-md-0">
                <div class="nav-item text-nowrap">
                    <div class="user mx-2 mx-md-4">
                        <a class="dropdown-toggle px-0 fw-bold" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg class="bi flex-shrink-0" width="20" height="20" style="margin: -2px 2px 0 0;"><use xlink:href="#symbol-person-circle"/></svg>
                            <?php h($_view['_user']['name'] ? $_view['_user']['name'] : $_view['_user']['username']) ?>さん
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end position-absolute mx-2">
                            <li><a class="dropdown-item" href="<?php t(MAIN_FILE) ?>/auth/modify">ユーザー情報編集</a></li>
                            <li><a class="dropdown-item" href="<?php t(MAIN_FILE) ?>/auth/logout">ログアウト</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-fluid">
            <div class="row">
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse top-0 bottom-0 start-0">
                    <div class="sidebar-sticky py-3">
                        <h2 class="d-none">メニュー</h2>
                        <?php foreach ($GLOBALS['menu_group']['admin'] as $menu_key => $menu_value) : if ($menu_value['show']) : ?>
                        <?php if ($menu_key != 'home') : ?>
                        <h3 class="h6 d-flex justify-content-between align-items-center px-3 mt-3">
                            <span><?php t($menu_value['name']) ?></span>
                        </h3>
                        <?php endif ?>
                        <ul class="nav flex-column mb-2">
                            <?php foreach ($GLOBALS['menu_contents']['admin'][$menu_key] as $work_key => $work_value) : if ($work_value['show']) : ?>
                            <li class="nav-item">
                                <a class="nav-link py-1 fw-bold<?php if (preg_match($work_value['active'], $_REQUEST['_work'])) : ?> active<?php endif ?>" href="<?php t(MAIN_FILE . $work_value['link']) ?>">
                                    <svg class="bi flex-shrink-0 me-1 mb-1" width="16" height="16"><use xlink:href="<?php t($work_value['icon']) ?>"/></svg>
                                    <?php t($work_value['name']) ?>
                                </a>
                            </li>
                            <?php endif; endforeach ?>
                        </ul>
                        <?php endif; endforeach ?>
                        <?php e($_view['widget_sets']['admin_menu']) ?>
                    </div>
                </nav>
        <?php endif ?>
