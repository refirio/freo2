<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-file-text"/></svg>
                            システム
                        </h2>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">ID</th>
                                        <th class="text-nowrap">名前</th>
                                        <th class="text-nowrap">バージョン</th>
                                        <th class="text-nowrap d-none d-md-table-cell">概要</th>
                                        <th class="text-nowrap">操作</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="text-nowrap">ID</th>
                                        <th class="text-nowrap">名前</th>
                                        <th class="text-nowrap">バージョン</th>
                                        <th class="text-nowrap d-none d-md-table-cell">概要</th>
                                        <th class="text-nowrap">操作</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach ($_view['plugins'] as $plugin) : ?>
                                    <tr>
                                        <td><?php h($plugin['id']) ?></td>
                                        <td><?php h($plugin['name']) ?></td>
                                        <td><?php h($plugin['version']) ?></td>
                                        <td class="d-none d-md-table-cell"><?php h($plugin['description']) ?></td>
                                        <td><a href="<?php t(MAIN_FILE) ?>/admin/plugin_view?id=<?php t($plugin['id']) ?>" class="btn btn-primary">詳細</a></td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
