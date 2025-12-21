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
                    <?php e($GLOBALS['setting']['text_auth_leave']) ?>
                    <form action="<?php t(MAIN_FILE) ?>/auth/leave" method="post">
                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary px-4">進む</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php e($_view['widget_sets']['auth_page']) ?>
        </main>
        <div class="my-4 text-center">
            <a href="<?php t(MAIN_FILE) ?>/auth/home"><?php h($GLOBALS['string']['text_goto_home']) ?></a>
        </div>

<?php import('app/views/auth/footer.php') ?>
