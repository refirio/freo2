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
        <link rel="stylesheet" href="<?php t($GLOBALS['config']['http_path']) ?>themes/sample/<?php t(loader_css('public.css')) ?>">
        <?php isset($_view['link']) ? e($_view['link']) : '' ?>
    </head>
    <body>
        <div class="container-xxl">
            <header class="navbar ms-3 pt-0 pb-1 mt-3 me-1">
                <h1 class="my-1 my-md-0"><a href="<?php t(MAIN_FILE) ?>/"><?php h($GLOBALS['setting']['title']) ?></a></h1>
                <button class="navbar-toggler position-absolute top-0 end-0 d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#globalMenu" aria-controls="globalMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </header>
            <div class="container-fluid mb-4">
                <div class="row">
                    <nav id="globalMenu" class="col-md-3 col-lg-2 mb-3 ps-3 pe-0 d-md-block sidebar collapse">
                        <div class="sidebar-sticky">
                            <h2 class="h3 mt-4 mb-3"><?php h($GLOBALS['string']['heading_menu']) ?></h2>
                            <?php if (!empty($_view['menus'])) : ?>
                            <ul>
                                <?php foreach ($_view['menus'] as $menu) : ?>
                                <li><a href="<?php h($menu['url']) ?>"><?php h($menu['title']) ?></a></li>
                                <?php endforeach ?>
                            </ul>
                            <?php endif ?>
                            <?php foreach ($GLOBALS['menu_group']['public'] as $menu_key => $menu_value) : if ($menu_value['show']) : ?>
                            <?php if ($menu_key != 'home') : ?>
                            <h3 class="h5 mb-3"><?php h($menu_value['name']) ?></h3>
                            <?php endif ?>
                            <ul>
                                <?php foreach ($GLOBALS['menu_contents']['public'][$menu_key] as $work_key => $work_value) : if ($work_value['show']) : ?>
                                <li>
                                    <a class="<?php if (preg_match($work_value['active'], $_REQUEST['_work'])) : ?>fw-bold<?php endif ?>" href="<?php t(MAIN_FILE . $work_value['link']) ?>">
                                        <?php h($work_value['name']) ?>
                                    </a>
                                </li>
                                <?php endif; endforeach ?>
                            </ul>
                            <?php endif; endforeach ?>
                            <?php e($_view['widget_sets']['public_menu']) ?>
                        </div>
                    </nav>
                    <main class="col-md-9 ms-sm-auto col-lg-10 px-3 px-md-4">
