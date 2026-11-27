<?php import('app/views/auth/header.php') ?>

    <main class="form-login my-5 text-center">
        <?php if (empty($_view['twostep'])) : ?>
        <form action="<?php t(MAIN_FILE) ?>/auth/<?php empty($_GET['referer']) ? '' : t('?referer=' . rawurlencode($_GET['referer'])) ?>" method="post">
            <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
            <h1 class="mb-4"><?php h($_view['title']) ?></h1>
            <?php e($GLOBALS['setting']['text_auth_index']) ?>

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
                <input type="password" name="password" size="30" value="" class="form-control" id="password" placeholder="パスワード">
                <label for="loginPassword">パスワード</label>
            </div>
            <div class="form-group mt-2">
                <label><input type="checkbox" name="session" value="keep" class="form-check-input"<?php isset($_view['user']['session']) ? e('checked="checked"') : '' ?>> ログイン状態を記憶する</label>
            </div>
            <div class="form-group mt-2">
                <button type="submit" class="w-100 btn btn-lg btn-primary"><?php h($GLOBALS['string']['button_auth_login']) ?></button>
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
                <a href="<?php t(MAIN_FILE) ?>/"><?php h($GLOBALS['string']['text_goto_home']) ?></a>
            </div>
        </form>
        <?php else : ?>
        <form action="<?php t(MAIN_FILE) ?>/auth/<?php empty($_GET['referer']) ? '' : t('?referer=' . rawurlencode($_GET['referer'])) ?>" method="post">
            <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
            <input type="hidden" name="exec" value="twostep">
            <h1 class="mb-4"><?php h($_view['title']) ?></h1>
            <p>登録されたメールアドレスに、2段階認証用コードを送信しました。</p>

            <?php if (isset($_view['warnings'])) : ?>
            <div class="alert alert-danger">
                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                <?php foreach ($_view['warnings'] as $warning) : ?>
                <?php h($warning) ?>
                <?php endforeach ?>
            </div>
            <?php endif ?>

            <div class="form-floating">
                <input type="text" name="twostep_code" size="30" value="" class="form-control" id="twostep_code" placeholder="2段階認証用コード" autocomplete="one-time-code" inputmode="numeric">
                <label for="twostep_code">2段階認証用コード</label>
            </div>
            <div class="form-group mt-2">
                <label><input type="checkbox" name="twostep_session" value="keep" class="form-check-input"<?php isset($_view['user']['twostep_session']) ? e('checked="checked"') : '' ?>> 次回からこのブラウザではコードを入力しない</label>
            </div>
            <div class="form-group mt-2">
                <button type="submit" class="w-100 btn btn-lg btn-primary">認証する</button>
            </div>
        </form>
        <form action="<?php t(MAIN_FILE) ?>/auth/<?php empty($_GET['referer']) ? '' : t('?referer=' . rawurlencode($_GET['referer'])) ?>" method="post" class="mt-4">
            <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
            <input type="hidden" name="exec" value="twostep_resend">
            <button type="submit" class="btn btn-link">コードを再送信する</button>
        </form>
        <div class="form-group mt-1">
            <a href="<?php t(MAIN_FILE) ?>/auth/<?php empty($_GET['referer']) ? '' : t('?referer=' . rawurlencode($_GET['referer'])) ?>">ログイン画面に戻る</a>
        </div>
        <?php endif ?>

        <?php e($_view['widget_sets']['auth_home']) ?>
    </main>

<?php import('app/views/auth/footer.php') ?>
