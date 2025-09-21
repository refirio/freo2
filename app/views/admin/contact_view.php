<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h1 class="h3">
                            <svg class="bi flex-shrink-0" width="24" height="24" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-pencil-square"/></svg>
                            お問い合わせ
                        </h1>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-2">日時</dt>
                                <dd class="col-sm-10"><?php h($_view['contact']['created']) ?></dd>
                                <dt class="col-sm-2">名前</dt>
                                <dd class="col-sm-10"><?php h($_view['contact']['name']) ?></dd>
                                <dt class="col-sm-2">メールアドレス</dt>
                                <dd class="col-sm-10"><?php h($_view['contact']['email']) ?></dd>
                                <dt class="col-sm-2">お問い合わせ内容</dt>
                                <dd class="col-sm-10"><?php h($_view['contact']['message']) ?></dd>
                                <dt class="col-sm-2">メモ</dt>
                                <dd class="col-sm-10"><?php h($_view['contact']['memo']) ?></dd>
                            </dl>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
