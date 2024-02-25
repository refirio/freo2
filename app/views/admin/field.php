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
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/field_form" class="btn btn-primary">フィールド登録</a></p>
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                フィールドを登録しました。
                                <?php elseif ($_GET['ok'] === 'sort') : ?>
                                フィールドを並び替えました。
                                <?php elseif ($_GET['ok'] === 'delete') : ?>
                                フィールドを削除しました。
                                <?php endif ?>
                            </div>
                            <?php elseif (isset($_GET['warning'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['warning'] === 'delete') : ?>
                                フィールドが選択されていません。
                                <?php endif ?>
                            </div>
                            <?php endif ?>

                            <form action="<?php t(MAIN_FILE) ?>/admin/field_sort" method="post" id="sortable">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap">ID</th>
                                            <th class="text-nowrap">名前</th>
                                            <th class="text-nowrap">種類</th>
                                            <th class="text-nowrap">対象</th>
                                            <th class="text-nowrap">並び替え</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th class="text-nowrap">ID</th>
                                            <th class="text-nowrap">名前</th>
                                            <th class="text-nowrap">種類</th>
                                            <th class="text-nowrap">対象</th>
                                            <th class="text-nowrap">並び替え</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php foreach ($_view['fields'] as $field) : ?>
                                        <tr id="sort_<?php h($field['id']) ?>">
                                            <td><?php h($field['id']) ?></td>
                                            <td><?php h(truncate($field['name'], 50)) ?></td>
                                            <td><?php h($GLOBALS['config']['options']['field']['types'][$field['type']]) ?></td>
                                            <td><?php h($GLOBALS['config']['options']['field']['targets'][$field['target']]) ?></td>
                                            <td><span class="handle text-nowrap">並び替え</span></td>
                                            <td><a href="<?php t(MAIN_FILE) ?>/admin/field_form?id=<?php t($field['id']) ?>" class="btn btn-primary">編集</a></td>
                                        </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
