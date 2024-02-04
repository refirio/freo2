<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h1 class="h3">
                            <svg class="bi flex-shrink-0" width="24" height="24" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-person-circle"/></svg>
                            ユーザ
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

                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    編集
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-3">ユーザ名</dt>
                                        <dd class="col-sm-9"><?php h($_view['user']['username']) ?></dd>
                                        <dt class="col-sm-3">パスワード</dt>
                                        <dd class="col-sm-9"><?php h(alt(str_repeat('*', strlen($_view['user']['password'])), '-')) ?></dd>
                                        <dt class="col-sm-3">名前</dt>
                                        <dd class="col-sm-9"><?php h(alt($_view['user']['name'], '-')) ?></dd>
                                        <dt class="col-sm-3">メールアドレス</dt>
                                        <dd class="col-sm-9"><?php h(alt($_view['user']['email'], '-')) ?></dd>
                                    </dl>
                                    <form action="<?php t(MAIN_FILE) ?>/admin/modify_preview" method="post">
                                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                        <div class="form-group mt-4">
                                            <a href="<?php t(MAIN_FILE) ?>/admin/modify?referer=preview" class="btn btn-secondary px-4">修正する</a>
                                            <button type="submit" class="btn btn-primary px-4">登録</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
