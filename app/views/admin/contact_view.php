<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-pencil-square"/></svg>
                            コミュニケーション
                        </h2>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    表示
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-2">日時</dt>
                                        <dd class="col-sm-10"><?php h($_view['contact']['created']) ?></dd>
                                        <?php if (!empty($_view['contact']['user_id'])) : ?>
                                        <dt class="col-sm-2">ユーザ名</dt>
                                        <dd class="col-sm-10"><?php h($_view['contact']['user_username']) ?></dd>
                                        <?php endif ?>
                                        <dt class="col-sm-2">名前</dt>
                                        <dd class="col-sm-10"><?php h($_view['contact']['name']) ?></dd>
                                        <dt class="col-sm-2">メールアドレス</dt>
                                        <dd class="col-sm-10"><?php h($_view['contact']['email']) ?></dd>
                                        <dt class="col-sm-2">お問い合わせ内容</dt>
                                        <dd class="col-sm-10"><?php h($_view['contact']['message']) ?></dd>
                                        <dt class="col-sm-2">状況</dt>
                                        <dd class="col-sm-10"><?php h($GLOBALS['config']['option']['contact']['status'][$_view['contact']['status']]) ?></dd>
                                        <dt class="col-sm-2">メモ</dt>
                                        <dd class="col-sm-10"><?php h($_view['contact']['memo']) ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
