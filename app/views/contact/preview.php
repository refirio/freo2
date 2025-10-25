<?php import('app/views/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-3 px-md-4">
                    <h2 class="h4 mb-3">Contact</h2>
                    <p>以下の内容で送信します。</p>
                    <form action="<?php t(MAIN_FILE) ?>/contact/preview" method="post">
                        <dl class="row">
                            <?php if (empty($_SESSION['auth']['user']['id'])) : ?>
                            <dt class="col-sm-2">お名前</dt>
                            <dd class="col-sm-10"><?php h($_view['contact']['name']) ?></dd>
                            <dt class="col-sm-2">メールアドレス</dt>
                            <dd class="col-sm-10"><?php h($_view['contact']['email']) ?></dd>
                            <?php else : ?>
                            <dt class="col-sm-2">お名前</dt>
                            <dd class="col-sm-10"><?php h($_view['_user']['name']) ?></dd>
                            <?php endif ?>
                            <dt class="col-sm-2">お問い合わせ内容</dt>
                            <dd class="col-sm-10"><?php h($_view['contact']['message']) ?></dd>
                        </dl>
                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                        <div class="form-group mt-4">
                            <a href="<?php t(MAIN_FILE) ?>/contact/?referer=preview" class="btn btn-secondary px-4">修正</a>
                            <button type="submit" class="btn btn-primary px-4">送信</button>
                        </div>
                    </form>
                </main>

<?php import('app/views/footer.php') ?>
