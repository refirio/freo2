<?php import('app/views/admin/header.php') ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
            <h2 class="h3">
                <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-clipboard-data"/></svg>
                ホーム
            </h2>
        </div>

        <div class="dashboard card shadow-sm mb-3">
            <div class="card-header heading">ダッシュボード</div>
            <div class="card-body">
                <?php if (DEBUG_LEVEL > 0) : ?>
                <div class="alert alert-warning">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    デバッグモードが有効になっています。運用環境では無効にすることを推奨します。
                </div>
                <?php endif ?>
                <?php if ($GLOBALS['setting']['admin_description']) : ?>
                <p><?php h($GLOBALS['setting']['admin_description']) ?></p>
                <?php endif ?>
                <?php foreach ($_view['dashboard'] as $group) : ?>
                <h3 class="h5"><?php h($group['name']) ?></h3>
                <div class="row">
                    <?php foreach ($group['boxes'] as $box) : ?>
                    <div class="col-md-3">
                        <div class="card shadow-sm mb-3">
                            <div class="card-header"><?php h($box['name']) ?>数</div>
                            <div class="card-body text-center">
                                <span class="fs-4"><?php if ($box['list'] !== null) : ?><a href="<?php t(MAIN_FILE) ?><?php t($box['list']) ?>"><?php h($box['count']) ?></a><?php else : ?><?php h($box['count']) ?><?php endif ?></span><?php if ($box['label'] !== null) : ?> / <?php h($box['label']) ?> <?php h($box['other']) ?><?php endif ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach ?>
                </div>
                <?php endforeach ?>
            </div>
        </div>
        <?php e($_view['widget_sets']['admin_home']) ?>
    </main>

<?php import('app/views/admin/footer.php') ?>
