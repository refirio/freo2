<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-list-ul"/></svg>
                            コミュニケーション
                        </h2>
                        <nav style="--bs-breadcrumb-divider: '>';">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/">ホーム</a></li>
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/contact">お問い合わせ管理</a></li>
                                <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                        <?php if (empty($_view['contacts'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                一括削除対象が選択されていません。
                            </div>
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/contact?page=<?php t($_POST['page']) ?>" class="btn btn-secondary px-4">戻る</a></p>
                        <?php else : ?>
                            <p>以下のお問い合わせが削除されます。よろしければ削除ボタンを押してください。</p>

                            <form action="<?php t(MAIN_FILE) ?>/admin/contact_delete" method="post" class="delete">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="page" value="<?php t($_POST['page']) ?>">
                                <?php foreach ($_view['contact_bulks'] as $contact_bulk) : ?>
                                <input type="hidden" name="list[]" value="<?php t($contact_bulk) ?>">
                                <?php endforeach ?>
                                <div class="form-group my-4">
                                    <a href="<?php t(MAIN_FILE) ?>/admin/contact?page=<?php t($_POST['page']) ?>" class="btn btn-secondary px-4">戻る</a>
                                    <button type="submit" class="btn btn-danger px-4">削除</button>
                                </div>
                            </form>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">ID</th>
                                        <th class="text-nowrap">日時</th>
                                        <th class="text-nowrap">名前</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="text-nowrap">ID</th>
                                        <th class="text-nowrap">日時</th>
                                        <th class="text-nowrap">名前</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach ($_view['contacts'] as $contact) : ?>
                                    <tr>
                                        <td><?php h($contact['id']) ?></td>
                                        <td class="d-none d-md-table-cell"><?php h(localdate('Ymd', $contact['created']) == localdate('Ymd') ? localdate('H:i:s', $contact['created']) : localdate('Y/m/d', $contact['created'])) ?></td>
                                        <td><?php h(truncate($contact['name'], 50)) ?></td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
