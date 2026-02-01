<?php import('app/views/header.php') ?>

            <div id="contact">
                <h2 class="h3 mb-3"><?php h($_view['title']) ?></h2>
                <?php e($GLOBALS['setting']['text_contact_index']) ?>

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
                    <?php if (empty($_SESSION['auth']['user']['id'])) : ?>
                    <div class="form-group mb-2">
                        <label>お名前 <span class="badge bg-danger">必須</span></label>
                        <input type="text" name="name" value="<?php t($_view['contact']['name']) ?>" class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label>メールアドレス <span class="badge bg-danger">必須</span></label>
                        <input type="text" name="email" value="<?php t($_view['contact']['email']) ?>" class="form-control">
                    </div>
                    <?php else : ?>
                    <input type="hidden" name="name" value="<?php t($_view['contact']['name']) ?>">
                    <input type="hidden" name="email" value="<?php t($_view['contact']['email']) ?>">
                    <div class="form-group mb-2">
                        <label>お名前</label>
                        <input type="text" value="<?php t($_view['_user']['name']) ?>" readonly class="form-control">
                    </div>
                    <?php endif ?>
                    <div class="form-group mb-2">
                        <label>お問い合わせ件名 <span class="badge bg-danger">必須</span></label>
                        <input type="text" name="subject" value="<?php t($_view['contact']['subject']) ?>" class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label>お問い合わせ内容 <span class="badge bg-danger">必須</span></label>
                        <textarea name="message" rows="10" cols="50" class="form-control"><?php t($_view['contact']['message']) ?></textarea>
                    </div>
                    <div class="form-group mt-4">
                        <?php if ($GLOBALS['config']['recaptcha_enable'] == true) : ?>
                        <?php recaptcha_input($GLOBALS['config']['recaptcha_site_key']) ?>
                        <?php endif ?>
                        <button type="submit" class="btn btn-primary px-4"><?php h($GLOBALS['string']['button_auth_contact_confirm']) ?></button>
                    </div>
                </form>

                <?php if ($GLOBALS['config']['recaptcha_enable'] == true) : ?>
                <?php recaptcha_import($GLOBALS['config']['recaptcha_site_key']) ?>
                <?php endif ?>
            </div>

            <?php e($_view['widget_sets']['public_page']) ?>

<?php import('app/views/footer.php') ?>
