<?php import('app/views/header.php') ?>

    <div id="comment">
        <h2 class="h3 mb-3"><?php h($_view['title']) ?></h2>
        <?php e($GLOBALS['setting']['text_comment_preview']) ?>
        <form action="<?php t(MAIN_FILE) ?>/comment/preview" method="post">
            <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
            <dl class="row">
                <dt class="col-sm-3">お名前</dt>
                <dd class="col-sm-9"><?php h($_view['comment']['name']) ?></dd>
                <dt class="col-sm-3">URL</dt>
                <dd class="col-sm-9"><?php if ($_view['comment']['url']) : ?><code class="text-dark"><?php h($_view['comment']['url']) ?></code><?php endif ?></dd>
                <dt class="col-sm-3">コメント内容</dt>
                <dd class="col-sm-9"><?php h($_view['comment']['message']) ?></dd>
            </dl>
            <div class="form-group mt-4">
                <a href="<?php t(MAIN_FILE) ?><?php t($_view['entry']['type_code'] === 'page' ? '/page/' : '/' . $_view['entry']['type_code'] . '/detail/') ?><?php t($_view['entry']['code']) ?>?referer=preview#comment_form" class="btn btn-secondary px-4">修正</a>
                <button type="submit" class="btn btn-primary px-4"><?php h($GLOBALS['string']['button_comment']) ?></button>
            </div>
        </form>
    </div>

    <?php e($_view['widget_sets']['public_page']) ?>

<?php import('app/views/footer.php') ?>
