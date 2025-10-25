<?php import('app/views/auth/header.php') ?>

        <main class="col-11 col-md-6 mx-auto my-4">
            <div class="mb-4 text-center">
                <h1 class="h3">
                    ユーザ登録
                </h1>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header heading"><?php h($_view['title']) ?></div>
                <div class="card-body">
                    <div class="card-body">
                        <div class="alert alert-success">
                            <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                            メールアドレス認証が完了しました。
                        </div>
                        <p><a href="<?php t(MAIN_FILE) ?>/auth/" class="btn btn-secondary">戻る</a><p>
                    </div>
                </div>
            </div>
        </main>

<?php import('app/views/auth/footer.php') ?>
