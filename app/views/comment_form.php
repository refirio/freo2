            <?php if ($_view['entry']['comment'] === 'opened' || ($_view['entry']['comment'] === 'user' && !empty($_SESSION['auth']['user']['id']))) : ?>
            <div id="comment_form">
                <h3 class="h4 mt-4"><?php h($GLOBALS['string']['heading_comment_form']) ?></h3>
                <?php e($GLOBALS['setting']['text_comment_form']) ?>

                <?php if (isset($_view['warnings'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php foreach ($_view['warnings'] as $warning) : ?>
                    <?php h($warning) ?>
                    <?php endforeach ?>
                </div>
                <?php endif ?>

                <form action="<?php t(MAIN_FILE) ?><?php t($_view['entry']['type_code'] === 'page' ? '/page/' : '/' . $_view['entry']['type_code'] . '/detail/') ?><?php t($_view['entry']['code']) ?>" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="exec" value="comment">
                    <input type="hidden" name="entry_id" value="<?php t($_view['entry']['id']) ?>">
                    <?php if (empty($_SESSION['auth']['user']['id'])) : ?>
                    <div class="form-group mb-2">
                        <label>お名前 <span class="badge bg-danger">必須</span></label>
                        <input type="text" name="name" value="<?php t($_view['comment']['name']) ?>" class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label>URL</label>
                        <input type="text" name="url" value="<?php t($_view['comment']['url']) ?>" class="form-control">
                    </div>
                    <?php else : ?>
                    <input type="hidden" name="name" value="<?php t($_view['comment']['name']) ?>">
                    <input type="hidden" name="url" value="<?php t($_view['comment']['url']) ?>">
                    <div class="form-group mb-2">
                        <label>お名前</label>
                        <input type="text" value="<?php t($_view['comment']['name']) ?>" readonly class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label>URL</label>
                        <input type="url" value="<?php t($_view['comment']['url']) ?>" readonly class="form-control">
                    </div>
                    <?php endif ?>
                    <div class="form-group mb-2">
                        <label>コメント内容 <span class="badge bg-danger">必須</span></label>
                        <textarea name="message" rows="10" cols="50" class="form-control"><?php t($_view['comment']['message']) ?></textarea>
                    </div>
                    <div class="form-group mt-4">
                        <?php if ($GLOBALS['config']['recaptcha_enable'] == true) : ?>
                        <?php recaptcha_input($GLOBALS['config']['recaptcha_site_key']) ?>
                        <?php endif ?>
                        <button type="submit" class="btn btn-primary px-4"><?php h($GLOBALS['string']['button_comment_confirm']) ?></button>
                    </div>
                </form>

                <?php if ($GLOBALS['config']['recaptcha_enable'] == true) : ?>
                <?php recaptcha_import($GLOBALS['config']['recaptcha_site_key']) ?>
                <?php endif ?>
            </div>
            <?php endif ?>
