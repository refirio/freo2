<!DOCTYPE html>
<html>
    <head>
        <meta charset="<?php t(MAIN_CHARSET) ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php isset($_view['title']) ? h($_view['title'] . ' | ') : '' ?><?php h($GLOBALS['setting']['title']) ?></title>
        <meta name="description" content="<?php t($GLOBALS['setting']['description']) ?>">
        <?php e($_view['widget_sets']['public_initial']) ?>
        <link rel="alternate" href="<?php h($GLOBALS['config']['http_url']) ?>/entry/feed" type="application/rss+xml" title="RSS">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link rel="stylesheet" href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('common.css')) ?>">
        <link rel="stylesheet" href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('public.css')) ?>">
        <?php isset($_view['link']) ? e($_view['link']) : '' ?>
    </head>
    <body>
        <header class="navbar ms-3 pt-0 pb-1 m-3 me-1">
            <div class="container px-lg-4 px-0">
                <h1 class="mx-0 mx-md-3 my-1 my-md-0"><a href="<?php t(MAIN_FILE) ?>/"><?php h($GLOBALS['setting']['title']) ?></a></h1>
                <button class="navbar-toggler position-absolute top-0 end-0 d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </header>
        <nav id="sidebarMenu" class="mx-3 mb-4 pe-md-0 d-md-block sidebar collapse">
            <div class="container px-0 px-md-5 sidebar-sticky">
                <h2 class="d-block d-md-none"><?php h($GLOBALS['string']['heading_menu']) ?></h2>
                <ul class="list-group list-group-horizontal-lg">
                    <li class="list-group-item"><a href="<?php t(MAIN_FILE) ?>/"><?php h($GLOBALS['string']['menu_public_home']) ?></a></li>
                    <?php foreach ($_view['menus'] as $menu) : ?>
                    <li class="list-group-item"><a href="<?php h($menu['url']) ?>"><?php h($menu['title']) ?></a></li>
                    <?php endforeach ?>
                    <?php foreach ($GLOBALS['menu_contents']['public']['home'] as $work_key => $work_value) : if ($work_value['show']) : ?>
                    <li class="list-group-item">
                        <a class="<?php if (preg_match($work_value['active'], $_REQUEST['_work'])) : ?>fw-bold<?php endif ?>" href="<?php t(MAIN_FILE . $work_value['link']) ?>">
                            <?php t($work_value['name']) ?>
                        </a>
                    </li>
                    <?php endif; endforeach ?>
                </ul>
                <?php e($_view['widget_sets']['public_menu']) ?>
            </div>
        </nav>
        <main class="container px-3 px-md-5">
