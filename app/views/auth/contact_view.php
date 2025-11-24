<?php import('app/views/auth/header.php') ?>

        <main class="col-11 col-md-6 mx-auto my-4">
            <div class="mb-4 text-center">
                <h1 class="h3">
                    ユーザー情報
                </h1>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header heading"><?php h($_view['title']) ?></div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-2">日時</dt>
                        <dd class="col-sm-10"><?php h(localdate('Y/m/d H:i:s', $_view['contact']['created'])) ?></dd>
                        <dt class="col-sm-2">内容</dt>
                        <dd class="col-sm-10"><?php h($_view['contact']['message']) ?></dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    コメント
                </div>
                <div class="card-body">
                    <p><a href="<?php t(MAIN_FILE) ?>/auth/comment_form?contact_id=<?php t($_view['contact']['id']) ?>" class="btn btn-primary">コメント登録</a></p>
                    <?php if (!empty($_view['comments'])) : ?>
                    <div id="comment">
                        <?php foreach ($_view['comments'] as $comment) : ?>
                        <div class="comment">
                            <h4 class="h6 mt-4"><?php if ($comment['url']) : ?><a href="<?php t($comment['url']) ?>" target="_blank"><?php endif ?><?php h($comment['name']) ?><?php if ($comment['url']) : ?></a><?php endif ?>（<time datetime="<?php h(localdate('Y-m-d H:i:s', $comment['created'])) ?>"><?php h(localdate('Y/m/d H:i', $comment['created'])) ?></time>）</h4>
                            <p><?php h($comment['message']) ?></p>
                        </div>
                        <?php endforeach ?>
                    </div>
                    <?php endif ?>
                </div>
            </div>
        </main>
        <div class="my-4 text-center">
            <a href="<?php t(MAIN_FILE) ?>/auth/home">ホームに戻る</a>
        </div>

<?php import('app/views/auth/footer.php') ?>
