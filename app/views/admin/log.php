<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-list-ul"/></svg>
                            システム
                        </h2>
                        <div class="btn-toolbar align-middl">
                            <nav style="--bs-breadcrumb-divider: '>';">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/">ホーム</a></li>
                                    <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <p>システムの操作ログを表示します。</p>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">日時</th>
                                        <th class="text-nowrap">ユーザー名</th>
                                        <th class="text-nowrap d-none d-md-table-cell">名前</th>
                                        <th class="text-nowrap">IPアドレス</th>
                                        <th class="text-nowrap d-none d-md-table-cell">ログ</th>
                                        <th class="text-nowrap">作業</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="text-nowrap">日時</th>
                                        <th class="text-nowrap">ユーザー名</th>
                                        <th class="text-nowrap d-none d-md-table-cell">名前</th>
                                        <th class="text-nowrap">IPアドレス</th>
                                        <th class="text-nowrap d-none d-md-table-cell">ログ</th>
                                        <th class="text-nowrap">作業</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach ($_view['logs'] as $log) : ?>
                                    <tr>
                                        <td><?php h(localdate('Ymd', $log['created']) == localdate('Ymd') ? localdate('H:i:s', $log['created']) : localdate('Y/m/d', $log['created'])) ?></td>
                                        <td><code class="text-dark"><?php h($log['user_username']) ?></code></td>
                                        <td class="d-none d-md-table-cell"><?php h($log['user_name']) ?></td>
                                        <td><code class="text-dark"><?php h($log['ip']) ?></code></td>
                                        <td class="d-none d-md-table-cell">
                                            <?php if ($log['model'] && $log['exec']) : ?>
                                                <?php h($log['model']) ?>テーブルに対して<?php h($log['exec']) ?>しました。
                                            <?php endif ?>
                                            <?php h($log['message']) ?>
                                        </td>
                                        <td>
                                            <a href="<?php t(MAIN_FILE) ?>/admin/log_view?id=<?php t($log['id']) ?>" class="btn btn-primary btn-sm text-nowrap">表示</a>
                                        </td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                            <?php if ($_view['log_page'] > 1) : ?>
                                <ul class="pagination d-flex justify-content-end">
                                    <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/log?page=1" class="page-link">&laquo;</a></li>
                                    <?php for ($i = max(1, $_GET['page'] - floor($GLOBALS['setting']['number_width_admin_log'] / 2)); $i <= min($_view['log_page'], $_GET['page'] + floor($GLOBALS['setting']['number_width_admin_log'] / 2)); $i++) : ?>
                                    <li class="page-item<?php if ($i == $_GET['page']) : ?> active<?php endif ?>"><a href="<?php t(MAIN_FILE) ?>/admin/log?page=<?php t($i) ?>" class="page-link"><?php t($i) ?></a></li>
                                    <?php endfor ?>
                                    <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/log?page=<?php t(ceil($_view['log_count'] / $GLOBALS['setting']['number_limit_admin_log'])) ?>" class="page-link">&raquo;</a></li>
                                </ul>
                            <?php endif ?>
                        </div>
                    </div>
                    <?php e($_view['widget_sets']['admin_page']) ?>
                </main>

<?php import('app/views/admin/footer.php') ?>
