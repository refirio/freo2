<?php import('app/views/auth/header.php') ?>

        <main class="form-login my-5 text-center">
            <form action="<?php t(MAIN_FILE) ?>/auth/<?php empty($_GET['referer']) ? '' : t('?referer=' . rawurlencode($_GET['referer'])) ?>" method="post">
                <h1 class="mb-4"><?php h($GLOBALS['setting']['title']) ?></h1>

                <?php if (isset($_view['warnings'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php foreach ($_view['warnings'] as $warning) : ?>
                    <?php h($warning) ?>
                    <?php endforeach ?>
                </div>
                <?php endif ?>

                <div class="form-floating">
                    <input type="text" name="username" size="30" value="<?php t($_view['user']['username']) ?>" class="form-control" id="username" placeholder="ユーザー名">
                    <label for="loginUsername">ユーザー名</label>
                </div>
                <div class="form-floating">
                    <input type="password" name="password" size="30" value="<?php t($_view['user']['password']) ?>" class="form-control" id="password" placeholder="パスワード">
                    <label for="loginPassword">パスワード</label>
                </div>
                <div class="form-group mt-2">
                    <label><input type="checkbox" name="session" value="keep" class="form-check-input"<?php isset($_view['user']['session']) ? e('checked="checked"') : '' ?>> ログイン状態を記憶する</label>
                </div>
                <div class="form-group mt-2">
                    <button class="w-100 btn btn-lg btn-primary" type="submit">ログイン</button>
                </div>
                <div class="form-group mt-4">
                    <a href="<?php t(MAIN_FILE) ?>/auth/password">パスワード再発行</a>
                </div>
                <?php if (!empty($GLOBALS['setting']['user_use_register'])) : ?>
                <div class="form-group mt-1">
                    <a href="<?php t(MAIN_FILE) ?>/auth/register">ユーザー登録</a>
                </div>
                <?php endif ?>
                <div class="form-group mt-4">
                    <a href="<?php t(MAIN_FILE) ?>/">トップページへ戻る</a>
                </div>
            </form>

            <?php e($_view['widget_sets']['auth_home']) ?>
        </main>

<?php import('app/views/auth/footer.php') ?>
