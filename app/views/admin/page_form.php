<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h1 class="h3">
                            <svg class="bi flex-shrink-0" width="24" height="24" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-pencil-square"/></svg>
                            コンテンツ
                        </h1>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                        <?php if (isset($_POST['view']) && $_POST['view'] === 'preview') : ?>
                            <h3>確認</h3>
                            <dl>
                                <dt>タイトル</dt>
                                    <dd><?php h($_view['page']['title']) ?></dd>
                                <dt>本文</dt>
                                    <dd><?php h($_view['page']['text']) ?></dd>
                                <dt>画像</dt>
                                    <dd><img src="<?php t(MAIN_FILE) ?>/admin/file?_type=image&amp;target=page&amp;key=picture&amp;format=image<?php $_view['page']['id'] ? t('&id=' . $_view['page']['id']) : '' ?>"></dd>
                                <dt>サムネイル</dt>
                                    <dd><img src="<?php t(MAIN_FILE) ?>/admin/file?_type=image&amp;target=page&amp;key=thumbnail&amp;format=image<?php $_view['page']['id'] ? t('&id=' . $_view['page']['id']) : '' ?>"></dd>
                                <dt>公開</dt>
                                    <dd><?php h($GLOBALS['config']['options']['page']['publics'][$_view['page']['public']]) ?></dd>
                            </dl>
                            <p><a href="#" class="close">閉じる</a></p>
                        <?php else : ?>
                            <?php if (isset($_view['warnings'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php foreach ($_view['warnings'] as $warning) : ?>
                                <?php h($warning) ?>
                                <?php endforeach ?>
                            </div>
                            <?php endif ?>

                            <form action="<?php t(MAIN_FILE) ?>/admin/page_form<?php $_view['page']['id'] ? t('?id=' . $_view['page']['id']) : '' ?>" method="post" class="register validate">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['page']['id']) ?>">
                                <input type="hidden" name="view" value="">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        登録
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">コード <span class="badge bg-danger">必須</span></label>
                                            <input type="text" name="code" size="30" value="<?php t($_view['page']['code']) ?>" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">公開 <span class="badge bg-danger">必須</span></label>
                                            <select name="public" class="form-select" style="width: 200px;">
                                                <?php foreach ($GLOBALS['config']['options']['page']['publics'] as $key => $value) : ?>
                                                <option value="<?php t($key) ?>"<?php $key == $_view['page']['public'] ? e(' selected="selected"') : '' ?>><?php t($value) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2 for-public">
                                            <label class="fw-bold">公開開始日時</label>
                                            <input type="text" name="public_begin" size="30" value="<?php t($_view['page']['public_begin']) ?>" autocomplete="off" class="form-control" style="width: 200px;">
                                        </div>
                                        <div class="form-group mb-2 for-public">
                                            <label class="fw-bold">公開終了日時</label>
                                            <input type="text" name="public_end" size="30" value="<?php t($_view['page']['public_end']) ?>" autocomplete="off" class="form-control" style="width: 200px;">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">日時 <span class="badge bg-danger">必須</span></label>
                                            <input type="text" name="datetime" size="30" value="<?php t($_view['page']['datetime']) ?>" autocomplete="off" class="form-control" style="width: 200px;">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">タイトル <span class="badge bg-danger">必須</span></label>
                                            <input type="text" name="title" size="30" value="<?php t($_view['page']['title']) ?>" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">本文</label>
                                            <textarea name="text" rows="10" cols="50" class="form-control editor"><?php t($_view['page']['text']) ?></textarea>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">画像</label>
                                            <div class="upload" id="picture" data-upload="<?php t(MAIN_FILE) ?>/admin/file_upload?_type=json&amp;target=page&amp;key=picture&amp;format=image">
                                                <button type="button">ファイル選択</button>
                                                <input type="file" name="picture">
                                                <p><img src="<?php t(MAIN_FILE) ?>/admin/file?_type=image&amp;target=page&amp;key=picture&amp;format=image<?php $_view['page']['id'] ? t('&id=' . $_view['page']['id']) : '' ?>"></p>
                                                <ul>
                                                    <li><a href="<?php t(MAIN_FILE) ?>/admin/file_delete?target=page&amp;key=picture&amp;format=image<?php $_view['page']['id'] ? t('&id=' . $_view['page']['id']) : '' ?>" id="picture_delete" class="token" data-token="<?php t($_view['token']) ?>">削除</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">サムネイル</label>
                                            <div class="upload" id="thumbnail" data-upload="<?php t(MAIN_FILE) ?>/admin/file_upload?_type=json&amp;target=page&amp;key=thumbnail&amp;format=image">
                                                <button type="button">ファイル選択</button>
                                                <input type="file" name="thumbnail">
                                                <p><img src="<?php t(MAIN_FILE) ?>/admin/file?_type=image&amp;target=page&amp;key=thumbnail&amp;format=image<?php $_view['page']['id'] ? t('&id=' . $_view['page']['id']) : '' ?>"></p>
                                                <ul>
                                                    <li><a href="<?php t(MAIN_FILE) ?>/admin/file_delete?target=page&amp;key=thumbnail&amp;format=image<?php $_view['page']['id'] ? t('&id=' . $_view['page']['id']) : '' ?>" id="thumbnail_delete" class="token" data-token="<?php t($_view['token']) ?>">削除</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="form-group mt-4">
                                            <button type="button" class="btn btn-primary px-4 preview">確認</button>
                                            <button type="submit" class="btn btn-primary px-4">登録</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <?php if (!empty($_GET['id'])) : ?>
                            <form action="<?php t(MAIN_FILE) ?>/admin/page_delete" method="post" class="delete">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['page']['id']) ?>">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        削除
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-danger px-4">削除</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endif ?>
                        <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
