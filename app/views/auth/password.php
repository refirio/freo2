<?php import('app/views/auth/header.php') ?>

        <main class="col-11 col-md-6 mx-auto my-4">
            <div class="mb-4 text-center">
                <h1 class="h3">
                    <?php h($GLOBALS['string']['heading_password']) ?>
                </h1>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header heading"><?php h($_view['title']) ?></div>
                <div class="card-body">
                    <?php e($GLOBALS['setting']['text_auth_password']) ?>

                    <?php if (isset($_view['warnings'])) : ?>
                    <div class="alert alert-danger">
                        <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                        <?php foreach ($_view['warnings'] as $warning) : ?>
                        <?php h($warning) ?>
                        <?php endforeach ?>
                    </div>
                    <?php endif ?>

                    <form action="<?php t(MAIN_FILE) ?>/auth/password" method="post" class="register validate">
                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                        <div class="form-group mb-2">
                            <label class="fw-bold">メールアドレス</label>
                            <input type="text" name="email" size="30" value="<?php t($_view['user']['email']) ?>" class="form-control">
                        </div>
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary px-4"><?php h($GLOBALS['string']['button_auth_password_confirm']) ?></button>
                        </div>
                    </form>
                </div>
            </div>
            <?php e($_view['widget_sets']['auth_page']) ?>
        </main>
        <div class="my-4 text-center">
            <a href="<?php t(MAIN_FILE) ?>/"><?php h($GLOBALS['string']['text_goto_home']) ?></a>
        </div>

<?php import('app/views/auth/footer.php') ?>
