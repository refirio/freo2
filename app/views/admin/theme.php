<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-list-ul"/></svg>
                            システム
                        </h2>
                        <nav style="--bs-breadcrumb-divider: '>';">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/">ホーム</a></li>
                                <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <p>テーマを管理します。</p>
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'install') : ?>
                                テーマをインストールしました。
                                <?php elseif ($_GET['ok'] === 'uninstall') : ?>
                                テーマをアンインストールしました。
                                <?php elseif ($_GET['ok'] === 'upgrade') : ?>
                                テーマをアップグレードしました。
                                <?php elseif ($_GET['ok'] === 'setting') : ?>
                                テーマの設定を更新しました。
                                <?php elseif ($_GET['ok'] === 'enable') : ?>
                                テーマを有効化しました。
                                <?php elseif ($_GET['ok'] === 'disable') : ?>
                                テーマを無効化しました。
                                <?php endif ?>
                            </div>
                            <?php endif ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap d-none d-md-table-cell">コード</th>
                                        <th class="text-nowrap">名前</th>
                                        <th class="text-nowrap">バージョン</th>
                                        <th class="text-nowrap d-none d-md-table-cell">概要</th>
                                        <th class="text-nowrap">状態</th>
                                        <th class="text-nowrap">操作</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="text-nowrap d-none d-md-table-cell">コード</th>
                                        <th class="text-nowrap">名前</th>
                                        <th class="text-nowrap">バージョン</th>
                                        <th class="text-nowrap d-none d-md-table-cell">概要</th>
                                        <th class="text-nowrap">状態</th>
                                        <th class="text-nowrap">操作</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach ($_view['themes'] as $theme) : ?>
                                    <tr>
                                        <td class="d-none d-md-table-cell"><code class="text-dark"><?php h($theme['code']) ?></code></td>
                                        <td><?php h($theme['name']) ?></td>
                                        <td><code class="text-dark"><?php h($theme['version']) ?></code></td>
                                        <td class="d-none d-md-table-cell"><?php h($theme['description']) ?></td>
                                        <td>
                                            <?php if (empty($theme['installed'])) : ?>
                                                <span class="badge <?php t(app_badge('installed', 0)) ?>">未インストール</span>
                                            <?php else : ?>
                                                <?php if (version_compare($theme['version'], $theme['installed'], '>')) : ?>
                                                    <span class="badge <?php t(app_badge('upgrade', 1)) ?>">要アップグレード</span>
                                                <?php elseif (empty($theme['enabled'])) : ?>
                                                    <span class="badge <?php t(app_badge('enabled', 0)) ?>">無効</span>
                                                <?php else : ?>
                                                    <span class="badge <?php t(app_badge('enabled', 1)) ?>">有効</span>
                                                <?php endif ?>
                                            <?php endif ?>
                                        </td>
                                        <td><a href="<?php t(MAIN_FILE) ?>/admin/theme_view?code=<?php t($theme['code']) ?>" class="btn btn-primary">詳細</a></td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php e($_view['widget_sets']['admin_page']) ?>
                </main>

<?php import('app/views/admin/footer.php') ?>
