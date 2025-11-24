<?php import('app/views/auth/header.php') ?>

        <main class="col-11 col-md-6 mx-auto my-4">
            <div class="mb-4 text-center">
                <h1 class="h3">
                    ユーザー情報
                </h1>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header heading"><?php h($_view['title']) ?></div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-2">日時</dt>
                        <dd class="col-sm-10"><?php h(localdate('Y/m/d H:i:s', $_view['contact']['created'])) ?></dd>
                        <dt class="col-sm-2">内容</dt>
                        <dd class="col-sm-10"><?php h($_view['contact']['message']) ?></dd>
                    </dl>
                </div>
            </div>
        </main>
        <div class="my-4 text-center">
            <?php if ($GLOBALS['authority']['power'] >= 1) : ?>
            <a href="<?php t(MAIN_FILE) ?>/admin/home">ホームに戻る</a>
            <?php else : ?>
            <a href="<?php t(MAIN_FILE) ?>/auth/home">ホームに戻る</a>
            <?php endif ?>
        </div>

<?php import('app/views/auth/footer.php') ?>
