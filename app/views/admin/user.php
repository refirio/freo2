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
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/user_form" class="btn btn-primary">ユーザー登録</a></p>
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                ユーザーを登録しました。
                                <?php elseif ($_GET['ok'] === 'delete') : ?>
                                ユーザーを削除しました。
                                <?php endif ?>
                            </div>
                            <?php elseif (isset($_GET['warning'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['warning'] === 'delete') : ?>
                                ユーザーが選択されていません。
                                <?php endif ?>
                            </div>
                            <?php endif ?>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">ユーザー名</th>
                                        <th class="text-nowrap">名前</th>
                                        <th class="text-nowrap d-none d-md-table-cell">メールアドレス</th>
                                        <th class="text-nowrap">権限</th>
                                        <th class="text-nowrap d-none d-md-table-cell">最終ログイン日時</th>
                                        <th class="text-nowrap">有効</th>
                                        <th class="text-nowrap">作業</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="text-nowrap">ユーザー名</th>
                                        <th class="text-nowrap">名前</th>
                                        <th class="text-nowrap d-none d-md-table-cell">メールアドレス</th>
                                        <th class="text-nowrap">権限</th>
                                        <th class="text-nowrap d-none d-md-table-cell">最終ログイン日時</th>
                                        <th class="text-nowrap">有効</th>
                                        <th class="text-nowrap">作業</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach ($_view['users'] as $user) : ?>
                                    <tr>
                                        <td><?php h($user['username']) ?></td>
                                        <td><?php h($user['name']) ?></td>
                                        <td class="d-none d-md-table-cell"><?php h($user['email']) ?><?php h($user['email_verified'] ? '' : '（未確認）') ?></td>
                                        <td>
                                            <?php if (empty($user['authority_id'])) : ?>
                                                -
                                            <?php else : ?>
                                                <?php h($_view['authority_sets'][$user['authority_id']]) ?>
                                            <?php endif ?>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <?php if (empty($user['loggedin'])) : ?>
                                                -
                                            <?php else : ?>
                                                <?php h(localdate('Ymd', $user['loggedin']) == localdate('Ymd') ? localdate('H:i:s', $user['loggedin']) : localdate('Y/m/d', $user['loggedin'])) ?>
                                            <?php endif ?>
                                        </td>
                                        <td><?php h($GLOBALS['config']['option']['user']['enabled'][$user['enabled']]) ?></td>
                                        <td><a href="<?php t(MAIN_FILE) ?>/admin/user_form?id=<?php t($user['id']) ?>" class="btn btn-primary">編集</a></td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                            <?php if ($_view['user_page'] > 1) : ?>
                                <ul class="pagination d-flex justify-content-end">
                                    <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/user?page=1" class="page-link">&laquo;</a></li>
                                    <?php for ($i = 1; $i <= $_view['user_page']; $i++) : ?>
                                    <li class="page-item<?php if ($i == $_GET['page']) : ?> active<?php endif ?>"><a href="<?php t(MAIN_FILE) ?>/admin/user?page=<?php t($i) ?>" class="page-link"><?php t($i) ?></a></li>
                                    <?php endfor ?>
                                    <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/user?page=<?php t(ceil($_view['user_count'] / $GLOBALS['config']['limit']['user'])) ?>" class="page-link">&raquo;</a></li>
                                </ul>
                            <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
