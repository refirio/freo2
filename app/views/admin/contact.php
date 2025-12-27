<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-list-ul"/></svg>
                            コミュニケーション
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
                            <p>お問い合わせと対応内容を管理できます。</p>
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                お問い合わせを登録しました。
                                <?php elseif ($_GET['ok'] === 'delete') : ?>
                                お問い合わせを削除しました。
                                <?php endif ?>
                            </div>
                            <?php elseif (isset($_GET['warning'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['warning'] === 'delete') : ?>
                                削除対象が選択されていません。
                                <?php endif ?>
                            </div>
                            <?php endif ?>

                            <form action="<?php t(MAIN_FILE) ?>/admin/contact_bulk" method="post" class="bulk">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="page" value="<?php t($_GET['page']) ?>">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                                            <th class="text-nowrap"><label><input type="checkbox" name="" value="" class="bulks"></label></th>
                                            <?php endif ?>
                                            <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                            <th class="text-nowrap d-none d-md-table-cell">名前</th>
                                            <th class="text-nowrap">件名</th>
                                            <th class="text-nowrap">状況</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                                            <th class="text-nowrap"><label><input type="checkbox" name="" value="" class="bulks"></label></th>
                                            <?php endif ?>
                                            <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                            <th class="text-nowrap d-none d-md-table-cell">名前</th>
                                            <th class="text-nowrap">件名</th>
                                            <th class="text-nowrap">状況</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php foreach ($_view['contacts'] as $contact) : ?>
                                        <tr>
                                            <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                                            <td><input type="checkbox" name="bulks[]" value="<?php h($contact['id']) ?>"<?php isset($_SESSION['bulk']['contact'][$contact['id']]) ? e('checked="checked"') : '' ?> class="bulk"></td>
                                            <?php endif ?>
                                            <td class="d-none d-md-table-cell"><?php h(localdate('Ymd', $contact['created']) == localdate('Ymd') ? localdate('H:i:s', $contact['created']) : localdate('Y/m/d', $contact['created'])) ?></td>
                                            <td class="d-none d-md-table-cell">
                                                <?php if ($contact['user_id'] && !preg_match('/^DELETED /', $contact['user_username'])) : ?>
                                                <a href="<?php t(MAIN_FILE) ?>/admin/user_form?id=<?php t($contact['user_id']) ?>" class="text-dark"><code class="text-dark"><?php h(truncate($contact['user_username'], 50)) ?></code></a>
                                                <?php else : ?>
                                                <?php h(truncate($contact['name'], 50)) ?>
                                                <?php endif ?>
                                            </td>
                                            <td><?php h(truncate($contact['subject'], 50)) ?></td>
                                            <td><span class="badge <?php t(app_badge('status', $contact['status'])) ?>"><?php h(truncate($GLOBALS['config']['option']['contact']['status'][$contact['status']], 50)) ?></span></td>
                                            <td>
                                                <a href="<?php t(MAIN_FILE) ?>/admin/contact_view?id=<?php t($contact['id']) ?>" class="btn btn-primary text-nowrap">表示</a>
                                                <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                                                <a href="<?php t(MAIN_FILE) ?>/admin/contact_form?id=<?php t($contact['id']) ?>" class="btn btn-primary text-nowrap">編集</a>
                                                <?php endif ?>
                                            </td>
                                        </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                                <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                                <p><input type="submit" value="一括削除" class="btn btn-danger"></p>
                                <?php endif ?>
                                <?php if ($_view['contact_page'] > 1) : ?>
                                    <ul class="pagination d-flex justify-content-end">
                                        <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/contact?page=1" class="page-link">&laquo;</a></li>
                                        <?php for ($i = max(1, $_GET['page'] - floor($GLOBALS['setting']['number_width_admin_contact'] / 2)); $i <= min($_view['contact_page'], $_GET['page'] + floor($GLOBALS['setting']['number_width_admin_contact'] / 2)); $i++) : ?>
                                        <li class="page-item<?php if ($i == $_GET['page']) : ?> active<?php endif ?>"><a href="<?php t(MAIN_FILE) ?>/admin/contact?page=<?php t($i) ?>" class="page-link"><?php t($i) ?></a></li>
                                        <?php endfor ?>
                                        <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/contact?page=<?php t(ceil($_view['contact_count'] / $GLOBALS['setting']['number_limit_admin_contact'])) ?>" class="page-link">&raquo;</a></li>
                                    </ul>
                                <?php endif ?>
                            </form>
                        </div>
                    </div>
                    <?php e($_view['widget_sets']['admin_page']) ?>
                </main>

<?php import('app/views/admin/footer.php') ?>
