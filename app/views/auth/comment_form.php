<?php import('app/views/auth/header.php') ?>

    <main class="col-11 col-md-7 mx-auto my-4">
        <div class="mb-4 text-center">
            <h1 class="h3">
                <a href="<?php t(MAIN_FILE) ?>/auth/home"><?php h($GLOBALS['string']['heading_mypage']) ?></a>
            </h1>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header heading"><?php h($_view['title']) ?></div>
            <div class="card-body">
                <?php e($GLOBALS['setting']['text_auth_comment_form']) ?>

                <?php if (isset($_view['warnings'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php foreach ($_view['warnings'] as $warning) : ?>
                    <?php h($warning) ?>
                    <?php endforeach ?>
                </div>
                <?php endif ?>

                <form action="<?php t(MAIN_FILE) ?>/auth/comment_form<?php $_view['comment']['id'] ? t('?id=' . $_view['comment']['id']) : '' ?><?php $_view['comment']['contact_id'] ? t('?contact_id=' . $_view['comment']['contact_id']) : '' ?>" method="post" class="register validate">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="id" value="<?php t($_view['comment']['id']) ?>">
                    <input type="hidden" name="contact_id" value="<?php t($_view['comment']['contact_id']) ?>">
                    <div class="form-group mb-2">
                        <label class="fw-bold">日時</label>
                        <input type="text" size="30" value="<?php t($_view['comment']['created']) ?>" readonly class="form-control">
                    </div>
                    <input type="hidden" name="name" value="<?php t($_view['comment']['name']) ?>">
                    <input type="hidden" name="url" value="<?php t($_view['comment']['url']) ?>">
                    <div class="form-group mb-2">
                        <label>お名前</label>
                        <input type="text" value="<?php t($_view['comment']['name']) ?>" readonly class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label>URL</label>
                        <input type="url" value="<?php t($_view['comment']['url']) ?>" readonly class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label class="fw-bold">コメント内容</label>
                        <textarea name="message" rows="10" cols="50" class="form-control"><?php t($_view['comment']['message']) ?></textarea>
                    </div>
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary px-4"><?php h($GLOBALS['string']['button_comment']) ?></button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($_GET['id'])) : ?>
        <div class="card shadow-sm mb-3">
            <div class="card-header">
                削除
            </div>
            <div class="card-body">
                <form action="<?php t(MAIN_FILE) ?>/auth/comment_delete" method="post" class="delete">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="id" value="<?php t($_view['comment']['id']) ?>">
                    <div class="form-group">
                        <button type="submit" class="btn btn-danger px-4"><?php h($GLOBALS['string']['button_comment_delete']) ?></button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif ?>

        <?php e($_view['widget_sets']['auth_page']) ?>
    </main>
    <div class="my-4 text-center">
        <a href="<?php t(MAIN_FILE) ?>/auth/home"><?php h($GLOBALS['string']['text_goto_auth_home']) ?></a>
    </div>

<?php import('app/views/auth/footer.php') ?>
