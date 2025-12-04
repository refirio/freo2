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
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/comment">コメント管理</a></li>
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
                                        <dd class="col-sm-10"><?php h(localdate('Y/m/d H:i:s', $_view['comment']['created'])) ?></dd>
                                        <?php if (!empty($_view['comment']['user_id']) && !preg_match('/^DELETED /', $_view['comment']['user_username'])) : ?>
                                        <dt class="col-sm-2">ユーザー名</dt>
                                        <dd class="col-sm-10"><code class="text-dark"><?php h($_view['comment']['user_username']) ?></code></dd>
                                        <?php endif ?>
                                        <dt class="col-sm-2">名前</dt>
                                        <dd class="col-sm-10"><?php h($_view['comment']['name']) ?></dd>
                                        <dt class="col-sm-2">URL</dt>
                                        <dd class="col-sm-10"><?php if ($_view['comment']['url']) : ?><code class="text-dark"><?php h($_view['comment']['url']) ?></code><?php endif ?></dd>
                                        <dt class="col-sm-2">コメント内容</dt>
                                        <dd class="col-sm-10"><?php h($_view['comment']['message']) ?></dd>
                                        <dt class="col-sm-2">対象</dt>
                                        <dd class="col-sm-10">
                                            <?php if ($_view['comment']['type_code'] === 'entry') : ?>
                                            <a href="<?php t(MAIN_FILE) ?>/<?php t($_view['comment']['type_code']) ?>/detail/<?php t($_view['comment']['entry_code']) ?>">エントリー</a>
                                            <?php elseif ($_view['comment']['type_code'] === 'page') : ?>
                                            <a href="<?php t(MAIN_FILE) ?>/<?php t($_view['comment']['type_code']) ?>/<?php t($_view['comment']['entry_code']) ?>">ページ</a>
                                            <?php elseif ($_view['comment']['contact_id']) : ?>
                                            <a href="<?php t(MAIN_FILE) ?>/admin/contact_view?id=<?php t($_view['comment']['contact_id']) ?>">お問い合わせ</a>
                                            <?php endif ?>
                                        </dd>
                                        <?php if ($GLOBALS['setting']['comment_use_approve']) : ?>
                                        <dt class="col-sm-2">承認</dt>
                                        <dd class="col-sm-10"><span class="badge rounded-pill text-white bg-secondary"><?php h($GLOBALS['config']['option']['comment']['approved'][$_view['comment']['approved']]) ?></span></dd>
                                        <?php endif ?>
                                        <dt class="col-sm-2">メモ</dt>
                                        <dd class="col-sm-10"><?php h($_view['comment']['memo']) ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
