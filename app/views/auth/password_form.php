<?php import('app/views/auth/header.php') ?>

        <main class="col-6 mx-auto">
            <div class="mb-4 text-center">
                <h1 class="h3">
                    パスワード再発行
                </h1>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header heading"><?php h($_view['title']) ?></div>
                <div class="card-body">
                    <?php if (isset($_view['warnings'])) : ?>
                    <div class="alert alert-danger">
                        <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                        <?php foreach ($_view['warnings'] as $warning) : ?>
                        <?php h($warning) ?>
                        <?php endforeach ?>
                    </div>
                    <?php endif ?>

                    <form action="<?php t(MAIN_FILE) ?>/auth/password_form" method="post" class="register validate">
                        <input type="hidden" name="key" value="<?php t($_view['key']) ?>">
                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                        <div class="card-body">
                            <div class="form-group mb-2">
                                <label class="fw-bold">暗証コード</label>
                                <input type="text" name="token_code" size="30" value="<?php t($_view['user']['token_code']) ?>" class="form-control">
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">パスワード</label>
                                <input type="password" name="password" size="30" value="<?php t($_view['user']['password']) ?>" class="form-control">
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">パスワード確認（同じものをもう一度入力）</label>
                                <input type="password" name="password_confirm" size="30" value="<?php t($_view['user']['password']) ?>" class="form-control">
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary px-4">登録</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <div class="text-center">
            <a href="<?php t(MAIN_FILE) ?>/">トップページへ戻る</a>
        </div>

<?php import('app/views/auth/footer.php') ?>
