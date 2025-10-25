<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-file-text"/></svg>
                            コンテンツ
                        </h2>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/category_form" class="btn btn-primary">カテゴリ登録</a></p>
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                カテゴリを登録しました。
                                <?php elseif ($_GET['ok'] === 'sort') : ?>
                                カテゴリを並び替えました。
                                <?php elseif ($_GET['ok'] === 'delete') : ?>
                                カテゴリを削除しました。
                                <?php endif ?>
                            </div>
                            <?php elseif (isset($_GET['warning'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['warning'] === 'delete') : ?>
                                カテゴリが選択されていません。
                                <?php endif ?>
                            </div>
                            <?php endif ?>

                            <form action="<?php t(MAIN_FILE) ?>/admin/category_sort" method="post" id="sortable">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap d-none d-md-table-cell">コード</th>
                                            <th class="text-nowrap">名前</th>
                                            <th class="text-nowrap">並び替え</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th class="text-nowrap d-none d-md-table-cell">コード</th>
                                            <th class="text-nowrap">名前</th>
                                            <th class="text-nowrap">並び替え</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php foreach ($_view['categories'] as $category) : ?>
                                        <tr id="sort_<?php h($category['id']) ?>">
                                            <td class="d-none d-md-table-cell"><?php h(truncate($category['code'], 50)) ?></td>
                                            <td><?php h(truncate($category['name'], 50)) ?></td>
                                            <td><span class="handle text-nowrap">並び替え</span></td>
                                            <td><a href="<?php t(MAIN_FILE) ?>/admin/category_form?id=<?php t($category['id']) ?>" class="btn btn-primary">編集</a></td>
                                        </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
