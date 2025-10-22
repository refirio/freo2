<?php import('app/views/auth/header.php') ?>

        <main class="col-6 mx-auto">
            <div class="mb-4 text-center">
                <h1 class="h3">
                    ユーザ情報
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

                    <form action="<?php t(MAIN_FILE) ?>/auth/modify" method="post" class="register validate">
                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                        <div class="card-body">
                            <div class="form-group mb-2">
                                <label class="fw-bold">ユーザ名 <span class="badge bg-danger">必須</span></label>
                                <input type="text" name="username" size="30" value="<?php t($_view['user']['username']) ?>" class="form-control">
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">パスワード（変更したい場合のみ入力）</label>
                                <input type="password" name="password" size="30" value="<?php t($_view['user']['password']) ?>" class="form-control">
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">パスワード確認（同じものをもう一度入力）</label>
                                <input type="password" name="password_confirm" size="30" value="<?php t($_view['user']['password']) ?>" class="form-control">
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">名前</label>
                                <input type="text" name="name" size="30" value="<?php t($_view['user']['name']) ?>" class="form-control">
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">メールアドレス <span class="badge bg-danger">必須</span></label>
                                <input type="text" name="email" size="30" value="<?php t($_view['user']['email']) ?>" class="form-control">
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary px-4">確認</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <div class="text-center">
            <?php if ($GLOBALS['authority']['power'] >= 1) : ?>
            <a href="<?php t(MAIN_FILE) ?>/admin/home">ホームに戻る</a>
            <?php else : ?>
            <a href="<?php t(MAIN_FILE) ?>/auth/home">ホームに戻る</a>
            <?php endif ?>
        </div>

<?php import('app/views/auth/footer.php') ?>
