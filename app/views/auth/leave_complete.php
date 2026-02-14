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
                <?php e($GLOBALS['setting']['text_auth_leave_complete']) ?>
                <p><a href="<?php t(MAIN_FILE) ?>/" class="btn btn-secondary"><?php h($GLOBALS['string']['text_goto_home']) ?></a><p>
            </div>
        </div>
        <?php e($_view['widget_sets']['auth_page']) ?>
    </main>

<?php import('app/views/auth/footer.php') ?>
