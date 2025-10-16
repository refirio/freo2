<?php import('app/views/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <h2 class="h4 mb-3">Contact</h2>
                    <p>以下の内容で送信します。</p>
                    <form action="<?php t(MAIN_FILE) ?>/contact/preview" method="post">
                        <dl>
                            <dt>お名前</dt>
                                <dd><?php h($_view['contact']['name']) ?></dd>
                            <dt>メールアドレス</dt>
                                <dd><?php h($_view['contact']['email']) ?></dd>
                            <dt>お問い合わせ内容</dt>
                                <dd><?php h($_view['contact']['message']) ?></dd>
                        </dl>
                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                        <div class="form-group mt-4">
                            <a href="<?php t(MAIN_FILE) ?>/contact/?referer=preview" class="btn btn-secondary px-4">修正</a>
                            <button type="submit" class="btn btn-primary px-4">送信</button>
                        </div>
                    </form>
                </main>

<?php import('app/views/footer.php') ?>
