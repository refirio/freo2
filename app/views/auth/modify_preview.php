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
                    <?php if (isset($_view['warnings'])) : ?>
                    <ul class="warning">
                        <?php foreach ($_view['warnings'] as $warning) : ?>
                        <li><?php h($warning) ?></li>
                        <?php endforeach ?>
                    </ul>
                    <?php endif ?>

                    <dl class="row">
                        <dt class="col-sm-3">ユーザー名</dt>
                        <dd class="col-sm-9"><?php h($_view['user']['username']) ?></dd>
                        <dt class="col-sm-3">パスワード</dt>
                        <dd class="col-sm-9"><?php h(alt(str_repeat('*', strlen($_view['user']['password'])), '-')) ?></dd>
                        <dt class="col-sm-3">名前</dt>
                        <dd class="col-sm-9"><?php h(alt($_view['user']['name'], '-')) ?></dd>
                        <dt class="col-sm-3">メールアドレス</dt>
                        <dd class="col-sm-9"><?php h(alt($_view['user']['email'], '-')) ?></dd>
                        <dt class="col-sm-3">URL</dt>
                        <dd class="col-sm-9"><?php h(alt($_view['user']['url'], '-')) ?></dd>
                        <dt class="col-sm-3">自己紹介</dt>
                        <dd class="col-sm-9"><?php h(alt($_view['user']['text'], '-')) ?></dd>
                    </dl>
                    <form action="<?php t(MAIN_FILE) ?>/auth/modify_preview" method="post">
                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                        <div class="form-group mt-4">
                            <a href="<?php t(MAIN_FILE) ?>/auth/modify?referer=preview" class="btn btn-secondary px-4">修正</a>
                            <button type="submit" class="btn btn-primary px-4">登録</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

<?php import('app/views/auth/footer.php') ?>
