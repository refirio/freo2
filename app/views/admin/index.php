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
                                            <span class="fs-4"><a href="<?php t(MAIN_FILE) ?>/admin/entry"><?php h($_view['entry_count']) ?></a></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card shadow-sm mb-3">
                                        <div class="card-header">ページ数</div>
                                        <div class="card-body text-center">
                                            <span class="fs-4"><a href="<?php t(MAIN_FILE) ?>/admin/page"><?php h($_view['page_count']) ?></a></span>
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
                                            <span class="fs-4"><a href="<?php t(MAIN_FILE) ?>/admin/contact"><?php h($_view['contact_count']) ?></a></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
