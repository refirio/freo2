<?php import('app/views/auth/header.php') ?>

    <main class="col-11 col-md-6 mx-auto my-4">
        <div class="mb-4 text-center">
            <h1 class="h3">
                <?php h($GLOBALS['string']['heading_register']) ?>
            </h1>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header heading"><?php h($_view['title']) ?></div>
            <div class="card-body">
                <?php e($GLOBALS['setting']['text_auth_register_preview']) ?>

                <?php if (isset($_view['warnings'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php foreach ($_view['warnings'] as $warning) : ?>
                    <?php h($warning) ?>
                    <?php endforeach ?>
                </div>
                <?php endif ?>

                <dl class="row">
                    <dt class="col-sm-3">ユーザー名</dt>
                    <dd class="col-sm-9"><code class="text-dark"><?php h($_view['user']['username']) ?></code></dd>
                    <dt class="col-sm-3">パスワード</dt>
                    <dd class="col-sm-9"><code class="text-dark"><?php h(alt(str_repeat('*', strlen($_view['user']['password'])), '-')) ?></code></dd>
                    <dt class="col-sm-3">名前</dt>
                    <dd class="col-sm-9"><?php h(alt($_view['user']['name'], '-')) ?></dd>
                    <dt class="col-sm-3">メールアドレス</dt>
                    <dd class="col-sm-9"><code class="text-dark"><?php h(alt($_view['user']['email'], '-')) ?></code></dd>
                    <dt class="col-sm-3">URL</dt>
                    <dd class="col-sm-9"><?php if ($_view['user']['url']) : ?><code class="text-dark"><?php h(alt($_view['user']['url'], '-')) ?></code><?php endif ?></dd>
                    <dt class="col-sm-3">自己紹介</dt>
                    <dd class="col-sm-9"><?php h(alt($_view['user']['text'], '-')) ?></dd>
                </dl>
                <form action="<?php t(MAIN_FILE) ?>/auth/register_preview" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <div class="form-group mt-4">
                        <a href="<?php t(MAIN_FILE) ?>/auth/register?referer=preview" class="btn btn-secondary px-4">修正</a>
                        <button type="submit" class="btn btn-primary px-4"><?php h($GLOBALS['string']['button_auth_register']) ?></button>
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
