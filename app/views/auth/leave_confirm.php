<?php import('app/views/auth/header.php') ?>

    <main class="col-11 col-md-7 mx-auto my-4">
        <div class="mb-4 text-center">
            <h1 class="h3">
                <a href="<?php t(MAIN_FILE) ?>/auth/home"><?php h($GLOBALS['string']['heading_user']) ?></a>
            </h1>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header heading"><?php h($_view['title']) ?></div>
            <div class="card-body">
                <?php e($GLOBALS['setting']['text_auth_leave_confirm']) ?>
                <form action="<?php t(MAIN_FILE) ?>/auth/leave_confirm" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <div class="form-group mt-4">
                        <a href="<?php t(MAIN_FILE) ?>/auth/leave" class="btn btn-secondary px-4">戻る</a>
                        <button type="submit" class="btn btn-primary px-4"><?php h($GLOBALS['string']['button_auth_leave']) ?></button>
                    </div>
                </form>
            </div>
        </div>
        <?php e($_view['widget_sets']['auth_page']) ?>
    </main>

<?php import('app/views/auth/footer.php') ?>
