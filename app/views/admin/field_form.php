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
                    <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/field">フィールド管理</a></li>
                    <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                </ol>
            </nav>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header heading"><?php h($_view['title']) ?></div>
            <div class="card-body">
                <?php if (isset($_view['warnings'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php foreach ($_view['warnings'] as $warning) : ?>
                    <?php h($warning) ?>
                    <?php endforeach ?>
                </div>
                <?php endif ?>

                <form action="<?php t(MAIN_FILE) ?>/admin/field_form<?php $_view['field']['id'] ? t('?id=' . $_view['field']['id']) : '' ?>" method="post" class="register validate">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="id" value="<?php t($_view['field']['id']) ?>">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">
                            登録
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-2">
                                <label class="fw-bold">名前 <span class="badge bg-danger">必須</span></label>
                                <input type="text" name="name" size="30" value="<?php t($_view['field']['name']) ?>" class="form-control">
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">対象 <span class="badge bg-danger">必須</span></label>
                                <select name="type_id" class="form-select" style="width: 200px;">
                                    <option value=""></option>
                                    <?php foreach ($_view['types'] as $type) : ?>
                                    <option value="<?php t($type['id']) ?>"<?php $type['id'] == $_view['field']['type_id'] ? e(' selected="selected"') : '' ?>><?php t($type['name']) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">種類 <span class="badge bg-danger">必須</span></label>
                                <select name="kind" class="form-select" style="width: 200px;">
                                    <option value=""></option>
                                    <?php foreach ($GLOBALS['config']['option']['field']['kind'] as $key => $value) : ?>
                                    <option value="<?php t($key) ?>"<?php $key == $_view['field']['kind'] ? e(' selected="selected"') : '' ?>><?php t($value) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">バリデーション</label>
                                <select name="validation" class="form-select" style="width: 200px;">
                                    <?php foreach ($GLOBALS['config']['option']['field']['validation'] as $key => $value) : ?>
                                    <option value="<?php t($key) ?>"<?php $key == $_view['field']['validation'] ? e(' selected="selected"') : '' ?>><?php t($value) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="form-group mb-2 for-kind">
                                <label class="fw-bold">選択肢 <span class="badge text-light bg-secondary" data-toggle="tooltip" title="改行区切りで複数指定可能。">？</span></label>
                                <textarea name="choices" rows="5" cols="50" class="form-control"><?php t($_view['field']['choices']) ?></textarea>
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">初期値</label>
                                <textarea name="initial" rows="5" cols="50" class="form-control"><?php t($_view['field']['initial']) ?></textarea>
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">説明 <span class="badge text-light bg-secondary" data-toggle="tooltip" title="入力画面に表示する説明。">？</span></label>
                                <input type="text" name="explanation" size="30" value="<?php t($_view['field']['explanation']) ?>" class="form-control">
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">メモ <span class="badge text-light bg-secondary" data-toggle="tooltip" title="公開されないテキスト。">？</span></label>
                                <textarea name="memo" rows="10" cols="50" class="form-control"><?php t($_view['field']['memo']) ?></textarea>
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary px-4">登録</button>
                            </div>
                        </div>
                    </div>
                </form>

                <?php if (!empty($_GET['id'])) : ?>
                <form action="<?php t(MAIN_FILE) ?>/admin/field_delete" method="post" class="delete">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="id" value="<?php t($_view['field']['id']) ?>">
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
            </div>
        </div>
        <?php e($_view['widget_sets']['admin_page']) ?>
    </main>

<?php import('app/views/admin/footer.php') ?>
