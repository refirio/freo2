<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h1 class="h3">
                            <svg class="bi flex-shrink-0" width="24" height="24" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-file-text"/></svg>
                            コンテンツ
                        </h1>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/page_form" class="btn btn-primary">ページ登録</a></p>
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                ページを登録しました。
                                <?php elseif ($_GET['ok'] === 'delete') : ?>
                                ページを削除しました。
                                <?php endif ?>
                            </div>
                            <?php elseif (isset($_GET['warning'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['warning'] === 'delete') : ?>
                                ページが選択されていません。
                                <?php endif ?>
                            </div>
                            <?php endif ?>

                            <form action="<?php t(MAIN_FILE) ?>/admin/page_bulk" method="post" class="bulk">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="page" value="<?php t($_GET['page']) ?>">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap"><label><input type="checkbox" name="" value="" class="bulks"></label></th>
                                            <th class="text-nowrap">コード</th>
                                            <th class="text-nowrap">タイトル</th>
                                            <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                            <th class="text-nowrap">公開</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th class="text-nowrap"><label><input type="checkbox" name="" value="" class="bulks"></label></th>
                                            <th class="text-nowrap">コード</th>
                                            <th class="text-nowrap">タイトル</th>
                                            <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                            <th class="text-nowrap">公開</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php foreach ($_view['entries'] as $entry) : ?>
                                        <tr>
                                            <td><input type="checkbox" name="bulks[]" value="<?php h($entry['id']) ?>"<?php isset($_SESSION['bulk']['entry'][$entry['id']]) ? e('checked="checked"') : '' ?> class="bulk"></td>
                                            <td><?php h(truncate($entry['code'], 50)) ?></td>
                                            <td><?php h(truncate($entry['title'], 50)) ?></td>
                                            <td class="d-none d-md-table-cell"><?php h(localdate('Ymd', $entry['datetime']) == localdate('Ymd') ? localdate('H:i:s', $entry['datetime']) : localdate('Y/m/d', $entry['datetime'])) ?></td>
                                            <td><?php h($GLOBALS['config']['options']['entry']['publics'][$entry['public']]) ?></td>
                                            <td><a href="<?php t(MAIN_FILE) ?>/admin/page_form?id=<?php t($entry['id']) ?>" class="btn btn-primary text-nowrap">編集</a></td>
                                        </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                                <p><input type="submit" value="一括削除" class="btn btn-danger"></p>
                            </form>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
