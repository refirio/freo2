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
                        <?php if (empty($_view['entries'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                一括処理対象が選択されていません。
                            </div>
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/entry?page=<?php t($_POST['page']) ?>" class="btn btn-secondary">戻る</a></p>
                        <?php else : ?>
                            <ul>
                                <li>以下の記事が削除されます。よろしければ削除ボタンを押してください。</li>
                            </ul>
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/entry?page=<?php t($_POST['page']) ?>">戻る</a></p>

                            <form action="<?php t(MAIN_FILE) ?>/admin/entry_delete" method="post" class="delete">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="page" value="<?php t($_POST['page']) ?>">
                                <?php foreach ($_view['entry_bulks'] as $entry_bulk) : ?>
                                <input type="hidden" name="list[]" value="<?php t($entry_bulk) ?>">
                                <?php endforeach ?>
                                <p><button type="submit" class="btn btn-danger px-4">削除</button></p>
                            </form>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">コード</th>
                                        <th class="text-nowrap">タイトル</th>
                                        <th class="text-nowrap">日時</th>
                                        <th class="text-nowrap">公開</th>
                                        <th class="text-nowrap">カテゴリ</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="text-nowrap">コード</th>
                                        <th class="text-nowrap">タイトル</th>
                                        <th class="text-nowrap">日時</th>
                                        <th class="text-nowrap">公開</th>
                                        <th class="text-nowrap">カテゴリ</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach ($_view['entries'] as $entry) : ?>
                                    <tr>
                                        <td><?php h(truncate($entry['code'], 50)) ?></td>
                                        <td><?php h(truncate($entry['title'], 50)) ?></td>
                                        <td><?php h(localdate('Ymd', $entry['datetime']) == localdate('Ymd') ? localdate('H:i:s', $entry['datetime']) : localdate('Y-m-d', $entry['datetime'])) ?></td>
                                        <td><?php h($GLOBALS['config']['options']['entry']['publics'][$entry['public']]) ?></td>
                                        <td>
                                            <?php foreach ($entry['category_sets'] as $category_sets) : ?>
                                            <div class="text-nowrap"><?php h($category_sets['category_name']) ?></div>
                                            <?php endforeach ?>
                                        </td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
