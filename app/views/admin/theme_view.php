<?php import('app/views/admin/header.php') ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
            <h2 class="h3">
                <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-file-text"/></svg>
                システム
            </h2>
            <nav style="--bs-breadcrumb-divider: '>';">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/theme">テーマ管理</a></li>
                    <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                </ol>
            </nav>
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
                            <dd class="col-sm-10"><code class="text-dark"><?php h($_GET['code']) ?></code></dd>
                            <dt class="col-sm-2">名前</dt>
                            <dd class="col-sm-10"><?php h($GLOBALS['theme'][$_GET['code']]['name']) ?></dd>
                            <dt class="col-sm-2">バージョン</dt>
                            <dd class="col-sm-10"><code class="text-dark"><?php h($GLOBALS['theme'][$_GET['code']]['version']) ?></code></dd>
                            <dt class="col-sm-2">概要</dt>
                            <dd class="col-sm-10"><?php h($GLOBALS['theme'][$_GET['code']]['description']) ?></dd>
                            <?php if (isset($GLOBALS['theme'][$_GET['code']]['detail'])) : ?>
                            <dt class="col-sm-2">詳細</dt>
                            <dd class="col-sm-10"><?php h($GLOBALS['theme'][$_GET['code']]['detail']) ?></dd>
                            <?php endif ?>
                            <?php if (isset($GLOBALS['theme'][$_GET['code']]['author'])) : ?>
                            <dt class="col-sm-2">製作者</dt>
                            <dd class="col-sm-10"><?php h($GLOBALS['theme'][$_GET['code']]['author']) ?></dd>
                            <?php endif ?>
                            <?php if (isset($GLOBALS['theme'][$_GET['code']]['link'])) : ?>
                            <dt class="col-sm-2">URL</dt>
                            <dd class="col-sm-10"><a href="<?php t($GLOBALS['theme'][$_GET['code']]['link']) ?>" target="_blank"><?php h($GLOBALS['theme'][$_GET['code']]['link']) ?></a></dd>
                            <?php endif ?>
                            <dt class="col-sm-2">更新日</dt>
                            <dd class="col-sm-10"><?php h(localdate('Y/m/d', $GLOBALS['theme'][$_GET['code']]['updated'])) ?></dd>
                            <dt class="col-sm-2">状態</dt>
                            <dd class="col-sm-10">
                                <?php if (empty($_view['theme'])) : ?>
                                    <span class="badge <?php t(app_badge('installed', 0)) ?>">未インストール</span>
                                <?php else : ?>
                                    <?php if (version_compare($GLOBALS['theme'][$_GET['code']]['version'], $_view['theme']['version'], '>')) : ?>
                                        <span class="badge <?php t(app_badge('upgrade', 1)) ?>">要アップグレード</span>
                                    <?php elseif (empty($_view['theme']['enabled'])) : ?>
                                        <span class="badge <?php t(app_badge('enabled', 0)) ?>">無効</span>
                                    <?php else : ?>
                                        <span class="badge <?php t(app_badge('enabled', 1)) ?>">有効</span>
                                    <?php endif ?>
                                <?php endif ?>
                            </dd>
                        </dl>
                    </div>
                </div>
                <?php if (empty($_view['theme'])) : ?>
                <form action="<?php t(MAIN_FILE) ?>/admin/theme_post" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="code" value="<?php t($_GET['code']) ?>">
                    <input type="hidden" name="exec" value="install">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">
                            インストール
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-4 for-public">
                                テーマをインストールします。
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary px-4">インストール</button>
                            </div>
                        </div>
                    </div>
                </form>
                <?php else : ?>
                <?php if (version_compare($GLOBALS['theme'][$_GET['code']]['version'], $_view['theme']['version'], '>')) : ?>
                <form action="<?php t(MAIN_FILE) ?>/admin/theme_post" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="code" value="<?php t($_GET['code']) ?>">
                    <input type="hidden" name="exec" value="upgrade">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">
                            アップグレード
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-4 for-public">
                                テーマをアップグレードします。
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-warning px-4">アップグレード</button>
                            </div>
                        </div>
                    </div>
                </form>
                <?php endif ?>
                <?php if (!empty($_view['contents'])) : ?>
                <form action="<?php t(MAIN_FILE) ?>/admin/theme_post" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="code" value="<?php t($_GET['code']) ?>">
                    <input type="hidden" name="exec" value="setting">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">
                            設定
                        </div>
                        <div class="card-body">
                            <?php foreach ($_view['contents'] as $key => $data) : ?>
                            <div class="form-group mb-2">
                                <label class="fw-bold"><?php t($data['name']) ?><?php if ($data['explanation']) : ?> <span class="badge text-light bg-secondary" data-toggle="tooltip" title="<?php t($data['explanation']) ?>">？</span><?php endif ?><?php if ($data['required']) : ?> <span class="badge bg-danger">必須</span><?php endif ?></label>
                                <?php if ($data['type'] == 'text') : ?>
                                <input type="text" name="setting[<?php t($key) ?>]" size="30" value="<?php t($_view['setting_sets'][$key]) ?>" class="form-control">
                                <?php elseif ($data['type'] == 'number') : ?>
                                <input type="text" name="setting[<?php t($key) ?>]" size="30" value="<?php t($_view['setting_sets'][$key]) ?>" class="form-control" style="width: 100px;">
                                <?php elseif ($data['type'] == 'textarea') : ?>
                                <textarea name="setting[<?php t($key) ?>]" rows="5" cols="50" class="form-control"><?php t($_view['setting_sets'][$key]) ?></textarea>
                                <?php elseif ($data['type'] == 'boolean') : ?>
                                <div><label><input type="checkbox" name="setting[<?php t($key) ?>]" value="1" class="form-check-input"<?php $_view['setting_sets'][$key] ? e(' checked="checked"') : '' ?>> ON</label></div>
                                <?php elseif ($data['type'] == 'select') : ?>
                                <select name="setting[<?php t($key) ?>]" class="form-select" style="width: 200px;">
                                    <?php foreach ($data['kind'] as $kind_key => $kind_value) : ?>
                                    <option value="<?php t($kind_key) ?>"<?php $kind_key == $_view['setting_sets'][$key] ? e(' selected="selected"') : '' ?>><?php t($kind_value) ?></option>
                                    <?php endforeach ?>
                                </select>
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
                <?php if (empty($_view['theme']['enabled'])) : ?>
                <form action="<?php t(MAIN_FILE) ?>/admin/theme_post" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="code" value="<?php t($_GET['code']) ?>">
                    <input type="hidden" name="exec" value="enable">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">
                            有効化
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-4 for-public">
                                テーマを有効化します。
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-warning px-4">有効化</button>
                            </div>
                        </div>
                    </div>
                </form>
                <form action="<?php t(MAIN_FILE) ?>/admin/theme_post" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="code" value="<?php t($_GET['code']) ?>">
                    <input type="hidden" name="exec" value="uninstall">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">
                            アンインストール
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-4 for-public">
                                テーマをアンインストールします。
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-danger px-4">アンインストール</button>
                            </div>
                        </div>
                    </div>
                </form>
                <?php else : ?>
                <form action="<?php t(MAIN_FILE) ?>/admin/theme_post" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="code" value="<?php t($_GET['code']) ?>">
                    <input type="hidden" name="exec" value="disable">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">
                            無効化
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-4 for-public">
                                テーマを無効化します。
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
        <?php e($_view['widget_sets']['admin_page']) ?>
    </main>

<?php import('app/views/admin/footer.php') ?>
