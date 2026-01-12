<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-pencil-square"/></svg>
                            コンテンツ
                        </h2>
                        <nav style="--bs-breadcrumb-divider: '>';">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/">ホーム</a></li>
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/page">ページ管理</a></li>
                                <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                        <?php if (isset($_POST['view']) && $_POST['view'] === 'preview') : ?>
                            <h3>確認</h3>
                            <dl>
                                <dt>タイトル</dt>
                                    <dd><?php h($_view['entry']['title']) ?></dd>
                                <dt>本文</dt>
                                    <dd><?php h($_view['entry']['text']) ?></dd>
                                <dt>画像</dt>
                                    <dd><img src="<?php t(MAIN_FILE) ?>/admin/file?_type=image&amp;target=entry&amp;key=pictures&amp;format=image&amp;index=0<?php $_view['entry']['id'] ? t('&id=' . $_view['entry']['id']) : '' ?>"></dd>
                                <dt>サムネイル</dt>
                                    <dd><img src="<?php t(MAIN_FILE) ?>/admin/file?_type=image&amp;target=entry&amp;key=thumbnail&amp;format=image<?php $_view['entry']['id'] ? t('&id=' . $_view['entry']['id']) : '' ?>"></dd>
                                <dt>公開</dt>
                                    <dd><?php h($GLOBALS['config']['option']['entry']['public'][$_view['entry']['public']]) ?></dd>
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

                            <form action="<?php t(MAIN_FILE) ?>/admin/page_form<?php $_view['entry']['id'] ? t('?id=' . $_view['entry']['id']) : '' ?>" method="post" class="register validate">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['entry']['id']) ?>">
                                <input type="hidden" name="type_id" value="<?php t($_view['type']['id']) ?>">
                                <input type="hidden" name="view" value="">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        登録
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">公開 <span class="badge bg-danger">必須</span></label>
                                            <select name="public" class="form-select" style="width: 200px;">
                                                <?php foreach ($GLOBALS['config']['option']['entry']['public'] as $key => $value) : ?>
                                                <option value="<?php t($key) ?>"<?php $key == $_view['entry']['public'] ? e(' selected="selected"') : '' ?>><?php t($value) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2 for-public">
                                            <label class="fw-bold">公開開始日時</label>
                                            <input type="text" name="public_begin" size="30" value="<?php t($_view['entry']['public_begin']) ?>" autocomplete="off" class="form-control" style="width: 200px;">
                                        </div>
                                        <div class="form-group mb-2 for-public">
                                            <label class="fw-bold">公開終了日時</label>
                                            <input type="text" name="public_end" size="30" value="<?php t($_view['entry']['public_end']) ?>" autocomplete="off" class="form-control" style="width: 200px;">
                                        </div>
                                        <div class="form-group mb-2 for-password">
                                            <label class="fw-bold">パスワード</label>
                                            <input type="text" name="password" size="30" value="<?php t($_view['entry']['password']) ?>" class="form-control">
                                        </div>
                                        <?php if (!empty($_view['attributes'])) : ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">属性</label>
                                            <div id="validate_attribute_sets">
                                                <?php foreach ($_view['attributes'] as $attribute) : ?>
                                                <label><input type="checkbox" name="attribute_sets[]" value="<?php t($attribute['id']) ?>" class="form-check-input"<?php in_array($attribute['id'], array_column($_view['entry']['attribute_sets'], 'attribute_id')) ? e(' checked="checked"') : '' ?>> <?php t($attribute['name']) ?></label><br>
                                                <?php endforeach ?>
                                            </div>
                                        </div>
                                        <?php endif ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">日時 <span class="badge bg-danger">必須</span></label>
                                            <input type="text" name="datetime" size="30" value="<?php t($_view['entry']['datetime']) ?>" autocomplete="off" class="form-control" style="width: 200px;">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">コード <span class="badge text-light bg-secondary" data-toggle="tooltip" title="ページのURLに使用されます。">？</span> <span class="badge bg-danger">必須</span></label>
                                            <input type="text" name="code" size="30" value="<?php t($_view['entry']['code']) ?>" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">タイトル <span class="badge bg-danger">必須</span></label>
                                            <input type="text" name="title" size="30" value="<?php t($_view['entry']['title']) ?>" class="form-control">
                                        </div>
                                        <?php if ($GLOBALS['setting']['page_text_type'] !== 'none') : ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">本文（<a href="#" data-bs-toggle="modal" data-bs-target="#mediaModal">メディア</a>）</label>
                                            <textarea name="text" rows="10" cols="50" class="form-control<?php if ($GLOBALS['setting']['page_text_type'] === 'wysiwyg') : ?> editor<?php endif ?>"><?php t($_view['entry']['text']) ?></textarea>
                                        </div>
                                        <?php endif ?>
                                        <?php if ($GLOBALS['setting']['page_use_pictures']) : ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">画像</label>
                                            <div class="uploads" id="pictures" data-upload="<?php t(MAIN_FILE) ?>/admin/file_upload?_type=json&amp;target=entry&amp;key=pictures&amp;format=image" data-show="<?php t(MAIN_FILE) ?>/admin/file?_type=image&amp;target=entry&amp;key=pictures&amp;format=image&amp;id=<?php t($_view['entry']['id']) ?>">
                                                <button type="button">ファイル選択</button>
                                                <input type="file" name="pictures[]" multiple>
                                                <div class="files pictures_result"></div>
                                            </div>
                                        </div>
                                        <?php endif ?>
                                        <?php if ($GLOBALS['setting']['entry_use_thumbnail']) : ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">サムネイル</label>
                                            <div class="upload" id="thumbnail" data-upload="<?php t(MAIN_FILE) ?>/admin/file_upload?_type=json&amp;target=entry&amp;key=thumbnail&amp;format=image">
                                                <button type="button">ファイル選択</button>
                                                <input type="file" name="thumbnail">
                                                <p><img src="<?php t(MAIN_FILE) ?>/admin/file?_type=image&amp;target=entry&amp;key=thumbnail&amp;format=image<?php $_view['entry']['id'] ? t('&id=' . $_view['entry']['id']) : '' ?>"></p>
                                                <ul>
                                                    <li><a href="<?php t(MAIN_FILE) ?>/admin/file_delete?target=entry&amp;key=thumbnail&amp;format=image<?php $_view['entry']['id'] ? t('&id=' . $_view['entry']['id']) : '' ?>" id="thumbnail_delete" class="remove token" data-token="<?php t($_view['token']) ?>">×</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <?php endif ?>
                                        <?php import('app/views/admin/field_set.php') ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">コメントの受付 <span class="badge bg-danger">必須</span></label>
                                            <select name="comment" class="form-select" style="width: 200px;">
                                                <?php foreach ($GLOBALS['config']['option']['entry']['comment'] as $key => $value) : ?>
                                                <option value="<?php t($key) ?>"<?php $key == $_view['entry']['comment'] ? e(' selected="selected"') : '' ?>><?php t($value) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                        <div class="form-group mt-4">
                                            <button type="button" class="btn btn-primary px-4 preview">確認</button>
                                            <button type="submit" class="btn btn-primary px-4">登録</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <?php if (!empty($_GET['id'])) : ?>
                            <?php if ($GLOBALS['setting']['page_use_approve'] && $GLOBALS['authority']['power'] >= 3) : ?>
                            <form action="<?php t(MAIN_FILE) ?>/admin/page_approve" method="post" class="approve">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['entry']['id']) ?>">
                                <?php if ($_view['entry']['approved'] === 1) : ?>
                                <input type="hidden" name="approved" value="0">
                                <?php else : ?>
                                <input type="hidden" name="approved" value="1">
                                <?php endif ?>
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        承認
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <?php if ($_view['entry']['approved'] === 1) : ?>
                                            <button type="submit" class="btn btn-warning px-4">未承認にする</button>
                                            <?php else : ?>
                                            <button type="submit" class="btn btn-warning px-4">承認済にする</button>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endif ?>
                            <form action="<?php t(MAIN_FILE) ?>/admin/page_delete" method="post" class="delete">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['entry']['id']) ?>">
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
                    <?php e($_view['widget_sets']['admin_page']) ?>
                </main>

<?php import('app/views/admin/footer.php') ?>
