<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-list-ul"/></svg>
                            コンテンツ
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
                            <p>時系列の記事を管理します。</p>
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/entry_form" class="btn btn-primary">エントリー登録</a></p>
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                エントリーを登録しました。
                                <?php elseif ($_GET['ok'] === 'approve') : ?>
                                エントリーの承認を変更しました。
                                <?php elseif ($_GET['ok'] === 'delete') : ?>
                                エントリーを削除しました。
                                <?php endif ?>
                            </div>
                            <?php elseif (isset($_GET['warning'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['warning'] === 'approve') : ?>
                                承認対象が選択されていません。
                                <?php elseif ($_GET['warning'] === 'delete') : ?>
                                削除対象が選択されていません。
                                <?php endif ?>
                            </div>
                            <?php endif ?>

                            <form action="<?php t(MAIN_FILE) ?>/admin/entry_bulk" method="post" class="bulk">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="page" value="<?php t($_GET['page']) ?>">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap"><label><input type="checkbox" name="" value="" class="bulks"></label></th>
                                            <th class="text-nowrap d-none d-md-table-cell">コード</th>
                                            <th class="text-nowrap">タイトル</th>
                                            <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                            <?php if ($GLOBALS['setting']['entry_use_approve']) : ?>
                                            <th class="text-nowrap">承認</th>
                                            <?php endif ?>
                                            <th class="text-nowrap">公開</th>
                                            <?php if (!empty($_view['categories'])) : ?>
                                            <th class="text-nowrap d-none d-md-table-cell">カテゴリー</th>
                                            <?php endif ?>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th class="text-nowrap"><label><input type="checkbox" name="" value="" class="bulks"></label></th>
                                            <th class="text-nowrap d-none d-md-table-cell">コード</th>
                                            <th class="text-nowrap">タイトル</th>
                                            <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                            <?php if ($GLOBALS['setting']['entry_use_approve']) : ?>
                                            <th class="text-nowrap">承認</th>
                                            <?php endif ?>
                                            <th class="text-nowrap">公開</th>
                                            <?php if (!empty($_view['categories'])) : ?>
                                            <th class="text-nowrap d-none d-md-table-cell">カテゴリー</th>
                                            <?php endif ?>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php foreach ($_view['entries'] as $entry) : ?>
                                        <tr>
                                            <td><input type="checkbox" name="bulks[]" value="<?php h($entry['id']) ?>"<?php isset($_SESSION['bulk']['entry'][$entry['id']]) ? e('checked="checked"') : '' ?> class="bulk"></td>
                                            <td class="d-none d-md-table-cell"><code class="text-dark"><?php h(truncate($entry['code'], 50)) ?></code></td>
                                            <td><?php h(truncate($entry['title'], 50)) ?></td>
                                            <td class="d-none d-md-table-cell"><?php h(localdate('Ymd', $entry['datetime']) == localdate('Ymd') ? localdate('H:i:s', $entry['datetime']) : localdate('Y/m/d', $entry['datetime'])) ?></td>
                                            <?php if ($GLOBALS['setting']['entry_use_approve']) : ?>
                                            <td class="text-nowrap"><span class="badge <?php t(app_badge('approved', $entry['approved'])) ?>"><?php h($GLOBALS['config']['option']['entry']['approved'][$entry['approved']]) ?></span></td>
                                            <?php endif ?>
                                            <td class="text-nowrap"><span class="badge <?php t(app_badge('public', $entry['public'])) ?>"><?php h($GLOBALS['config']['option']['entry']['public'][$entry['public']]) ?></span></td>
                                            <?php if (!empty($_view['categories'])) : ?>
                                            <td class="d-none d-md-table-cell">
                                                <?php foreach ($entry['category_sets'] as $category_sets) : ?>
                                                <div class="text-nowrap"><?php h($category_sets['category_name']) ?></div>
                                                <?php endforeach ?>
                                            </td>
                                            <?php endif ?>
                                            <td><a href="<?php t(MAIN_FILE) ?>/admin/entry_form?id=<?php t($entry['id']) ?>" class="btn btn-primary btn-sm text-nowrap">編集</a></td>
                                        </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                                <p><input type="submit" value="一括削除" class="btn btn-danger"></p>
                                <?php if ($_view['entry_page'] > 1) : ?>
                                    <ul class="pagination d-flex justify-content-end">
                                        <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/entry?page=1" class="page-link">&laquo;</a></li>
                                        <?php for ($i = max(1, $_GET['page'] - floor($GLOBALS['setting']['number_width_admin_entry'] / 2)); $i <= min($_view['entry_page'], $_GET['page'] + floor($GLOBALS['setting']['number_width_admin_entry'] / 2)); $i++) : ?>
                                        <li class="page-item<?php if ($i == $_GET['page']) : ?> active<?php endif ?>"><a href="<?php t(MAIN_FILE) ?>/admin/entry?page=<?php t($i) ?>" class="page-link"><?php t($i) ?></a></li>
                                        <?php endfor ?>
                                        <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/entry?page=<?php t(ceil($_view['entry_count'] / $GLOBALS['setting']['number_limit_admin_entry'])) ?>" class="page-link">&raquo;</a></li>
                                    </ul>
                                <?php endif ?>
                            </form>
                        </div>
                    </div>
                    <?php e($_view['widget_sets']['admin_page']) ?>
                </main>

<?php import('app/views/admin/footer.php') ?>
