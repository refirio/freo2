<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <?php if ($_REQUEST['_type'] !== 'iframe') : ?>
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
                    <?php endif ?>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <?php if ($_view['current_dir']) : ?>
                            <p>作業ディレクトリは <code><?php h($_view['current_dir']) ?></code> です。</p>
                            <?php endif ?>
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/media_form?directory=<?php t($_view['current_dir']) ?><?php t(empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']) ?>" class="btn btn-primary">メディア登録</a></p>
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                メディアを登録しました。
                                <?php elseif ($_GET['ok'] === 'delete') : ?>
                                メディアを削除しました。
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

                            <form action="<?php t(MAIN_FILE) ?>/admin/media_delete?directory=<?php t($_view['current_dir']) ?><?php t(empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']) ?>" method="post">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="confirm" value=1">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap"></th>
                                            <th class="text-nowrap">名前</th>
                                            <?php if ($_REQUEST['_type'] !== 'iframe') : ?>
                                            <th class="text-nowrap d-none d-md-table-cell">最終更新日時</th>
                                            <th class="text-nowrap d-none d-md-table-cell">ファイルサイズ</th>
                                            <?php endif ?>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th class="text-nowrap"></th>
                                            <th class="text-nowrap">名前</th>
                                            <?php if ($_REQUEST['_type'] !== 'iframe') : ?>
                                            <th class="text-nowrap d-none d-md-table-cell">最終更新日時</th>
                                            <th class="text-nowrap d-none d-md-table-cell">ファイルサイズ</th>
                                            <?php endif ?>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php if ($_view['parent_dir']) : ?>
                                        <tr>
                                            <td></td>
                                            <td><svg class="bi flex-shrink-0 me-1" width="16" height="16"><use xlink:href="#symbol-folder-fill"/></svg> <a href="<?php t(MAIN_FILE) ?>/admin/media?directory=<?php t($_view['parent_dir']) ?><?php t(empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']) ?>" class="text-decoration-none"><code class="text-dark">..</code></a></td>
                                            <?php if ($_REQUEST['_type'] !== 'iframe') : ?>
                                            <td class="d-none d-md-table-cell"></td>
                                            <td class="d-none d-md-table-cell"></td>
                                            <?php endif ?>
                                            <td></td>
                                        </tr>
                                        <?php endif ?>
                                        <?php foreach ($_view['medias'] as $media) : ?>
                                        <?php if ($media['type'] === 'directory') : ?>
                                        <tr>
                                            <td></td>
                                            <td><svg class="bi flex-shrink-0 me-1" width="16" height="16"><use xlink:href="#symbol-folder-fill"/></svg> <a href="<?php t(MAIN_FILE) ?>/admin/media?directory=<?php t($_view['current_dir'] ? $_view['current_dir'] . '/' : '') ?><?php t($media['name']) ?><?php t(empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']) ?>" class="text-decoration-none"><code class="text-dark"><?php h($media['name']) ?></code></a></td>
                                            <?php if ($_REQUEST['_type'] !== 'iframe') : ?>
                                            <td class="d-none d-md-table-cell"></td>
                                            <td class="d-none d-md-table-cell"></td>
                                            <?php endif ?>
                                            <td><a href="<?php t(MAIN_FILE) ?>/admin/media_form?type=directory&amp;directory=<?php t($_view['current_dir']) ?>&amp;name=<?php t($media['name']) ?><?php t(empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']) ?>" class="btn btn-primary<?php t($_REQUEST['_type'] === 'iframe' ? ' btn-sm' : '') ?> text-nowrap">編集</a></td>
                                        </tr>
                                        <?php else : ?>
                                        <tr>
                                            <td><input type="checkbox" name="medias[]" value="<?php t($media['name']) ?>"></td>
                                            <td><svg class="bi flex-shrink-0 me-1" width="16" height="16"><use xlink:href="#symbol-file-text"/></svg> <code class="text-dark"><?php h($media['name']) ?></code></td>
                                            <?php if ($_REQUEST['_type'] !== 'iframe') : ?>
                                            <td class="d-none d-md-table-cell"><?php h(localdate('Y/m/d H:i:s', $media['modified'])) ?></td>
                                            <td class="d-none d-md-table-cell text-end"><?php h(number_format($media['size'] / 1024)) ?>KB</td>
                                            <?php endif ?>
                                            <td>
                                                <?php if ($_REQUEST['_type'] === 'iframe') : ?>
                                                <button type="button" class="btn btn-primary btn-sm text-nowrap insert-media" data-url="<?php t(APP_STORAGE_URL . $GLOBALS['config']['file_target']['media'] . ($_GET['directory'] ? $_GET['directory'] . '/' : '') . $media['name']) ?>" data-name="<?php t($media['name']) ?>">挿入</button>
                                                <?php endif ?>
                                                <a href="<?php t(MAIN_FILE) ?>/admin/media_form?type=file&amp;directory=<?php t($_view['current_dir']) ?>&amp;name=<?php t($media['name']) ?><?php t(empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']) ?>" class="btn btn-primary<?php t($_REQUEST['_type'] === 'iframe' ? ' btn-sm' : '') ?> text-nowrap">編集</a>
                                            </td>
                                        </tr>
                                        <?php endif ?>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                                <p><input type="submit" value="一括削除" class="btn btn-danger"></p>
                            </form>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
