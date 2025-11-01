<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-pencil-square"/></svg>
                            システム
                        </h2>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    詳細
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-2">コード</dt>
                                        <dd class="col-sm-10"><?php h($_GET['code']) ?></dd>
                                        <dt class="col-sm-2">名前</dt>
                                        <dd class="col-sm-10"><?php h($GLOBALS['plugin'][$_GET['code']]['name']) ?></dd>
                                        <dt class="col-sm-2">概要</dt>
                                        <dd class="col-sm-10"><?php h($GLOBALS['plugin'][$_GET['code']]['description']) ?></dd>
                                        <?php if (isset($GLOBALS['plugin'][$_GET['code']]['detail'])) : ?>
                                        <dt class="col-sm-2">詳細</dt>
                                        <dd class="col-sm-10"><?php h($GLOBALS['plugin'][$_GET['code']]['detail']) ?></dd>
                                        <?php endif ?>
                                        <?php if (isset($GLOBALS['plugin'][$_GET['code']]['author'])) : ?>
                                        <dt class="col-sm-2">製作者</dt>
                                        <dd class="col-sm-10"><?php h($GLOBALS['plugin'][$_GET['code']]['author']) ?></dd>
                                        <?php endif ?>
                                        <?php if (isset($GLOBALS['plugin'][$_GET['code']]['link'])) : ?>
                                        <dt class="col-sm-2">URL</dt>
                                        <dd class="col-sm-10"><a href="<?php t($GLOBALS['plugin'][$_GET['code']]['link']) ?>" target="_blank"><?php h($GLOBALS['plugin'][$_GET['code']]['link']) ?></a></dd>
                                        <?php endif ?>
                                        <dt class="col-sm-2">バージョン</dt>
                                        <dd class="col-sm-10"><?php h($GLOBALS['plugin'][$_GET['code']]['version']) ?></dd>
                                        <dt class="col-sm-2">更新日</dt>
                                        <dd class="col-sm-10"><?php h(localdate('Y/m/d', $GLOBALS['plugin'][$_GET['code']]['updated'])) ?></dd>
                                    </dl>
                                </div>
                            </div>
                            <?php if (empty($_view['plugin'])) : ?>
                            <form action="<?php t(MAIN_FILE) ?>/admin/plugin_post" method="post">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="code" value="<?php t($_GET['code']) ?>">
                                <input type="hidden" name="exec" value="install">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        インストール
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-4 for-public">
                                            プラグインをインストールします。
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary px-4">インストール</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php else : ?>
                            <?php if (empty($_view['plugin']['enabled'])) : ?>
                            <form action="<?php t(MAIN_FILE) ?>/admin/plugin_post" method="post">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="code" value="<?php t($_GET['code']) ?>">
                                <input type="hidden" name="exec" value="enable">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        有効化
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-4 for-public">
                                            プラグインを有効化します。
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-warning px-4">有効化</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <form action="<?php t(MAIN_FILE) ?>/admin/plugin_post" method="post">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="code" value="<?php t($_GET['code']) ?>">
                                <input type="hidden" name="exec" value="uninstall">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        アンインストール
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-4 for-public">
                                            プラグインをアンインストールします。
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-danger px-4">アンインストール</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php else : ?>
                            <form action="<?php t(MAIN_FILE) ?>/admin/plugin_post" method="post">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="code" value="<?php t($_GET['code']) ?>">
                                <input type="hidden" name="exec" value="disable">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        無効化
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-4 for-public">
                                            プラグインを無効化します。
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-warning px-4">無効化</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endif ?>
                            <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
