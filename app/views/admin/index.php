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
                            <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                            <h3 class="h5">コンテンツ</h3>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card shadow-sm mb-3">
                                        <div class="card-header">エントリー数</div>
                                        <div class="card-body text-center">
                                            <span class="fs-4"><a href="<?php t(MAIN_FILE) ?>/admin/entry"><?php h($_view['entry_count']) ?></a></span><?php if ($GLOBALS['setting']['entry_use_approve']) : ?> / 未承認 <?php h($_view['entry_not_approved_count']) ?><?php else :?> / 非公開 <?php h($_view['page_not_approved_count']) ?><?php endif ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card shadow-sm mb-3">
                                        <div class="card-header">ページ数</div>
                                        <div class="card-body text-center">
                                            <span class="fs-4"><a href="<?php t(MAIN_FILE) ?>/admin/page"><?php h($_view['page_count']) ?></a></span><?php if ($GLOBALS['setting']['page_use_approve']) : ?> / 未承認 <?php h($_view['page_not_approved_count']) ?><?php else :?> / 非公開 <?php h($_view['page_public_none_count']) ?><?php endif ?>
                                        </div>
                                    </div>
                                </div>
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
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
