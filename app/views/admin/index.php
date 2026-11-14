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
                <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                <h3 class="h5">コンテンツ</h3>
                <div class="row">
                    <?php foreach ($_view['contents'] as $content) : ?>
                    <div class="col-md-3">
                        <div class="card shadow-sm mb-3">
                            <div class="card-header"><?php h($content['name']) ?>数</div>
                            <div class="card-body text-center">
                                <span class="fs-4"><?php if ($content['list'] !== null) : ?><a href="<?php t(MAIN_FILE) ?><?php t($content['list']) ?>"><?php h($content['count']) ?></a><?php else : ?><?php h($content['count']) ?><?php endif ?></span><?php if ($content['approve']) : ?> / 未承認 <?php h($content['other']) ?><?php else :?> / 非公開 <?php h($content['other']) ?><?php endif ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach ?>
                </div>
                <?php endif ?>
                <h3 class="h5">コミュニケーション</h3>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card shadow-sm mb-3">
                            <div class="card-header">お問い合わせ数</div>
                            <div class="card-body text-center">
                                <span class="fs-4"><a href="<?php t(MAIN_FILE) ?>/admin/contact"><?php h($_view['contact_count']) ?></a></span> / 未対応 <?php h($_view['contact_status_opened_count']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($GLOBALS['authority']['power'] >= 3) : ?>
                <h3 class="h5">システム</h3>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card shadow-sm mb-3">
                            <div class="card-header">ユーザー数</div>
                            <div class="card-body text-center">
                                <span class="fs-4"><a href="<?php t(MAIN_FILE) ?>/admin/user"><?php h($_view['user_count']) ?></a></span> / 無効 <?php h($_view['user_enabled_count']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif ?>
            </div>
        </div>
        <?php e($_view['widget_sets']['admin_home']) ?>
    </main>

<?php import('app/views/admin/footer.php') ?>
