<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-list-ul"/></svg>
                            コンテンツ
                        </h2>
                        <nav style="--bs-breadcrumb-divider: '>';">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/">ホーム</a></li>
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/media">メディア管理</a></li>
                                <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                        <?php if (empty($_view['medias'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                一括処理対象が選択されていません。
                            </div>
                            <p><a href="<?php t(MAIN_FILE) ?>/admin/media" class="btn btn-secondary px-4">戻る</a></p>
                        <?php else : ?>
                            <p>以下のメディアが削除されます。よろしければ削除ボタンを押してください。</p>

                            <form action="<?php t(MAIN_FILE) ?>/admin/media_delete" method="post" class="delete">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <?php foreach ($_view['medias'] as $media) : ?>
                                <input type="hidden" name="medias[]" value="<?php t($media) ?>">
                                <?php endforeach ?>
                                <div class="form-group my-4">
                                    <a href="<?php t(MAIN_FILE) ?>/admin/media" class="btn btn-secondary px-4">戻る</a>
                                    <button type="submit" class="btn btn-danger px-4">削除</button>
                                </div>
                            </form>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">ファイル名</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th class="text-nowrap">ファイル名</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php foreach ($_view['medias'] as $media) : ?>
                                    <tr>
                                        <td><?php h($media) ?></td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
