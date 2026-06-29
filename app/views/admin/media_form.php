<?php import('app/views/admin/header.php') ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
        <?php if ($_REQUEST['_type'] !== 'iframe') : ?>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
            <h2 class="h3">
                <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-pencil-square"/></svg>
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
        <?php endif ?>

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
                <?php elseif (isset($_GET['warning'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php if ($_GET['warning'] === 'post') : ?>
                    メディアを登録できません。
                    <?php endif ?>
                </div>
                <?php endif ?>

                <?php if (empty($_GET['type'])) : ?>
                <form action="<?php t(MAIN_FILE) ?>/admin/media_form<?php t(empty($_REQUEST['_type']) ? '' : '?_type=' . $_REQUEST['_type']) ?>" method="post" class="register">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">
                            登録
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-2">
                                <label class="fw-bold">ファイル</label>
                                <div class="uploads" id="medias" data-uploads="<?php t(MAIN_FILE) ?>/admin/media_upload?_type=json">
                                    <button type="button">ファイル選択</button>
                                    <input type="file" name="medias[]" multiple>
                                    <p>選択されていません</p>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="fw-bold">ディレクトリ</label>
                                <input type="text" name="directory" size="30" value="<?php t($_GET['directory']) ?>" class="form-control">
                            </div>
                            <div class="form-group mt-4">
                                <a href="<?php t(MAIN_FILE) ?>/admin/media?directory=<?php t($_GET['directory'] === '' ? '' : $_GET['directory']) ?><?php t(empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']) ?>" class="btn btn-secondary px-4">戻る</a>
                                <button type="submit" class="btn btn-primary px-4">登録</button>
                            </div>
                        </div>
                    </div>
                </form>
                <?php else : ?>
                <form action="<?php t(MAIN_FILE) ?>/admin/media_form<?php t(empty($_REQUEST['_type']) ? '' : '?_type=' . $_REQUEST['_type']) ?>" method="post" class="register validate">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="directory" value="<?php t($_GET['directory']) ?>">
                    <input type="hidden" name="name" value="<?php t($_GET['name']) ?>">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header">
                            登録
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-2">
                                <label class="fw-bold">名前</label>
                                <input type="text" name="rename" size="30" value="<?php t($_GET['name']) ?>" class="form-control">
                            </div>
                            <div class="form-group mt-4">
                                <a href="<?php t(MAIN_FILE) ?>/admin/media?directory=<?php t($_GET['directory'] === '' ? '' : $_GET['directory']) ?><?php t(empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']) ?>" class="btn btn-secondary px-4">戻る</a>
                                <button type="submit" class="btn btn-primary px-4">登録</button>
                            </div>
                        </div>
                    </div>
                </form>

                <form action="<?php t(MAIN_FILE) ?>/admin/media_delete<?php t(empty($_REQUEST['_type']) ? '' : '?_type=' . $_REQUEST['_type']) ?>" method="post" class="delete">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="directory" value="<?php t($_GET['directory']) ?>">
                    <input type="hidden" name="name" value="<?php t($_GET['name']) ?><?php t($_GET['type'] === 'directory' ? '/' : '') ?>">
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
