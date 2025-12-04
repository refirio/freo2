<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-file-text"/></svg>
                            コミュニケーション
                        </h2>
                        <nav style="--bs-breadcrumb-divider: '>';">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/">ホーム</a></li>
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/contact">お問い合わせ管理</a></li>
                                <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    表示
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-2">日時</dt>
                                        <dd class="col-sm-10"><?php h(localdate('Y/m/d H:i:s', $_view['contact']['created'])) ?></dd>
                                        <?php if (!empty($_view['contact']['user_id']) && !preg_match('/^DELETED /', $_view['contact']['user_username'])) : ?>
                                        <dt class="col-sm-2">ユーザー名</dt>
                                        <dd class="col-sm-10"><code class="text-dark"><?php h($_view['contact']['user_username']) ?></code></dd>
                                        <?php endif ?>
                                        <dt class="col-sm-2">名前</dt>
                                        <dd class="col-sm-10"><?php h($_view['contact']['name']) ?></dd>
                                        <dt class="col-sm-2">メールアドレス</dt>
                                        <dd class="col-sm-10"><?php if ($_view['contact']['email']) : ?><code class="text-dark"><?php h($_view['contact']['email']) ?></code><?php endif ?></dd>
                                        <dt class="col-sm-2">お問い合わせ件名</dt>
                                        <dd class="col-sm-10"><?php h($_view['contact']['subject']) ?></dd>
                                        <dt class="col-sm-2">お問い合わせ内容</dt>
                                        <dd class="col-sm-10"><?php h($_view['contact']['message']) ?></dd>
                                        <dt class="col-sm-2">状況</dt>
                                        <dd class="col-sm-10"><span class="badge rounded-pill text-white bg-secondary"><?php h($GLOBALS['config']['option']['contact']['status'][$_view['contact']['status']]) ?></span></dd>
                                        <dt class="col-sm-2">メモ</dt>
                                        <dd class="col-sm-10"><?php h($_view['contact']['memo']) ?></dd>
                                    </dl>
                                </div>
                            </div>
                            <?php if (!empty($_view['contact']['user_id'])) : ?>
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    コメント
                                </div>
                                <div class="card-body">
                                    <p><a href="<?php t(MAIN_FILE) ?>/admin/comment_form?contact_id=<?php t($_view['contact']['id']) ?>" class="btn btn-primary">コメント登録</a></p>
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
                            <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
