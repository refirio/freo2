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
                                <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'install') : ?>
                                プラグインをインストールしました。
                                <?php elseif ($_GET['ok'] === 'uninstall') : ?>
                                プラグインをアンインストールしました。
                                <?php elseif ($_GET['ok'] === 'enable') : ?>
                                プラグインを有効化しました。
                                <?php elseif ($_GET['ok'] === 'disable') : ?>
                                プラグインを無効化しました。
                                <?php endif ?>
                            </div>
                            <?php endif ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">コード</th>
                                        <th class="text-nowrap">名前</th>
                                        <th class="text-nowrap">バージョン</th>
                                        <th class="text-nowrap d-none d-md-table-cell">概要</th>
                                        <th class="text-nowrap">状態</th>
                                        <th class="text-nowrap">操作</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="text-nowrap">コード</th>
                                        <th class="text-nowrap">名前</th>
                                        <th class="text-nowrap">バージョン</th>
                                        <th class="text-nowrap d-none d-md-table-cell">概要</th>
                                        <th class="text-nowrap">状態</th>
                                        <th class="text-nowrap">操作</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach ($_view['plugins'] as $plugin) : ?>
                                    <tr>
                                        <td><?php h($plugin['code']) ?></td>
                                        <td><?php h($plugin['name']) ?></td>
                                        <td><?php h($plugin['version']) ?></td>
                                        <td class="d-none d-md-table-cell"><?php h($plugin['description']) ?></td>
                                        <td>
                                            <?php if (empty($plugin['installed'])) : ?>
                                                インストールされていません。
                                            <?php else : ?>
                                                <?php if (empty($plugin['enabled'])) : ?>
                                                    有効化されていません。
                                                <?php else : ?>
                                                    稼働しています。
                                                <?php endif ?>
                                            <?php endif ?>
                                        </td>
                                        <td><a href="<?php t(MAIN_FILE) ?>/admin/plugin_view?code=<?php t($plugin['code']) ?>" class="btn btn-primary">詳細</a></td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
