<?php import('app/views/header.php') ?>

            <div id="contact">
                <h2 class="h3 mb-3"><?php h($_view['title']) ?></h2>
                <?php e($GLOBALS['setting']['text_contact_preview']) ?>
                <form action="<?php t(MAIN_FILE) ?>/contact/preview" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <dl class="row">
                        <?php if (empty($_SESSION['auth']['user']['id'])) : ?>
                        <dt class="col-sm-3">お名前</dt>
                        <dd class="col-sm-9"><?php h($_view['contact']['name']) ?></dd>
                        <dt class="col-sm-3">メールアドレス</dt>
                        <dd class="col-sm-9"><?php h($_view['contact']['email']) ?></dd>
                        <?php else : ?>
                        <dt class="col-sm-3">お名前</dt>
                        <dd class="col-sm-9"><?php h($_view['_user']['name']) ?></dd>
                        <?php endif ?>
                        <dt class="col-sm-3">お問い合わせ件名</dt>
                        <dd class="col-sm-9"><?php h($_view['contact']['subject']) ?></dd>
                        <dt class="col-sm-3">お問い合わせ内容</dt>
                        <dd class="col-sm-9"><?php h($_view['contact']['message']) ?></dd>
                    </dl>
                    <div class="form-group mt-4">
                        <a href="<?php t(MAIN_FILE) ?>/contact/?referer=preview" class="btn btn-secondary px-4">修正</a>
                        <button type="submit" class="btn btn-primary px-4">送信</button>
                    </div>
                </form>
            </div>

            <?php e($_view['widget_sets']['public_page']) ?>

<?php import('app/views/footer.php') ?>
