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
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                ウィジェットを登録しました。
                                <?php endif ?>
                            </div>
                            <?php endif ?>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap d-none d-md-table-cell">コード</th>
                                        <th class="text-nowrap">タイトル</th>
                                        <th class="text-nowrap">作業</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="text-nowrap d-none d-md-table-cell">コード</th>
                                        <th class="text-nowrap">タイトル</th>
                                        <th class="text-nowrap">作業</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach ($_view['widgets'] as $widget) : ?>
                                    <tr>
                                        <td class="d-none d-md-table-cell"><?php h(truncate($widget['code'], 50)) ?></td>
                                        <td><?php h(truncate($widget['title'], 50)) ?></td>
                                        <td><a href="<?php t(MAIN_FILE) ?>/admin/widget_form?id=<?php t($widget['id']) ?>" class="btn btn-primary">編集</a></td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
