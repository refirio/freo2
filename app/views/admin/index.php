<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0" width="24" height="24" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-clipboard-data"/></svg>
                            ホーム
                        </h2>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading">メニュー</div>
                        <div class="card-body">
                            <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                            <h3 class="h5">コンテンツ</h3>
                            <ul>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/entry">記事管理</a></li>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/page">ページ管理</a></li>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/category">カテゴリ管理</a></li>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/field">フィールド管理</a></li>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/menu">メニュー管理</a></li>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/widget">ウィジェット管理</a></li>
                            </ul>
                            <?php endif ?>
                            <h3 class="h5">お問い合わせ</h3>
                            <ul>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/contact">お問い合わせ管理</a></li>
                            </ul>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
