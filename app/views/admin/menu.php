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
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/menu_form" class="btn btn-primary">メニュー登録</a></p>
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                メニューを登録しました。
                                <?php elseif ($_GET['ok'] === 'sort') : ?>
                                メニューを並び替えました。
                                <?php elseif ($_GET['ok'] === 'delete') : ?>
                                メニューを削除しました。
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

                            <form action="<?php t(MAIN_FILE) ?>/admin/menu_sort" method="post" id="sortable">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap">タイトル</th>
                                            <th class="text-nowrap d-none d-md-table-cell">URL</th>
                                            <th class="text-nowrap">有効</th>
                                            <th class="text-nowrap">並び替え</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th class="text-nowrap">タイトル</th>
                                            <th class="text-nowrap d-none d-md-table-cell">URL</th>
                                            <th class="text-nowrap">有効</th>
                                            <th class="text-nowrap">並び替え</th>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php foreach ($_view['menus'] as $menu) : ?>
                                        <tr id="sort_<?php h($menu['id']) ?>">
                                            <td><?php h(truncate($menu['title'], 50)) ?></td>
                                            <td class="d-none d-md-table-cell"><code class="text-dark"><?php h(truncate($menu['url'], 50)) ?></code></td>
                                            <td><span class="badge rounded-pill text-white bg-secondary"><?php h($GLOBALS['config']['option']['menu']['enabled'][$menu['enabled']]) ?></span></td>
                                            <td><span class="handle text-nowrap"><svg class="bi flex-shrink-0 me-1 mb-1" width="16" height="16"><use xlink:href="#symbol-arrow-down-up"/></svg></span></td>
                                            <td><a href="<?php t(MAIN_FILE) ?>/admin/menu_form?id=<?php t($menu['id']) ?>" class="btn btn-primary">編集</a></td>
                                        </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
