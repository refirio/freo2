<?php import('app/views/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <h2 class="h4 mb-3">Contact</h2>
                    <p>お問い合わせを承ります。</p>

                    <?php if (isset($_view['warnings'])) : ?>
                    <div class="alert alert-danger">
                        <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                        <?php foreach ($_view['warnings'] as $warning) : ?>
                        <?php h($warning) ?>
                        <?php endforeach ?>
                    </div>
                    <?php endif ?>

                    <form action="<?php t(MAIN_FILE) ?>/contact/" method="post" class="register validate">
                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                        <input type="hidden" name="view" value="">
                        <div class="form-group mb-2">
                            <label>お名前 <span class="badge bg-danger">必須</span></label>
                            <input type="text" name="name" value="<?php t($_view['contact']['name']) ?>" class="form-control">
                        </div>
                        <div class="form-group mb-2">
                            <label>メールアドレス <span class="badge bg-danger">必須</span></label>
                            <input type="text" name="email" value="<?php t($_view['contact']['email']) ?>" class="form-control">
                        </div>
                        <div class="form-group mb-2">
                            <label>お問い合わせ内容 <span class="badge bg-danger">必須</span></label>
                            <textarea name="message" rows="10" cols="50" class="form-control"><?php t($_view['contact']['message']) ?></textarea>
                        </div>
                        <div class="form-group mt-4">
                            <?php if ($GLOBALS['config']['recaptcha_enable'] == true) : ?>
                            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                            <?php endif ?>
                            <button type="submit" class="btn btn-primary px-4">確認画面へ</button>
                        </div>
                    </form>

                    <?php if ($GLOBALS['config']['recaptcha_enable'] == true) : ?>
                    <script src="https://www.google.com/recaptcha/api.js?render=<?php t($GLOBALS['config']['recaptcha_site_key']) ?>"></script>
                    <script>
                    grecaptcha.ready(function() {
                        grecaptcha.execute('<?php t($GLOBALS['config']['recaptcha_site_key']) ?>', {action: 'homepage'}).then(function(token) {
                            var recaptchaResponse = document.getElementById('g-recaptcha-response');
                            recaptchaResponse.value = token;
                        });
                    });
                    </script>
                    <?php endif ?>
                </main>

<?php import('app/views/footer.php') ?>
