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
                            <p>エントリーとページに追加する入力項目を管理します。</p>
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
                                削除対象が選択されていません。
                                <?php endif ?>
                            </div>
                            <?php endif ?>

                            <form action="<?php t(MAIN_FILE) ?>/admin/field_sort" method="post" id="sortable">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap">対象</th>
                                            <th class="text-nowrap">名前</th>
                                            <th class="text-nowrap d-none d-md-table-cell">種類</th>
                                            <th class="text-nowrap d-none d-md-table-cell">並び替え</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th class="text-nowrap">対象</th>
                                            <th class="text-nowrap">名前</th>
                                            <th class="text-nowrap d-none d-md-table-cell">種類</th>
                                            <th class="text-nowrap d-none d-md-table-cell">並び替え</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php foreach ($_view['fields'] as $field) : ?>
                                        <tr id="sort_<?php h($field['id']) ?>">
                                            <td><?php h($field['type_name']) ?></td>
                                            <td><?php h(truncate($field['name'], 50)) ?></td>
                                            <td class="text-nowrap d-none d-md-table-cell"><span class="badge <?php t(app_badge('kind', $field['kind'])) ?>"><?php h($GLOBALS['config']['option']['field']['kind'][$field['kind']]) ?></span></td>
                                            <td class="d-none d-md-table-cell"><span class="handle text-nowrap"><svg class="bi flex-shrink-0 me-1 mb-1" width="16" height="16"><use xlink:href="#symbol-arrow-down-up"/></svg></span></td>
                                            <td><a href="<?php t(MAIN_FILE) ?>/admin/field_form?id=<?php t($field['id']) ?>" class="btn btn-primary">編集</a></td>
                                        </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                    <?php e($_view['widget_sets']['admin_page']) ?>
                </main>

<?php import('app/views/admin/footer.php') ?>
