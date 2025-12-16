<?php import('app/views/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-3 px-md-4">
                    <h2 class="h4 mb-3"><?php h($_view['title']) ?></h2>
                    <?php e($GLOBALS['setting']['text_comment_preview']) ?>
                    <form action="<?php t(MAIN_FILE) ?>/comment/preview" method="post">
                        <dl class="row">
                            <dt class="col-sm-2">お名前</dt>
                            <dd class="col-sm-10"><?php h($_view['comment']['name']) ?></dd>
                            <dt class="col-sm-2">URL</dt>
                            <dd class="col-sm-10"><?php if ($_view['comment']['url']) : ?><code class="text-dark"><?php h($_view['comment']['url']) ?></code><?php endif ?></dd>
                            <dt class="col-sm-2">コメント内容</dt>
                            <dd class="col-sm-10"><?php h($_view['comment']['message']) ?></dd>
                        </dl>
                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                        <div class="form-group mt-4">
                            <a href="<?php t(MAIN_FILE) ?><?php t($_view['entry']['type_code'] === 'entry' ? '/entry/detail/' : '/page/') ?><?php t($_view['entry']['code']) ?>?referer=preview" class="btn btn-secondary px-4">修正</a>
                            <button type="submit" class="btn btn-primary px-4">投稿</button>
                        </div>
                    </form>
                    <?php e($_view['widget_sets']['public_page']) ?>
                </main>

<?php import('app/views/footer.php') ?>
