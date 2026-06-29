<?php import('app/views/admin/header.php') ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
            <h2 class="h3">
                <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-file-text"/></svg>
                システム
            </h2>
            <nav style="--bs-breadcrumb-divider: '>';">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/log">ログ</a></li>
                    <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                </ol>
            </nav>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header heading"><?php h($_view['title']) ?></div>
            <div class="card-body">
                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        表示
                    </div>
                    <div class="card-body">
                        <?php list($environment, $browser, $os) = environment_useragent($_view['log']['agent']) ?>
                        <dl class="row">
                            <dt class="col-sm-2">日時</dt>
                            <dd class="col-sm-10"><?php h(localdate('Y/m/d H:i:s', $_view['log']['created'])) ?></dd>
                            <dt class="col-sm-2">ユーザー名</dt>
                            <dd class="col-sm-10"><code class="text-dark"><?php h($_view['log']['user_username']) ?></code></dd>
                            <dt class="col-sm-2">名前</dt>
                            <dd class="col-sm-10"><?php h($_view['log']['user_name']) ?></dd>
                            <dt class="col-sm-2">IPアドレス</dt>
                            <dd class="col-sm-10"><code class="text-dark"><?php h($_view['log']['ip']) ?></code></dd>
                            <dt class="col-sm-2">環境</dt>
                            <dd class="col-sm-10"><span title="<?php t($_view['log']['agent']) ?>"><?php h($environment ? $environment : '-') ?></span></dd>
                            <dt class="col-sm-2">ログ</dt>
                            <dd class="col-sm-10">
                                <?php if ($_view['log']['model'] && $_view['log']['exec']) : ?>
                                    <?php h($_view['log']['model']) ?>テーブルに対して<?php h($_view['log']['exec']) ?>しました。
                                <?php endif ?>
                                <?php h($_view['log']['message']) ?>
                            </dd>
                            <dt class="col-sm-2">ページ</dt>
                            <dd class="col-sm-10"><code class="text-dark"><?php h($_view['log']['page']) ?></code></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <?php e($_view['widget_sets']['admin_page']) ?>
    </main>

<?php import('app/views/admin/footer.php') ?>
