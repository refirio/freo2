<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-pencil-square"/></svg>
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
                            <?php if (isset($_view['warnings'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php foreach ($_view['warnings'] as $warning) : ?>
                                <?php h($warning) ?>
                                <?php endforeach ?>
                            </div>
                            <?php endif ?>

                            <form action="<?php t(MAIN_FILE) ?>/admin/comment_form<?php $_view['comment']['id'] ? t('?id=' . $_view['comment']['id']) : '' ?><?php $_view['comment']['contact_id'] ? t('?contact_id=' . $_view['comment']['contact_id']) : '' ?>" method="post" class="register validate">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['comment']['id']) ?>">
                                <input type="hidden" name="contact_id" value="<?php t($_view['comment']['contact_id']) ?>">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        登録
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">日時</label>
                                            <input type="text" size="30" value="<?php t($_view['comment']['created']) ?>" readonly class="form-control">
                                        </div>
                                        <?php if (!empty($_GET['id'])) : ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">名前 <span class="badge bg-danger">必須</span></label>
                                            <input type="text" name="name" size="30" value="<?php t($_view['comment']['name']) ?>" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">URL</label>
                                            <input type="text" name="url" size="30" value="<?php t($_view['comment']['url']) ?>" class="form-control">
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
                                            <label class="fw-bold">コメント内容</label>
                                            <textarea name="message" rows="10" cols="50" class="form-control"><?php t($_view['comment']['message']) ?></textarea>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">メモ</label>
                                            <textarea name="memo" rows="10" cols="50" class="form-control"><?php t($_view['comment']['memo']) ?></textarea>
                                        </div>
                                        <div class="form-group mt-4">
                                            <button type="submit" class="btn btn-primary px-4">登録</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <?php if (!empty($_GET['id'])) : ?>
                            <?php if ($GLOBALS['setting']['comment_use_approve'] && $GLOBALS['authority']['power'] >= 3) : ?>
                            <form action="<?php t(MAIN_FILE) ?>/admin/comment_approve" method="post" class="approve">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['comment']['id']) ?>">
                                <?php if ($_view['comment']['approved'] === 1) : ?>
                                <input type="hidden" name="approved" value="0">
                                <?php else : ?>
                                <input type="hidden" name="approved" value="1">
                                <?php endif ?>
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        承認
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <?php if ($_view['comment']['approved'] === 1) : ?>
                                            <button type="submit" class="btn btn-warning px-4">未承認にする</button>
                                            <?php else : ?>
                                            <button type="submit" class="btn btn-warning px-4">承認済にする</button>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endif ?>

                            <form action="<?php t(MAIN_FILE) ?>/admin/comment_delete" method="post" class="delete">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['comment']['id']) ?>">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        削除
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-danger px-4">削除</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
