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
                        <?php if (empty($_view['pages'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                一括処理対象が選択されていません。
                            </div>
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/page?page=<?php t($_POST['page']) ?>" class="btn btn-secondary">戻る</a></p>
                        <?php else : ?>
                            <ul>
                                <li>以下のページが削除されます。よろしければ削除ボタンを押してください。</li>
                            </ul>
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/page?page=<?php t($_POST['page']) ?>">戻る</a></p>

                            <form action="<?php t(MAIN_FILE) ?>/admin/page_delete" method="post" class="delete">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="page" value="<?php t($_POST['page']) ?>">
                                <?php foreach ($_view['page_bulks'] as $page_bulk) : ?>
                                <input type="hidden" name="list[]" value="<?php t($page_bulk) ?>">
                                <?php endforeach ?>
                                <p><button type="submit" class="btn btn-danger px-4">削除</button></p>
                            </form>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">ID</th>
                                        <th class="text-nowrap">タイトル</th>
                                        <th class="text-nowrap">コード</th>
                                        <th class="text-nowrap">公開</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="text-nowrap">ID</th>
                                        <th class="text-nowrap">タイトル</th>
                                        <th class="text-nowrap">コード</th>
                                        <th class="text-nowrap">公開</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach ($_view['pages'] as $page) : ?>
                                    <tr>
                                        <td><?php h($page['id']) ?></td>
                                        <td><?php h(truncate($page['title'], 50)) ?></td>
                                        <td><?php h(truncate($page['code'], 50)) ?></td>
                                        <td><?php h(localdate('Ymd', $page['datetime']) == localdate('Ymd') ? localdate('H:i:s', $page['datetime']) : localdate('Y-m-d', $page['datetime'])) ?></td>
                                        <td><?php h($GLOBALS['config']['options']['page']['publics'][$page['public']]) ?></td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
