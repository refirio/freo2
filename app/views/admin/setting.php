<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h1 class="h3">
                            <svg class="bi flex-shrink-0" width="24" height="24" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-file-text"/></svg>
                            システム
                        </h1>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                設定を登録しました。
                                <?php endif ?>
                            </div>
                            <?php elseif (isset($_GET['warning'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php foreach ($_view['warnings'] as $warning) : ?>
                                <?php h($warning) ?>
                                <?php endforeach ?>
                            </div>
                            <?php endif ?>

                            <?php if (empty($_view['contents'])) : ?>
                            <ul>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/setting?target=basis">基本設定</a></li>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/setting?target=entry">記事設定</a></li>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/setting?target=page">ページ設定</a></li>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/setting?target=mail">メール設定</a></li>
                            </ul>
                            <?php else : ?>
                            <form action="<?php t(MAIN_FILE) ?>/admin/setting?target=<?php t($_GET['target']) ?>" method="post" class="register validate">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        設定
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($_view['contents'] as $key => $data) : ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold"><?php t($data['name']) ?><?php if ($data['required']) : ?> <span class="badge bg-danger">必須</span><?php endif ?></label>
                                            <?php if ($data['type'] == 'text') : ?>
                                            <input type="text" name="<?php t($key) ?>" size="30" value="<?php t($_view['setting_sets'][$key]) ?>" class="form-control">
                                            <?php elseif ($data['type'] == 'textarea') : ?>
                                            <textarea name="<?php t($key) ?>" rows="5" cols="50" class="form-control"><?php t($_view['setting_sets'][$key]) ?></textarea>
                                            <?php elseif ($data['type'] == 'boolean') : ?>
                                            <div><label><input type="checkbox" name="<?php t($key) ?>" value="1" class="form-check-input"<?php $_view['setting_sets'][$key] ? e(' checked="checked"') : '' ?>> ON</label></div>
                                            <?php endif ?>
                                        </div>
                                        <?php endforeach ?>
                                        <div class="form-group mt-4">
                                            <button type="submit" class="btn btn-primary px-4">登録</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
