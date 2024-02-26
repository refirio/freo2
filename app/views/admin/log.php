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
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">日時</th>
                                        <th class="text-nowrap">ユーザ名</th>
                                        <th class="text-nowrap d-none d-md-table-cell">名前</th>
                                        <th class="text-nowrap">IPアドレス</th>
                                        <th class="text-nowrap d-none d-md-table-cell">環境</th>
                                        <th class="text-nowrap">ログ</th>
                                        <th class="text-nowrap d-none d-md-table-cell">ページ</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="text-nowrap">日時</th>
                                        <th class="text-nowrap">ユーザ名</th>
                                        <th class="text-nowrap d-none d-md-table-cell">名前</th>
                                        <th class="text-nowrap">IPアドレス</th>
                                        <th class="text-nowrap d-none d-md-table-cell">環境</th>
                                        <th class="text-nowrap">ログ</th>
                                        <th class="text-nowrap d-none d-md-table-cell">ページ</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach ($_view['logs'] as $log) : list($environment, $browser, $os) = environment_useragent($log['agent']) ?>
                                    <tr>
                                        <td><?php h(localdate('Ymd', $log['created']) == localdate('Ymd') ? localdate('H:i:s', $log['created']) : localdate('Y-m-d', $log['created'])) ?></td>
                                        <td><?php h($log['user_username']) ?></td>
                                        <td class="d-none d-md-table-cell"><?php h($log['user_name']) ?></td>
                                        <td><?php h($log['ip']) ?></td>
                                        <td class="d-none d-md-table-cell"><span title="<?php t($log['agent']) ?>"><?php h($environment ? $environment : '-') ?></span></td>
                                        <td>
                                            <?php if ($log['model'] && $log['exec']) : ?>
                                                <?php h($log['model']) ?>テーブルに対して<?php h($log['exec']) ?>しました。
                                            <?php endif ?>
                                            <?php h($log['message']) ?>
                                        </td>
                                        <td class="d-none d-md-table-cell"><?php h($log['page']) ?></td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                            <?php if ($_view['log_page'] > 1) : ?>
                                <ul class="pagination" style="justify-content: flex-end;">
                                    <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/log?page=1" class="page-link">&laquo;</a></li>
                                    <?php for ($i = 1; $i <= $_view['log_page']; $i++) : ?>
                                    <li class="page-item<?php if ($i == $_GET['page']) : ?> active<?php endif ?>"><a href="<?php t(MAIN_FILE) ?>/admin/log?page=<?php t($i) ?>" class="page-link"><?php t($i) ?></a></li>
                                    <?php endfor ?>
                                    <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/log?page=<?php t(ceil($_view['log_count'] / $GLOBALS['config']['limits']['log'])) ?>" class="page-link">&raquo;</a></li>
                                </ul>
                            <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
