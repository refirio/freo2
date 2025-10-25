<?php import('app/views/auth/header.php') ?>

        <main class="col-11 col-md-6 mx-auto my-4">
            <div class="mb-4 text-center">
                <h1 class="h3">
                    ユーザ情報
                </h1>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header heading"><?php h($_view['title']) ?></div>
                <div class="card-body">
                    <form action="<?php t(MAIN_FILE) ?>/auth/leave" method="post">
                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                        <div class="card-body">
                            <div class="form-group">
                                ユーザ情報の削除を行います。
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary px-4">進む</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <div class="my-4 text-center">
            <?php if ($GLOBALS['authority']['power'] >= 1) : ?>
            <a href="<?php t(MAIN_FILE) ?>/admin/">管理ページへ戻る</a>
            <?php else : ?>
            <a href="<?php t(MAIN_FILE) ?>/">トップページへ戻る</a>
            <?php endif ?>
        </div>

<?php import('app/views/auth/footer.php') ?>
