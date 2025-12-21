<?php import('app/views/auth/header.php') ?>

        <main class="col-11 col-md-6 mx-auto my-4">
            <div class="mb-4 text-center">
                <h1 class="h3">
                    <?php h($GLOBALS['string']['heading_user']) ?>
                </h1>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header heading"><?php h($_view['title']) ?></div>
                <div class="card-body">
                    <?php e($GLOBALS['setting']['text_auth_modify_complete']) ?>
                    <?php if ($GLOBALS['authority']['power'] >= 1) : ?>
                    <p><a href="<?php t(MAIN_FILE) ?>/admin/home" class="btn btn-secondary">戻る</a><p>
                    <?php else : ?>
                    <p><a href="<?php t(MAIN_FILE) ?>/auth/home" class="btn btn-secondary">戻る</a><p>
                    <?php endif ?>
                </div>
            </div>
            <?php e($_view['widget_sets']['auth_page']) ?>
        </main>

<?php import('app/views/auth/footer.php') ?>
