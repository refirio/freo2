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
                    <div class="card-body">
                        <p>本当にユーザ情報を削除してもよろしいですか？この操作は取り消すことができません。</p>
                        <form action="<?php t(MAIN_FILE) ?>/auth/leave_confirm" method="post">
                            <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                            <div class="form-group mt-4">
                                <a href="<?php t(MAIN_FILE) ?>/auth/leave" class="btn btn-secondary px-4">戻る</a>
                                <button type="submit" class="btn btn-primary px-4">削除</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>

<?php import('app/views/auth/footer.php') ?>
