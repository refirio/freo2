<?php import('app/views/auth/header.php') ?>

        <main class="col-11 col-md-6 mx-auto my-4">
            <div class="mb-4 text-center">
                <h1 class="h3">
                    ユーザー登録
                </h1>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header heading"><?php h($_view['title']) ?></div>
                <div class="card-body">
                    <?php e($GLOBALS['setting']['text_auth_email_verified']) ?>
                    <p><a href="<?php t(MAIN_FILE) ?>/auth/" class="btn btn-secondary">戻る</a><p>
                </div>
            </div>
            <?php e($_view['widget_sets']['auth_page']) ?>
        </main>

<?php import('app/views/auth/footer.php') ?>
